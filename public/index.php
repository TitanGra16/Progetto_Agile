<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;

require_once dirname(__DIR__) . '/bootstrap.php';

try {
    $connessione = Database::connetti();

    $risultato = $connessione->query(
        'SELECT COUNT(*) AS totale FROM utenti'
    );

    $riga = $risultato->fetch_assoc();
    $totale = (int) $riga['totale'];

    $connessione->close();

    $messaggio = "Database collegato! Iscritti: $totale";
} catch (Throwable $errore) {
    http_response_code(500);
    error_log($errore->getMessage());

    $messaggio = "Errore di collegamento al database.";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>FitSlot</title>
</head>
<body>
    <h1>Benvenuto su FitSlot</h1>
    <p>
        <?= htmlspecialchars(
            $messaggio,
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>
</body>
</html>
