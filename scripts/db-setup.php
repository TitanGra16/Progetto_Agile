<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;

if ($argc > 1) {
    fwrite(STDERR, "Usare composer db:setup senza opzioni.\n");
    exit(1);
}

$operazione = 'lettura della configurazione';

try {
    require_once dirname(__DIR__) . '/bootstrap.php';
    $configurazione = Database::leggiConfigurazione();
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

    // 2. Creiamo il database solo se manca.
    $operazione = 'preparazione del database';
    $nomeDatabase = $configurazione['nome_database'];
    $connessione->query("CREATE DATABASE IF NOT EXISTS `$nomeDatabase` CHARACTER SET utf8mb4");
    $connessione->select_db($nomeDatabase);

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
    echo "Database pronto. File SQL eseguiti: $fileEseguiti.\n";
} catch (Throwable $errore) {
    // Non mostriamo messaggi SQL o dettagli che possono contenere credenziali.
    fwrite(STDERR, "Errore durante $operazione (codice " . $errore->getCode() . ").\n");
    fwrite(STDERR, "Controllare la configurazione e i file SQL. Le istruzioni gia eseguite potrebbero essere rimaste applicate: non ritentare alla cieca.\n");
    exit(1);
}
