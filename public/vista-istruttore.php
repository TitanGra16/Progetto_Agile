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
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="index.php">Portale</a>
            <a href="login.php">Accedi</a>
        </nav>
    </header>

    <main class="page-shell">
        <div class="page-heading">
            <span class="eyebrow">Vista istruttore</span>
            <h1>Area istruttore</h1>
            <p>Qui l'istruttore potrà consultare le proprie lezioni e attività.</p>
        </div>
        <section class="card-grid" aria-label="Funzioni dell'istruttore">
            <article class="feature-card">
                <span class="feature-tag">ISTRUTTORE</span>
                <h3>Le mie lezioni</h3>
                <p>Visualizza le lezioni assegnate e gli iscritti prenotati.</p>
            </article>
            <article class="feature-card">
                <span class="feature-tag">ISTRUTTORE</span>
                <h3>Disponibilità</h3>
                <p>Consulta e aggiorna le disponibilità per le attività.</p>
            </article>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Area istruttore</footer>
</body>
</html>
