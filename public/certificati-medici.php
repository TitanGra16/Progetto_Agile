<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\CertificatoMedicoController;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/bootstrap.php';

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
    header('Location: certificati-medici.php?utente_id=' . $pagina['utente_id'], true, 303);
    exit;
}

$dati = $pagina['dati'];
$certificato = $dati['certificato'] ?? null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificati medici - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/certificati-medici.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
    </header>
    <main class="page-shell pagina-certificati">
        <h1>Certificati medici</h1>
        <section class="panel" aria-labelledby="cerca-iscritto">
            <h2 id="cerca-iscritto">Cerca iscritto</h2>
            <form class="registration-form" method="get" action="certificati-medici.php">
                <p>
                    <label for="utente-id">Identificativo iscritto</label>
                    <input id="utente-id" name="utente_id" type="number" min="1"
                           value="<?= $pagina['utente_id'] ?? '' ?>" required>
                </p>
                <button type="submit">Carica iscritto</button>
            </form>
            <?php if ($pagina['errore'] !== null): ?>
                <p role="status"><?= htmlspecialchars($pagina['errore'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </section>

        <?php if ($dati !== null): ?>
            <section class="panel" aria-labelledby="certificato-attuale">
                <h2 id="certificato-attuale">Certificato attuale</h2>
                <p>
                    <?= htmlspecialchars($dati['utente']['nome'] . ' ' . $dati['utente']['cognome'], ENT_QUOTES, 'UTF-8') ?>
                    · Iscritto <?= $pagina['utente_id'] ?>
                </p>
                <p>Stato <strong><?= htmlspecialchars($dati['stato'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                <?php if ($certificato !== null): ?>
                    <p>Scadenza <?= htmlspecialchars($certificato['data_scadenza'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Ultima verifica <?= htmlspecialchars($certificato['verificato_at'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ($certificato['pdf_disponibile']): ?>
                        <a class="button secondary" href="scarica-certificato.php?id=<?= (int) $certificato['id'] ?>">
                            Scarica PDF
                        </a>
                    <?php else: ?>
                        <p>PDF non disponibile.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </section>

            <section class="panel" aria-labelledby="salva-certificato">
                <h2 id="salva-certificato"><?= $certificato === null ? 'Registra certificato' : 'Aggiorna certificato' ?></h2>
                <form class="registration-form" method="post" action="certificati-medici.php" enctype="multipart/form-data">
                    <input type="hidden" name="utente_id" value="<?= $pagina['utente_id'] ?>">
                    <p>
                        <label for="scadenza">Data di scadenza</label>
                        <input id="scadenza" name="scadenza" type="date" min="<?= date('Y-m-d') ?>" required>
                    </p>
                    <p>
                        <label for="certificato-pdf">Copia PDF (massimo 5 MB)</label>
                        <input id="certificato-pdf" name="certificato_pdf" type="file" accept=".pdf,application/pdf" required>
                    </p>
                    <p>
                        <label class="conferma-certificato">
                            <input type="checkbox" name="verificato" value="1" required>
                            Ho verificato il certificato cartaceo.
                        </label>
                    </p>
                    <button type="submit">Salva certificato</button>
                </form>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
