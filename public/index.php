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
    <link rel="stylesheet" href="css/index.css">
</head>
<body class="portal-page">
    <header class="topbar portal-header">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
    </header>

    <main class="page-shell portal-main">
        <section class="portal-hero" aria-labelledby="portal-title">
            <div class="portal-copy">
                <h1 id="portal-title">Il tuo centro <span class="portal-title-line">sportivo <span class="portal-accent">online.</span></span></h1>
                <p>Il portale per iscritti e staff del centro sportivo.</p>

                <div class="hero-actions">
                    <a class="button primary" href="<?= $autenticato ? 'homepage.php' : 'login.php' ?>">
                        <?= $autenticato ? 'Vai all’area personale' : 'Accedi' ?>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <?php if (!$autenticato): ?>
                        <a class="button secondary" href="richiesta-attivazione.php">Attiva account</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="portal-visual">
                <img src="images/portale-fitslot.webp" width="1600" height="800" alt="" fetchpriority="high">
            </div>
        </section>
    </main>

    <footer class="site-footer portal-footer">Progetto universitario Agile · Team LadyTheLemons!</footer>
</body>
</html>
