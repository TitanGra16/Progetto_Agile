<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();

if ($sessione->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benvenuto - FitSlot</title>
</head>
<body>

    <h1>Benvenuto su FitSlot!</h1>

    <p>Hai effettuato correttamente l'accesso.</p>


    <!-- TODO(UE-04): collegare il profilo alle homepage
         di tutti i ruoli dopo il merge della UE-04. -->

    <p>
        <a href="profilo.php">Il mio profilo</a>
    </p>

    <!--pulsante per uscire dal profilo e tornare alla pagina di login, momentaneamente in fondo alla homepage -->
    <form action="logout.php" method="POST">
        <button type="submit">Esci</button>
    </form>

</body>
</html>
