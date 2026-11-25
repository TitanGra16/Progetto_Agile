<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\AggiuntaSalaController;
use Ladythelemons\Fitslot\DataModel\Sala;
use Ladythelemons\Fitslot\Repository\SaleRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$sessioneUtente = new SessioneService();
if ($sessioneUtente->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

if (!(new AutorizzazioneService())->haAccessoA(['GESTORE'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

$controllore = new AggiuntaSalaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $risultato = $controllore->gestisciRichiesta('POST', $_POST);
    $_SESSION['risultato_aggiunta_sala'] = $risultato;
    $destinazione = $risultato['esito'] === 'successo' ? '#sale' : '#nuova-sala';
    header('Location: aggiunge-sala.php' . $destinazione, true, 303);
    exit;
}

$pagina = $controllore->gestisciRichiesta('GET', []);
if (isset($_SESSION['risultato_aggiunta_sala'])) {
    $pagina = $_SESSION['risultato_aggiunta_sala'];
    unset($_SESSION['risultato_aggiunta_sala']);
}

$sale = [];
$erroreSale = '';
try {
    $sale = (new SaleRepository())->trovaTutte();
} catch (Throwable $errore) {
    $erroreSale = 'Non è stato possibile caricare l’elenco delle sale. Riprova più tardi.';
}

$dati = $pagina['dati'];
$salaAggiunta = $pagina['esito'] === 'successo' && $pagina['codice_sala'] !== null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sale - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
    <link rel="stylesheet" href="css/accesso.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="vista-gestore.php">Area gestore</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="dashboard-heading">
            <h1>Sale</h1>
            <a class="button primary member-action" href="#nuova-sala">Aggiungi sala</a>
        </header>

        <div class="panel-stack">
            <section class="panel wide-panel content-panel" id="sale" aria-labelledby="elenco-sale">
                <h2 id="elenco-sale">Elenco sale</h2>

                <?php if ($salaAggiunta): ?>
                    <p class="access-message form-success" role="status">
                        Sala aggiunta con successo. Codice sala <strong><?= (int) $pagina['codice_sala'] ?></strong>
                    </p>
                <?php endif; ?>

                <?php if ($erroreSale !== ''): ?>
                    <p class="access-message access-error" role="alert"><?= htmlspecialchars($erroreSale, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif ($sale === []): ?>
                    <p role="status">Non sono ancora presenti sale.</p>
                <?php else: ?>
                    <div class="table-scroll" role="region" aria-labelledby="elenco-sale" tabindex="0">
                        <table class="data-table">
                            <caption class="visually-hidden">Elenco delle sale con capienza, orario e codice</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Sala</th>
                                    <th scope="col">Capienza</th>
                                    <th scope="col">Orario di occupazione</th>
                                    <th scope="col">Codice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sale as $sala): ?>
                                    <?php
                                    if (!$sala instanceof Sala) {
                                        continue;
                                    }

                                    $inizio = substr($sala->getInizioOccupazione(), 0, 5);
                                    $fine = substr($sala->getFineOccupazione(), 0, 5);
                                    ?>
                                    <tr>
                                        <th scope="row"><?= htmlspecialchars($sala->getNome(), ENT_QUOTES, 'UTF-8') ?></th>
                                        <td><?= (int) $sala->getCapienza() ?> persone</td>
                                        <td><?= htmlspecialchars($inizio . '–' . $fine, ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) $sala->getId() ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel content-panel" id="nuova-sala" aria-labelledby="titolo-nuova-sala">
                <h2 id="titolo-nuova-sala">Aggiungi una sala</h2>

                <?php if ($pagina['esito'] !== 'iniziale' && !$salaAggiunta): ?>
                    <p class="access-message access-error" role="alert"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <form class="access-form two-column-form" id="form-aggiunta-sala" method="post" action="aggiunge-sala.php">
                    <div class="access-field">
                        <label for="nome">Nome sala</label>
                        <input id="nome" name="nome" type="text" maxlength="100"
                               value="<?= htmlspecialchars((string) $dati['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                    </div>

                    <div class="access-field">
                        <label for="capienza">Capienza (persone)</label>
                        <input id="capienza" name="capienza" type="number" min="1" step="1"
                               value="<?= htmlspecialchars((string) $dati['capienza'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="access-field">
                        <label for="inizio_occupazione">Inizio occupazione</label>
                        <input id="inizio_occupazione" name="inizio_occupazione" type="time" step="60"
                               value="<?= htmlspecialchars((string) $dati['inizio_occupazione'], ENT_QUOTES, 'UTF-8') ?>"
                               aria-describedby="aiuto-orario" required>
                    </div>

                    <div class="access-field">
                        <label for="fine_occupazione">Fine occupazione</label>
                        <input id="fine_occupazione" name="fine_occupazione" type="time" step="60"
                               value="<?= htmlspecialchars((string) $dati['fine_occupazione'], ENT_QUOTES, 'UTF-8') ?>"
                               aria-describedby="aiuto-orario" required>
                    </div>

                    <p class="access-hint form-wide" id="aiuto-orario">La fascia oraria deve durare almeno un’ora.</p>

                    <div class="form-actions form-wide">
                        <button class="button primary access-action" type="submit">Aggiungi sala</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
