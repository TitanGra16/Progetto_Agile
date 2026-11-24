<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();

if ($sessione->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

$autorizzazione = new AutorizzazioneService();
$ruoli = $autorizzazione->getRuoliUtente();
$isGestore = in_array('GESTORE', $ruoli, true);

$mostraIscritto = $isGestore || in_array('ISCRITTO', $ruoli, true);
$mostraReception = $isGestore || in_array('RECEPTION', $ruoli, true);
$mostraIstruttore = $isGestore || in_array('ISTRUTTORE', $ruoli, true);
$mostraGestore = $isGestore;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area personale - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
    <nav aria-label="Navigazione principale">
        <a href="index.php">Portale</a>

        <!-- Accesso al profilo personale per tutti gli utenti autenticati -->
        <a href="profilo.php">Il mio profilo</a>
        <form class="logout-form" action="logout.php" method="POST">
            <button type="submit">Esci</button>
        </form>
    </nav>

    </header>

    <main class="page-shell">
        <section class="hero" aria-labelledby="area-title">
            <span class="eyebrow">Area personale</span>
            <h1 id="area-title">Bentornato su <span>FitSlot</span></h1>
            <p>Seleziona l'area che vuoi utilizzare.</p>
            <p>Ruoli associati: <strong><?= htmlspecialchars(implode(', ', $ruoli), ENT_QUOTES, 'UTF-8') ?></strong></p>
        </section>

        <section aria-labelledby="views-title">
            <div class="section-heading">
                <h2 id="views-title">Le tue aree</h2>
                <p>Le funzioni mostrate dipendono dai ruoli assegnati al tuo account.</p>
            </div>

            <div class="card-grid">
                <?php if ($mostraIscritto): ?>
                    <a class="feature-card" href="vista-iscritto.php">
                        <span class="feature-tag">ISCRITTO</span>
                        <h3>Area personale</h3>
                        <p>Consulta prenotazioni, profilo e informazioni personali.</p>
                        <span class="card-link">Apri area iscritto →</span>
                    </a>
                <?php endif; ?>

                <?php if ($mostraReception): ?>
                    <a class="feature-card" href="vista-reception.php">
                        <span class="feature-tag">RECEPTION</span>
                        <h3>Area reception</h3>
                        <p>Gestisci iscritti e attività quotidiane del centro.</p>
                        <span class="card-link">Apri area reception →</span>
                    </a>
                <?php endif; ?>

                <?php if ($mostraIstruttore): ?>
                    <a class="feature-card" href="vista-istruttore.php">
                        <span class="feature-tag">ISTRUTTORE</span>
                        <h3>Area istruttore</h3>
                        <p>Consulta lezioni, iscritti prenotati e disponibilità.</p>
                        <span class="card-link">Apri area istruttore →</span>
                    </a>
                <?php endif; ?>

                <?php if ($mostraGestore): ?>
                    <a class="feature-card" href="vista-gestore.php">
                        <span class="feature-tag">GESTORE</span>
                        <h3>Area gestore</h3>
                        <p>Controlla le attività del centro e quelle degli altri ruoli.</p>
                        <span class="card-link">Apri area gestore →</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Area personale</footer>
</body>
</html>
