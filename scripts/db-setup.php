<?php

declare(strict_types=1);

/**
 * Ricrea il database fitslot ed esegue migration e seed locali.
 */
use Ladythelemons\Fitslot\Config\Database;

if ($argc > 1) {
    fwrite(STDERR, "Usare composer db:setup senza opzioni.\n");
    exit(1);
}

$operazione = 'caricamento del bootstrap';

try {
    require_once dirname(__DIR__) . '/bootstrap.php';
    $operazione = 'lettura della configurazione';
    $configurazione = Database::leggiConfigurazione();

    // La configurazione ammette solo host locali e il nome esatto fitslot.
    echo "ATTENZIONE: il database locale fitslot e tutti i suoi dati saranno eliminati.\n";
    echo "Il reset verra eseguito automaticamente.\n";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // 1. Ci colleghiamo a MySQL senza selezionare ancora un database.
    $operazione = 'connessione a MySQL';
    $connessione = new mysqli(
        $configurazione['indirizzo'],
        $configurazione['utente'],
        $configurazione['password'],
        '',
        $configurazione['porta']
    );
    $connessione->set_charset('utf8mb4');

    // 2. Ripartiamo da un database vuoto. Il nome nelle query e fisso.
    $operazione = 'eliminazione del database fitslot';
    $connessione->query('DROP DATABASE IF EXISTS `fitslot`');
    echo "[OK] Database fitslot eliminato, se presente.\n";

    $operazione = 'creazione del database fitslot';
    $connessione->query('CREATE DATABASE `fitslot` CHARACTER SET utf8mb4');
    $connessione->select_db('fitslot');
    echo "[OK] Database fitslot ricreato vuoto.\n";

    // 3. Eseguiamo prima le migration e poi i seed, in ordine di nome.
    $cartellaProgetto = dirname(__DIR__);
    $fileEseguiti = 0;
    foreach (['migrations', 'seed'] as $cartella) {
        $fileSql = glob("$cartellaProgetto/database/$cartella/*.sql") ?: [];
        sort($fileSql);

        foreach ($fileSql as $percorso) {
            $nomeFile = $cartella . '/' . basename($percorso);
            $operazione = "lettura di $nomeFile";
            $sql = file_get_contents($percorso);
            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('File SQL illeggibile o vuoto');
            }

            $operazione = "esecuzione di $nomeFile";
            $connessione->multi_query($sql);

            // Un file puo contenere piu istruzioni: leggiamo tutti i risultati.
            do {
                $risultato = $connessione->store_result();
                if ($risultato instanceof mysqli_result) {
                    $risultato->free();
                }
            } while ($connessione->more_results() && $connessione->next_result());

            echo "[OK] Eseguito: $nomeFile\n";
            $fileEseguiti++;
        }
    }

    $connessione->close();
    echo "Reset completato. File SQL eseguiti: $fileEseguiti.\n";
} catch (Throwable $errore) {
    if ($operazione === 'lettura della configurazione' && $errore instanceof InvalidArgumentException) {
        fwrite(STDERR, "Errore di configurazione: " . $errore->getMessage() . "\n");
    } else {
        // I messaggi delle librerie e di MySQL possono contenere dati riservati.
        fwrite(STDERR, "Errore durante $operazione (codice " . $errore->getCode() . "). Controllare .env, MySQL e i file SQL.\n");
        fwrite(STDERR, "Se il reset era gia iniziato, il database potrebbe essere vuoto o ricreato solo in parte. Non e previsto rollback.\n");
    }
    exit(1);
}
