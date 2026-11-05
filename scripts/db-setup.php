<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;

// Distingue i nostri messaggi dagli errori delle librerie, che possono contenere dati riservati.
final class ErrorePreparazioneDatabase extends RuntimeException
{
}

$connessione = null;
$codiceUscita = 0;
$fase = 'configurazione';

try {
    require_once dirname(__DIR__) . '/bootstrap.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $argomenti = array_slice($argv, 1);
    if ($argomenti !== [] && $argomenti !== ['--registra-impronte'] && $argomenti !== ['--baseline-existing']) {
        throw new ErrorePreparazioneDatabase('Uso: composer db:setup [-- --registra-impronte]');
    }
    $registraImpronte = $argomenti !== [];
    $cartellaProgetto = dirname(__DIR__);
    try {
        $configurazione = Database::leggiConfigurazione();
    } catch (InvalidArgumentException $errore) {
        throw new ErrorePreparazioneDatabase($errore->getMessage());
    }
    $nomeDatabase = $configurazione['nome_database'];

    $fase = 'connessione';
    $connessione = new mysqli($configurazione['indirizzo'], $configurazione['utente'], $configurazione['password'], '', $configurazione['porta']);
    $connessione->set_charset('utf8mb4');

    // 1. Impediamo a due copie dello script di lavorare sullo stesso database.
    // Il blocco viene acquisito prima di qualsiasi DDL. Non viene rilasciato dai commit impliciti.
    $fase = 'acquisizione lock';
    $nomeBlocco = 'fitslot:' . substr(hash('sha256', strtolower($nomeDatabase)), 0, 56);
    $risultato = $connessione->execute_query('SELECT GET_LOCK(?, 0)', [$nomeBlocco]);
    $bloccoOttenuto = $risultato->fetch_row()[0];
    if ((int) $bloccoOttenuto !== 1) {
        throw new ErrorePreparazioneDatabase('Setup gia in esecuzione o lock non disponibile. Nessun SQL applicato.');
    }

    // 2. Prepariamo il database e aggiorniamo il vecchio registro, senza ricrearlo.
    $fase = 'preparazione registro';
    $connessione->query("CREATE DATABASE IF NOT EXISTS `$nomeDatabase` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $connessione->select_db($nomeDatabase);
    $connessione->query("
        CREATE TABLE IF NOT EXISTS schema_migrations (
            filename VARCHAR(255) PRIMARY KEY,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            checksum CHAR(64) NULL,
            state VARCHAR(16) NOT NULL DEFAULT 'applied'
        ) ENGINE=InnoDB
    ");

    // Estensione compatibile del registro esistente; nessun SQL applicativo viene rieseguito.
    $risultato = $connessione->query('SHOW COLUMNS FROM schema_migrations');
    $colonne = array_column($risultato->fetch_all(MYSQLI_ASSOC), 'Field');
    if (!in_array('checksum', $colonne, true)) {
        $connessione->query('ALTER TABLE schema_migrations ADD COLUMN checksum CHAR(64) NULL');
    }
    if (!in_array('state', $colonne, true)) {
        $connessione->query("ALTER TABLE schema_migrations ADD COLUMN state VARCHAR(16) NOT NULL DEFAULT 'applied'");
    }

    // 3. Leggiamo tutti gli SQL, prima le migration e poi i seed.
    $fase = 'verifica file SQL';
    $fileSql = [];
    foreach (['migrations', 'seed'] as $cartella) {
        $percorsiSql = glob("$cartellaProgetto/database/$cartella/*.sql") ?: [];
        sort($percorsiSql);
        foreach ($percorsiSql as $file) {
            $nome = "$cartella/" . basename($file);
            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                throw new ErrorePreparazioneDatabase("File SQL illeggibile o vuoto: $nome");
            }
            // L'impronta cambia se cambia il contenuto del file.
            // Ignoriamo solo le differenze tra i fine riga di Windows e Linux.
            $fileSql[$nome] = [
                'sql' => $sql,
                'impronta' => hash('sha256', str_replace(["\r\n", "\r"], "\n", $sql)),
            ];
        }
    }
    // 4. Controlliamo i file gia eseguiti prima di applicare quelli nuovi.
    $fileApplicati = [];
    $fileSenzaImpronta = [];
    $risultato = $connessione->query('SELECT filename, checksum, state FROM schema_migrations');
    while ($riga = $risultato->fetch_assoc()) {
        $nome = $riga['filename'];
        if ($riga['state'] !== 'applied') {
            throw new ErrorePreparazioneDatabase('Esecuzione precedente incompleta: verificare registro e schema con il team.');
        }
        if (!isset($fileSql[$nome])) {
            throw new ErrorePreparazioneDatabase('Un file SQL registrato e assente o rinominato: ripristinarlo prima del setup.');
        }
        if ($riga['checksum'] === null) {
            $fileSenzaImpronta[] = $nome;
        } elseif ($riga['checksum'] !== $fileSql[$nome]['impronta']) {
            throw new ErrorePreparazioneDatabase("File SQL gia applicato ma modificato: $nome. Ripristinare il file e creare una nuova migration.");
        }
        $fileApplicati[$nome] = true;
    }

    if ($fileSenzaImpronta !== [] && !$registraImpronte) {
        throw new ErrorePreparazioneDatabase('Registro precedente senza checksum. Confrontare SQL e schema, poi usare --registra-impronte (vedi README).');
    }
    // 5. Registriamo le impronte precedenti oppure eseguiamo i file nuovi.
    if ($registraImpronte) {
        // Conferma esplicita dell'operatore: non prova il contenuto eseguito in passato.
        foreach ($fileSenzaImpronta as $nome) {
            $connessione->execute_query(
                'UPDATE schema_migrations SET checksum = ? WHERE filename = ? AND checksum IS NULL',
                [$fileSql[$nome]['impronta'], $nome]
            );
            echo "[IMPRONTA REGISTRATA] $nome\n";
        }
        echo "Registrazione delle impronte completata. Nessuna migration o seed eseguiti.\n";
    } else {
        foreach ($fileSql as $nome => $file) {
            if (isset($fileApplicati[$nome])) {
                echo "[OK] Gia eseguito e checksum verificato: $nome\n";
                continue;
            }
            $fase = "esecuzione $nome";
            // Persistiamo l'intenzione prima dell'SQL: un crash blocca i tentativi successivi.
            $connessione->execute_query(
                "INSERT INTO schema_migrations (filename, checksum, state) VALUES (?, ?, 'running')",
                [$nome, $file['impronta']]
            );
            echo "[...] Eseguo: $nome\n";
            $connessione->multi_query($file['sql']);
            do {
                $risultato = $connessione->store_result();
                if ($risultato instanceof mysqli_result) {
                    $risultato->free();
                }
                // Anche INSERT IGNORE puo nascondere anomalie: non registriamo un successo.
                if ($connessione->warning_count > 0) {
                    throw new ErrorePreparazioneDatabase("Warning SQL in $nome: controllare manualmente prima di proseguire.");
                }
                if (!$connessione->more_results()) {
                    break;
                }
                $connessione->next_result(); // In modalita STRICT gli errori successivi fermano il setup.
            } while (true);
            $connessione->execute_query(
                "UPDATE schema_migrations SET state = 'applied', applied_at = CURRENT_TIMESTAMP WHERE filename = ?",
                [$nome]
            );
            echo "[OK] Completato: $nome\n";
        }
        echo "\nDatabase FitSlot pronto!\n";
    }
} catch (Throwable $errore) {
    if ($errore instanceof ErrorePreparazioneDatabase) {
        $messaggioErrore = $errore->getMessage();
    } elseif ($errore instanceof mysqli_sql_exception) {
        $messaggioErrore = 'Errore MySQL (codice ' . $errore->getCode() . '). Dettagli riservati omessi.';
    } else {
        $messaggioErrore = 'Errore interno. Dettagli riservati omessi.';
    }
    fwrite(STDERR, "ERRORE durante $fase: $messaggioErrore\n");
    fwrite(STDERR, "Setup interrotto. I DDL MySQL possono lasciare modifiche parziali: NON ritentare alla cieca. Consultare README e verificare schema e registro.\n");
    $codiceUscita = 1;
} finally {
    // La chiusura della sessione rilascia GET_LOCK anche con risultati SQL pendenti.
    if ($connessione instanceof mysqli) {
        $connessione->close();
    }
}
exit($codiceUscita);
