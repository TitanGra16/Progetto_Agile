<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\CaricamentoCertificatoService;
use Ladythelemons\Fitslot\Service\SessioneService;
use Ladythelemons\Fitslot\Service\TokenCsrf;

require_once dirname(__DIR__) . '/bootstrap.php';

header('Cache-Control: no-store');

$sessione = new SessioneService();
$sessione->avvia();

$idUtente = $sessione->getIdUtente();

if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

// Solo chi possiede realmente il ruolo ISCRITTO.
$autorizzazione = new AutorizzazioneService();

if (!$autorizzazione->haRuolo('ISCRITTO')) {
    http_response_code(403);
    exit('Accesso riservato agli iscritti.');
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'GET' && $metodo !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

$token = TokenCsrf::genera(
    $_SESSION,
    'csrf_caricamento_certificato'
);

$errore = null;
$certificatoInAttesa = null;

if ($metodo === 'POST') {
    if (!TokenCsrf::valida(
        $_SESSION,
        'csrf_caricamento_certificato',
        $_POST['token_csrf'] ?? null
    )) {
        http_response_code(403);
        exit('Richiesta non valida. Ricarica la pagina.');
    }
}

try {
    $repository = new CertificatoMedicoRepository();

    if ($metodo === 'POST') {
        $servizio = new CaricamentoCertificatoService(
            $repository,
            new ArchivioPdfCertificati()
        );

        // L'ID proviene dalla sessione, mai dal modulo.
        $errore = $servizio->carica(
            $idUtente,
            $_FILES['certificato_pdf'] ?? []
        );

        if ($errore === null) {
            header(
                'Location: carica-certificato.php?caricato=1',
                true,
                303
            );
            exit;
        }
    }

    $certificatoInAttesa =
        $repository->trovaInAttesaPerUtente($idUtente);

} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage());
    http_response_code(503);
    exit('Servizio temporaneamente non disponibile.');
}

$caricamentoRiuscito =
    $certificatoInAttesa !== null
    && ($_GET['caricato'] ?? null) === '1';

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>Carica certificato medico - FitSlot</title>

    <link rel="stylesheet" href="css/portale.css">
</head>

<body>

<header class="topbar">
    <a class="wordmark" href="index.php">
        FIT<span>SLOT</span>
    </a>

    <nav aria-label="Navigazione principale">
        <a href="vista-iscritto.php">Area iscritto</a>
    </nav>
</header>

<main class="page-shell">

    <div class="page-heading">
        <span class="eyebrow">UE-51 · Area iscritto</span>

        <h1>Carica certificato medico</h1>

        <p>
            Carica il tuo certificato medico in formato PDF.
            La Reception dovrà verificarlo prima
            che venga considerato valido.
        </p>
    </div>

    <section class="panel">

        <?php if ($errore !== null): ?>

            <p role="alert">
                <?= htmlspecialchars(
                    $errore,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        <?php endif; ?>

        <?php if ($certificatoInAttesa !== null): ?>

            <h2>Certificato in attesa di verifica</h2>

            <?php if ($caricamentoRiuscito): ?>
                <p role="status">
                    Certificato caricato correttamente!
                </p>
            <?php endif; ?>

            <p>
                Il tuo certificato è stato ricevuto
                ed è <strong>IN ATTESA DI VERIFICA</strong>.
            </p>

            <p>
                <strong>Data di caricamento:</strong>
                <?= htmlspecialchars(
                    (new DateTimeImmutable(
                        $certificatoInAttesa['caricato_at']
                    ))->format('d/m/Y H:i'),
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

            <p>
                La Reception dovrà controllare il documento
                prima che possa essere considerato valido.
            </p>

        <?php else: ?>

            <h2>Seleziona il certificato</h2>

            <form
                class="registration-form"
                method="post"
                action="carica-certificato.php"
                enctype="multipart/form-data"
            >

                <input
                    type="hidden"
                    name="token_csrf"
                    value="<?= htmlspecialchars(
                        $token,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                >

                <p>
                    <label for="certificato_pdf">
                        Certificato medico (PDF, massimo 5 MB)
                    </label>
                </p>

                <p>
                    <input
                        type="file"
                        id="certificato_pdf"
                        name="certificato_pdf"
                        accept=".pdf,application/pdf"
                        required
                    >
                </p>

                <p>
                    <button type="submit">
                        Carica certificato
                    </button>
                </p>

            </form>

        <?php endif; ?>

    </section>

</main>

<footer class="site-footer">
    FitSlot · Area iscritto
</footer>

</body>
</html>

