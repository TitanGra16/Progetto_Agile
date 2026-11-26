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

if (!$autorizzazione->haAccessoA(['RECEPTION', 'GESTORE'])) {
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
            <h1>Area <span>reception</span></h1>
        </header>

        <nav class="card-grid" aria-label="Funzioni della reception">
            <a class="feature-card dashboard-card dashboard-card-primary" href="ricerca-iscritto.php">
                <h2>Cerca iscritto</h2>
                <p>Cerca per nome, cognome o codice iscritto.</p>
                <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
            </a>
            <a class="feature-card dashboard-card" href="registrazione-iscritto.php">
                <h2>Registra iscritto</h2>
                <p>Crea l’anagrafica di un nuovo iscritto.</p>
                <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
            </a>
            <?php if ($autorizzazione->haAccessoA(['RECEPTION', 'GESTORE'])): ?>
                <a class="feature-card dashboard-card" href="certificati-medici.php">
                    <h2>Certificati medici</h2>
                    <p>Registra la scadenza e archivia una copia PDF.</p>
                    <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>
        </nav>
    </main>
</body>
</html>
