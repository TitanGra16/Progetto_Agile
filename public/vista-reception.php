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

if (!$autorizzazione->haAccessoA(['RECEPTION'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area reception - FitSlot</title>
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
            <span class="eyebrow">Vista reception</span>
            <h1>Area reception</h1>
            <p>Qui la reception potrà gestire le attività quotidiane del centro.</p>
        </div>
        <section class="card-grid" aria-label="Funzioni della reception">
            <a class="feature-card" href="registrazione-iscritto.php">
                <span class="feature-tag">RECEPTION</span>
                <h3>Registra iscritto</h3>
                <p>Inserisci i dati di una nuova persona iscritta.</p>
                <span class="card-link">Apri registrazione →</span>
            </a>
            <a class="feature-card" href="ricerca-iscritto.php">
                <span class="feature-tag">RECEPTION</span>
                <h3>Cerca iscritto</h3>
                <p>Trova rapidamente un iscritto già presente nel sistema.</p>
                <span class="card-link">Apri ricerca →</span>
            </a>
            <a class="feature-card" href="certificati-medici.php">
                <span class="feature-tag">RECEPTION</span>
                <h3>Certificati medici</h3>
                <p>Registra e aggiorna i certificati medici degli iscritti.</p>
                <span class="card-link">Gestisci certificati →</span>
            </a>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Area reception</footer>
</body>
</html>
