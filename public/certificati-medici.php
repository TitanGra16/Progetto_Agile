<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\CertificatoMedicoController;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\TokenCsrf;

header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

if (!(new AutorizzazioneService())->haRuolo('RECEPTION')) {
    http_response_code(403);
    exit('Accesso riservato alla reception.');
}

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo !== 'GET' && $metodo !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

$tokenCsrf = TokenCsrf::genera($_SESSION, 'csrf_certificati_medici');

if ($metodo === 'POST'
    && !TokenCsrf::valida($_SESSION, 'csrf_certificati_medici', $_POST['token_csrf'] ?? null)) {
    http_response_code(403);
    exit('Richiesta non valida. Ricarica la pagina e riprova.');
}

try {
    $controller = new CertificatoMedicoController();
    $pagina = $controller->gestisciRichiesta(
        $metodo,
        $metodo === 'POST' ? $_POST : $_GET,
        $_FILES['certificato_pdf'] ?? []
    );
} catch (mysqli_sql_exception) {
    http_response_code(503);
    exit('Il servizio dei certificati medici non è disponibile.');
}

if ($pagina['salvato']) {
    $_SESSION['certificato_salvato_utente'] = $pagina['utente_id'];
    header('Location: certificati-medici.php?utente_id=' . $pagina['utente_id'], true, 303);
    exit;
}

$dati = $pagina['dati'];
$certificato = $dati['certificato'] ?? null;
$salvataggioRiuscito = $metodo === 'GET'
    && $dati !== null
    && ($_SESSION['certificato_salvato_utente'] ?? null) === $pagina['utente_id'];

if ($salvataggioRiuscito) {
    unset($_SESSION['certificato_salvato_utente']);
}

$scadenzaInviata = $_POST['scadenza'] ?? '';
$scadenzaModulo = is_string($scadenzaInviata) ? $scadenzaInviata : '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificati medici - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
    <link rel="stylesheet" href="css/accesso.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="vista-reception.php">Area reception</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="dashboard-heading">
            <h1>Certificati <span>medici</span></h1>
            <a class="button secondary member-action" href="ricerca-iscritto.php">
                Cerca iscritto <span aria-hidden="true">&rarr;</span>
            </a>
        </header>

        <section class="panel wide-panel" aria-label="Seleziona iscritto">
            <form class="access-form inline-form" method="get" action="certificati-medici.php">
                <div class="access-field">
                    <label for="utente-id">Codice iscritto</label>
                    <input id="utente-id" name="utente_id" type="number" min="1"
                           value="<?= $pagina['utente_id'] ?? '' ?>" required>
                </div>
                <button class="button primary access-action" type="submit">Carica iscritto</button>
            </form>

            <?php if ($pagina['errore'] !== null && $dati === null): ?>
                <p class="access-message access-error" role="alert"><?= htmlspecialchars($pagina['errore'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </section>

        <?php if ($dati !== null): ?>
            <?php $statoCertificato = strtolower($dati['stato']); ?>
            <div class="card-grid certificate-layout">
                <section class="panel certificate-panel" aria-labelledby="certificato-attuale">
                    <header class="member-certificate-heading">
                        <h2 id="certificato-attuale">Certificato attuale</h2>
                        <span class="member-status <?= $statoCertificato ?>"><?= ucfirst($statoCertificato) ?></span>
                    </header>
                    <p class="certificate-member">
                        <strong><?= htmlspecialchars($dati['utente']['nome'] . ' ' . $dati['utente']['cognome'], ENT_QUOTES, 'UTF-8') ?></strong>
                        · Codice <?= $pagina['utente_id'] ?>
                    </p>

                    <?php if ($salvataggioRiuscito): ?>
                        <p class="access-message form-success" role="status">Certificato salvato correttamente.</p>
                    <?php endif; ?>

                    <?php if ($certificato !== null): ?>
                        <?php
                        $scadenza = new DateTimeImmutable($certificato['data_scadenza']);
                        $ultimaVerifica = new DateTimeImmutable($certificato['verificato_at']);
                        ?>
                        <dl class="record-details">
                            <div>
                                <dt>Data di scadenza</dt>
                                <dd><time datetime="<?= $scadenza->format('Y-m-d') ?>"><?= $scadenza->format('d/m/Y') ?></time></dd>
                            </div>
                            <div>
                                <dt>Ultima verifica</dt>
                                <dd><time datetime="<?= $ultimaVerifica->format('Y-m-d\TH:i:s') ?>"><?= $ultimaVerifica->format('d/m/Y H:i') ?></time></dd>
                            </div>
                        </dl>

                        <?php if ($certificato['pdf_disponibile']): ?>
                            <a class="button secondary member-action" href="scarica-certificato.php?id=<?= (int) $certificato['id'] ?>">
                                Scarica PDF <span aria-hidden="true">&darr;</span>
                            </a>
                        <?php else: ?>
                            <p class="certificate-member">PDF non disponibile.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

                <section class="panel certificate-panel" aria-labelledby="salva-certificato">
                    <h2 id="salva-certificato"><?= $certificato === null ? 'Registra certificato' : 'Aggiorna certificato' ?></h2>

                    <?php if ($pagina['errore'] !== null): ?>
                        <p class="access-message access-error" role="alert"><?= htmlspecialchars($pagina['errore'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <form class="access-form" method="post" action="certificati-medici.php" enctype="multipart/form-data">
                        <input type="hidden" name="token_csrf" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="utente_id" value="<?= $pagina['utente_id'] ?>">

                        <div class="access-field">
                            <label for="scadenza">Data di scadenza</label>
                            <input id="scadenza" name="scadenza" type="date" min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($scadenzaModulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                        </div>

                        <div class="access-field">
                            <label for="certificato-pdf">Copia PDF</label>
                            <input id="certificato-pdf" name="certificato_pdf" type="file" accept=".pdf,application/pdf"
                                   aria-describedby="aiuto-certificato" required>
                            <p class="access-hint" id="aiuto-certificato">Dimensione massima 5 MB.</p>
                        </div>

                        <label class="form-check">
                            <input type="checkbox" name="verificato" value="1" required>
                            Ho verificato il certificato cartaceo.
                        </label>

                        <button class="button primary access-action" type="submit">Salva certificato</button>
                    </form>
                </section>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
