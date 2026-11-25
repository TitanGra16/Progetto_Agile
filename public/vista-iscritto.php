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

if (!$autorizzazione->haAccessoA(['ISCRITTO'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area iscritto - FitSlot</title>
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
            <span class="eyebrow">Vista iscritto</span>
            <h1>Area personale</h1>
            <p>Qui l'iscritto potrà consultare le proprie attività e il proprio account.</p>
        </div>
        <section class="card-grid" aria-label="Funzioni dell'iscritto">
            <article class="feature-card">
                <span class="feature-tag">ISCRITTO</span>
                <h3>Le mie prenotazioni</h3>
                <p>Consulta le attività prenotate e i posti disponibili.</p>
            </article>
            
            <a class="feature-card" href="profilo.php">
                <span class="feature-tag">ISCRITTO</span>
                <h3>Il mio profilo</h3>
                <p>Visualizza le informazioni del tuo account.</p>
                <span class="card-link">Apri il mio profilo →</span>
            </a>

            <a class="feature-card" href="carica-certificato.php">
                <span class="feature-tag">ISCRITTO</span>

                <h3>Carica certificato medico</h3>

                <p>
                    Carica il tuo certificato medico in formato PDF
                    e invialo alla Reception per la verifica.
                </p>

                <span class="card-link">
                    Carica certificato →
                </span>
            </a>

        </section>
    </main>
    <footer class="site-footer">FitSlot · Area iscritto</footer>
</body>
</html>
