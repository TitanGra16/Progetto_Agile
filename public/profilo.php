<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$idUtente = $sessione->getIdUtente();

if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

$utente = (new UtenteRepository())->trovaPerId($idUtente);

if ($utente === null) {
    http_response_code(404);
    exit('Profilo utente non trovato.');
}

$ruoli = (new AutorizzazioneService())->getRuoliUtente();
$mostraCertificato = in_array('ISCRITTO', $ruoli, true);
$statoCertificato = null;
$scadenza = null;

if ($mostraCertificato) {
    try {
        $certificato = (new CertificatoMedicoRepository())->trovaPerUtente($idUtente);
        $dataScadenza = $certificato['data_scadenza'] ?? null;
        $scadenza = $dataScadenza === null ? null : new DateTimeImmutable($dataScadenza);
        $statoCertificato = CertificatoMedicoService::stato($dataScadenza);
    } catch (Throwable $errore) {
        error_log('Profilo: errore recupero certificato: ' . $errore->getMessage());
    }
}

[$etichettaCertificato, $indicazioneCertificato] = match ($statoCertificato) {
    'VALIDO' => ['Valido', null],
    'SCADUTO' => ['Scaduto', 'Consegna un certificato aggiornato in reception per continuare a usare i servizi del centro.'],
    'ASSENTE' => ['Assente', 'Consegna il tuo certificato medico in reception per poter usare i servizi del centro.'],
    default => ['Stato non disponibile', 'Riprova più tardi o chiedi informazioni in reception.'],
};
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Il mio profilo - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
    <link rel="stylesheet" href="css/profilo.css">
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
        <header class="profile-heading">
            <?php if ($mostraCertificato): ?>
                <a class="profile-back" href="vista-iscritto.php">
                    <span aria-hidden="true">&larr;</span> Area iscritto
                </a>
            <?php endif; ?>
            <h1>Il mio <span>profilo</span></h1>
        </header>

        <div class="profile-grid">
            <section class="panel profile-panel" aria-labelledby="dati-personali">
                <h2 id="dati-personali">Dati personali</h2>

                <dl class="profile-details">
                    <div>
                        <dt>Nome</dt>
                        <dd><?= htmlspecialchars($utente['nome'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div>
                        <dt>Cognome</dt>
                        <dd><?= htmlspecialchars($utente['cognome'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div class="profile-detail-wide">
                        <dt>Indirizzo email</dt>
                        <dd><?= htmlspecialchars($utente['email'], ENT_QUOTES, 'UTF-8') ?></dd>
                    </div>
                    <div class="profile-detail-wide">
                        <dt>Ruoli</dt>
                        <dd class="profile-roles">
                            <?php if ($ruoli === []): ?>
                                Nessun ruolo assegnato
                            <?php else: ?>
                                <?php foreach ($ruoli as $ruolo): ?>
                                    <span><?= htmlspecialchars(ucfirst(strtolower($ruolo)), ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </dd>
                    </div>
                </dl>

                <a class="button primary member-action" href="modifica-credenziali.php">
                    Modifica credenziali <span aria-hidden="true">&rarr;</span>
                </a>
            </section>

            <?php if ($mostraCertificato): ?>
                <section class="panel profile-panel profile-certificate" aria-labelledby="titolo-certificato">
                    <div class="member-certificate-heading">
                        <h2 id="titolo-certificato">Certificato medico</h2>
                        <span class="member-status <?= strtolower($statoCertificato ?? 'indisponibile') ?>">
                            <?= $etichettaCertificato ?>
                        </span>
                    </div>

                    <?php if ($scadenza !== null): ?>
                        <dl class="profile-expiry">
                            <dt>Data di scadenza</dt>
                            <dd><time datetime="<?= $scadenza->format('Y-m-d') ?>"><?= $scadenza->format('d/m/Y') ?></time></dd>
                        </dl>
                    <?php endif; ?>

                    <?php if ($indicazioneCertificato !== null): ?>
                        <p><?= $indicazioneCertificato ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
