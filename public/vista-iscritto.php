<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$autorizzazione = new AutorizzazioneService();
$idUtente = $sessione->getIdUtente();

if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

if (!$autorizzazione->haAccessoA(['ISCRITTO'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

$mostraCertificato = $autorizzazione->haRuolo('ISCRITTO');
$statoCertificato = null;
$scadenza = null;

if ($mostraCertificato) {
    try {
        $certificato = (new CertificatoMedicoRepository())->trovaPerUtente($idUtente);
        $dataScadenza = $certificato['data_scadenza'] ?? null;
        $scadenza = $dataScadenza === null ? null : new DateTimeImmutable($dataScadenza);
        $statoCertificato = CertificatoMedicoService::stato($dataScadenza);
    } catch (Throwable $errore) {
        error_log('Area iscritto: ' . $errore->getMessage());
    }
}

[$etichettaCertificato, $indicazioneCertificato] = match ($statoCertificato) {
    'VALIDO' => ['Valido', null],
    'SCADUTO' => ['Scaduto', 'Consegna un certificato aggiornato in reception.'],
    'ASSENTE' => ['Assente', 'Consegna il tuo certificato medico in reception.'],
    default => ['Stato non disponibile', 'Riprova più tardi.'],
};

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Area iscritto - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="homepage.php">Le mie aree</a>
            <form class="logout-form" method="post" action="logout.php">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <section class="member-hero" aria-labelledby="member-title">
            <h1 id="member-title">Area <span>iscritto</span></h1>
            <a class="button primary member-action" href="profilo.php">
                Il mio profilo <span aria-hidden="true">&rarr;</span>
            </a>
        </section>

        <?php if ($mostraCertificato): ?>
            <section class="member-certificate" aria-labelledby="member-certificate-title">
                <div>
                    <div class="member-certificate-heading">
                        <h2 id="member-certificate-title">Certificato medico</h2>
                        <span class="member-status <?= strtolower($statoCertificato ?? 'indisponibile') ?>">
                            <?= $etichettaCertificato ?>
                        </span>
                    </div>

                    <?php if ($scadenza !== null): ?>
                        <p>Scadenza <time datetime="<?= $scadenza->format('Y-m-d') ?>"><?= $scadenza->format('d/m/Y') ?></time></p>
                    <?php endif; ?>
                    <?php if ($indicazioneCertificato !== null): ?>
                        <p><?= $indicazioneCertificato ?></p>
                    <?php endif; ?>
                </div>

                <a class="button secondary member-action" href="profilo.php#titolo-certificato">
                    Dettagli certificato <span aria-hidden="true">&rarr;</span>
                </a>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
