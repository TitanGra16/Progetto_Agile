<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$autenticato = $sessione->getIdUtente() !== null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FitSlot — Portale</title>
    <link rel="stylesheet" href="css/portale.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
    </header>

    <main class="page-shell">
        <section class="hero" aria-labelledby="portal-title">
            <span class="eyebrow">Gestione centro sportivo</span>
            <h1 id="portal-title">Benvenuto su <span>FitSlot</span></h1>
            <div class="hero-actions">
                <a class="button primary" href="<?= $autenticato ? 'homepage.php' : 'login.php' ?>">
                    <?= $autenticato ? 'Vai all’area personale' : 'Accedi al tuo account' ?>
                </a>
                <a class="button secondary" href="registrazione-iscritto.php">Registra un iscritto</a>
            </div>
        </section>

        <section aria-labelledby="services-title">
            <div class="section-heading">
                <h2 id="services-title">Funzionalità</h2>
            </div>
            <div class="card-grid">
                <a class="feature-card" href="registrazione-iscritto.php">
                    <span class="feature-tag">UE-01</span>
                    <h3>Registra iscritto</h3>
                    <p>Inserisci i dati anagrafici per creare un nuovo iscritto.</p>
                    <span class="card-link">Apri registrazione →</span>
                </a>
                <article class="feature-card unavailable" aria-label="Ricerca iscritti: non ancora integrata">
                    <span class="feature-tag">UE-02</span>
                    <h3>Cerca iscritto</h3>
                    <p>La ricerca non è ancora disponibile nella versione di Main analizzata.</p>
                    <span class="card-link">In attesa di integrazione</span>
                </article>
                <a class="feature-card" href="<?= $autenticato ? 'homepage.php' : 'login.php' ?>">
                    <span class="feature-tag">UE-03</span>
                    <h3><?= $autenticato ? 'Area personale' : 'Login' ?></h3>
                    <p><?= $autenticato ? 'Accedi alla pagina riservata della tua sessione.' : 'Entra utilizzando email e password.' ?></p>
                    <span class="card-link">Apri →</span>
                </a>
                <a class="feature-card" href="richiesta-attivazione.php">
                    <span class="feature-tag">UE-69</span>
                    <h3>Attivazione account</h3>
                    <p>Richiedi un nuovo link se il tuo account è ancora in attesa di attivazione.</p>
                    <span class="card-link">Richiedi link →</span>
                </a>
            </div>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Progetto universitario Agile Lady!TheLemons!</footer>
</body>
</html>
