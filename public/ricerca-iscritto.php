<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RicercaIscrittoController;
use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$sessioneUtente = new SessioneService();
if ($sessioneUtente->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

$autorizzazione = new AutorizzazioneService();
if (!$autorizzazione->haAccessoA(['RECEPTION'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

$puoGestireCertificati = $autorizzazione->haRuolo('RECEPTION');
$pagina = (new RicercaIscrittoController())->gestisciRichiesta($_SERVER['REQUEST_METHOD'], $_POST);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cerca iscritto - FitSlot</title>
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
            <h1>Cerca <span>iscritto</span></h1>
            <a class="button secondary member-action" href="registrazione-iscritto.php">
                Registra iscritto <span aria-hidden="true">&rarr;</span>
            </a>
        </header>

        <section class="panel wide-panel" aria-label="Ricerca iscritto">
            <form class="access-form inline-form" method="post" action="ricerca-iscritto.php">
                <div class="access-field">
                    <label for="termine">Nome, cognome o codice iscritto</label>
                    <input id="termine" name="termine" type="search" maxlength="100" placeholder="Es. Rossi o 42"
                           value="<?= htmlspecialchars($pagina['termine'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                           autofocus required>
                </div>
                <button class="button primary access-action" type="submit">Cerca</button>
            </form>

            <?php if ($pagina['messaggio'] !== ''): ?>
                <?php if ($pagina['esito'] === 'non_trovato'): ?>
                    <p class="access-message" role="status"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php else: ?>
                    <p class="access-message access-error" role="alert"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <?php if ($pagina['risultati'] !== []): ?>
            <?php
            $archivio = new UtenteRepository();
            $archivioCertificati = new CertificatoMedicoRepository();
            ?>
            <section class="search-results" aria-labelledby="risultati-ricerca">
                <h2 id="risultati-ricerca">Risultati della ricerca (<?= count($pagina['risultati']) ?>)</h2>

                <div class="result-list">
                    <?php foreach ($pagina['risultati'] as $utente): ?>
                        <?php
                        if (!$utente instanceof Utente) {
                            continue;
                        }

                        $idUtente = (int) $utente->getId();
                        $nomeCompleto = $utente->getNome() . ' ' . $utente->getCognome();
                        $certificato = $archivioCertificati->trovaPerUtente($idUtente);
                        $statoCertificato = CertificatoMedicoService::stato($certificato['data_scadenza'] ?? null);
                        $statoIscritto = $archivio->getStatoIscritto($idUtente);
                        ?>
                        <article class="result-card">
                            <header class="result-card-heading">
                                <div>
                                    <h3><?= htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p>Codice iscritto <strong><?= $idUtente ?></strong></p>
                                </div>
                                <?php if ($puoGestireCertificati): ?>
                                    <a class="button secondary member-action" href="certificati-medici.php?utente_id=<?= $idUtente ?>">
                                        Gestisci certificato <span aria-hidden="true">&rarr;</span>
                                    </a>
                                <?php endif; ?>
                            </header>

                            <dl class="record-details">
                                <div>
                                    <dt>Data di nascita</dt>
                                    <dd><?= htmlspecialchars($utente->getDataNascita(), ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                                <div>
                                    <dt>Recapito</dt>
                                    <dd><?= htmlspecialchars($utente->getRecapito(), ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                                <div>
                                    <dt>Email</dt>
                                    <dd><?= htmlspecialchars($utente->getEmail(), ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                                <div>
                                    <dt>Stato iscrizione</dt>
                                    <dd><?= htmlspecialchars($statoIscritto, ENT_QUOTES, 'UTF-8') ?></dd>
                                </div>
                                <div>
                                    <dt>Certificato medico</dt>
                                    <dd><span class="member-status <?= strtolower($statoCertificato) ?>"><?= ucfirst(strtolower($statoCertificato)) ?></span></dd>
                                </div>
                            </dl>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
