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

if (!$autorizzazione->haAccessoA(['GESTORE'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area gestore - FitSlot</title>
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
            <span class="eyebrow">Vista gestore</span>
            <h1>Area gestore</h1>
            <p>Qui il gestore potrà controllare le attività e l'andamento del centro.</p>
        </div>
        <section class="card-grid" aria-label="Funzioni del gestore">
            <a class="feature-card" href="tipi-abbonamento.php">
                <span class="feature-tag">UE-08</span>
                <h3>Tipi di abbonamento</h3>
                <p>Gestisci il listino e i piani di abbonamento.</p>
                <span class="card-link">Apri gestione listino &rarr;</span>
            </a>
            <article class="feature-card">
                <span class="feature-tag">GESTORE</span>
                <h3>Aggiungi sala</h3>
                <p>Aggiungi una nuova sala al sistema.</p>
            </a>
            <article class="unavailable">
                <span class="feature-tag">GESTORE</span>
                <h3>Gestione centro</h3>
                <p>Accedi alle funzioni di gestione del centro sportivo.</p>
            </article>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Area gestore</footer>
</body>
</html>
