<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$autorizzazione = new AutorizzazioneService();

if ($sessione->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

if (!$autorizzazione->haAccessoA(['ISTRUTTORE'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area istruttore - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="homepage.php">Le mie aree</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="dashboard-heading">
            <h1>Area <span>istruttore</span></h1>
        </header>

        <section class="panel content-panel" aria-labelledby="lezioni-disponibilita">
            <h2 id="lezioni-disponibilita">Lezioni e disponibilità</h2>
            <p class="panel-description">Le funzioni per consultare le lezioni e gestire le disponibilità sono in preparazione.</p>
            <a class="button secondary member-action" href="profilo.php">
                Il mio profilo <span aria-hidden="true">&rarr;</span>
            </a>
        </section>
    </main>
</body>
</html>
