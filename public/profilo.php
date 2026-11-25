<?php

declare(strict_types=1);


use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use Ladythelemons\Fitslot\Service\SessioneService;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$idUtente = $sessione->getIdUtente();

/* verifico che l'utente sia loggato altrimento lo mando alla pagina di login*/
if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

$repository = new UtenteRepository();
$utente = $repository->trovaPerId($idUtente);

if ($utente === null) {
    http_response_code(404);
    exit('Profilo utente non trovato.');
}
// Recupero i ruoli associati all'utente autenticato
$autorizzazione = new AutorizzazioneService();
$ruoli = $autorizzazione->getRuoliUtente();

// UE-53: recupero il certificato medico dell'utente autenticato
$certificatoRepository = new CertificatoMedicoRepository();
$certificato = $certificatoRepository->trovaPerUtente($idUtente);

// Calcolo lo stato del certificato usando il Service della UE-05
$statoCertificato = CertificatoMedicoService::stato(
    $certificato['data_scadenza'] ?? null
);

?>


<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Il mio profilo - FitSlot</title>

    <link rel="stylesheet" href="css/portale.css">
</head>

<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">
            FIT<span>SLOT</span>
        </a>

        <nav aria-label="Navigazione principale">
            <a href="homepage.php">Torna nell'area personale</a>
        </nav>
    </header>


    <main class="page-shell">

        <div class="page-heading">
            <span class="eyebrow">Area personale</span>
            <h1>Il mio profilo</h1>
            <p>Visualizza le informazioni del tuo account.</p>

    <section class="panel">

    <h2>Informazioni personali</h2>

    <p>
        <strong>Nome:</strong>
        <?= htmlspecialchars($utente['nome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p>
        <strong>Cognome:</strong>
        <?= htmlspecialchars($utente['cognome'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p>
        <strong>Email:</strong>
        <?= htmlspecialchars($utente['email'], ENT_QUOTES, 'UTF-8') ?>
    </p>
                
    <p>
        <strong>Ruoli:</strong>
        <?= htmlspecialchars(
            $ruoli !== [] ? implode(', ', $ruoli) : 'Nessun ruolo assegnato',
            ENT_QUOTES,
            'UTF-8'
        ) ?>
    </p>

    <a href="modifica-credenziali.php" class="button primary">
        Modifica credenziali
    </a>

</section>

<?php if (in_array('ISCRITTO', $ruoli, true)): ?>

<section class="panel panel-certificato" aria-labelledby="titolo-certificato">
    <h2 id="titolo-certificato">Il mio certificato medico</h2>

    <?php if ($statoCertificato === 'VALIDO'): ?>

        <p>
            <strong>Stato:</strong>
            <span class="stato-certificato-valido">Valido</span>
        </p>

        <p>
            <strong>Data di scadenza:</strong>
            <?= htmlspecialchars(
                (new DateTimeImmutable($certificato['data_scadenza']))->format('d/m/Y'),
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </p>

    <?php elseif ($statoCertificato === 'SCADUTO'): ?>

        <div class="avviso-certificato" role="alert">
            <strong>ATTENZIONE: CERTIFICATO SCADUTO!</strong>

            <p>
                Il tuo certificato medico è scaduto.
                Consegna un nuovo certificato in reception.
            </p>

            <p>
                Senza un certificato medico valido
                non potrai utilizzare i servizi della palestra.
            </p>
        </div>

    <?php else: ?>

        <div class="avviso-certificato" role="alert">
            <strong>ATTENZIONE: CERTIFICATO MEDICO ASSENTE!</strong>

            <p>
                Non hai ancora consegnato il certificato medico.
            </p>

            <p>
                Senza un certificato medico valido
                non potrai utilizzare i servizi della palestra.
                Consegnalo il prima possibile in reception.
            </p>
        </div>

    <?php endif; ?>

</section>

<?php endif; ?>

        </div>

    </main>

</body>
</html>
