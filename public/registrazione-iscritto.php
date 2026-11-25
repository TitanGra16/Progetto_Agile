<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RegistrazioneIscrittoController;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$sessioneUtente = new SessioneService();
if ($sessioneUtente->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

if (!(new AutorizzazioneService())->haAccessoA(['RECEPTION'])) {
    http_response_code(403);
    exit('Accesso non autorizzato.');
}

$controllore = new RegistrazioneIscrittoController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['risultato_registrazione'] = $controllore->gestisciRichiesta('POST', $_POST, $_SESSION);
    // Il redirect evita che un aggiornamento della pagina ripeta l'invio.
    header('Location: registrazione-iscritto.php', true, 303);
    exit;
}
$pagina = $controllore->gestisciRichiesta('GET', [], $_SESSION);
if (isset($_SESSION['risultato_registrazione'])) {
    $pagina = $_SESSION['risultato_registrazione'];
    unset($_SESSION['risultato_registrazione']);
}
$dati = $pagina['dati'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registra iscritto - FitSlot</title>
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
            <h1>Registra <span>iscritto</span></h1>
            <a class="button secondary member-action" href="ricerca-iscritto.php">
                Cerca iscritto <span aria-hidden="true">&rarr;</span>
            </a>
        </header>

        <section class="panel" aria-label="Modulo di registrazione">
            <p class="access-hint">Tutti i campi sono obbligatori.</p>

            <?php if ($pagina['esito'] === 'registrato'): ?>
                <p class="access-message form-success" role="status">
                    <?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?>
                    Codice iscritto <strong><?= (int) $pagina['codice_iscritto'] ?></strong>
                </p>
            <?php elseif ($pagina['richiede_conferma']): ?>
                <p role="status"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php elseif ($pagina['messaggio'] !== ''): ?>
                <p class="access-message access-error" role="alert"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="access-form two-column-form" method="post" action="registrazione-iscritto.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($pagina['token'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="access-field">
                    <label for="nome">Nome</label>
                    <input id="nome" name="nome" type="text" maxlength="100"
                           value="<?= htmlspecialchars($dati['nome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </div>

                <div class="access-field">
                    <label for="cognome">Cognome</label>
                    <input id="cognome" name="cognome" type="text" maxlength="100"
                           value="<?= htmlspecialchars($dati['cognome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </div>

                <div class="access-field">
                    <label for="data_nascita">Data di nascita</label>
                    <input id="data_nascita" name="data_nascita" type="date" max="<?= date('Y-m-d') ?>"
                           value="<?= htmlspecialchars($dati['data_nascita'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </div>

                <div class="access-field">
                    <label for="recapito">Telefono</label>
                    <input id="recapito" name="recapito" type="tel" maxlength="190"
                           value="<?= htmlspecialchars($dati['recapito'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </div>

                <div class="access-field form-wide">
                    <label for="email">Indirizzo email</label>
                    <input id="email" name="email" type="email" maxlength="255"
                           value="<?= htmlspecialchars($dati['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                </div>

                <div class="form-actions form-wide">
                    <?php if ($pagina['richiede_conferma']): ?>
                        <button class="button secondary access-action" type="submit" name="azione" value="modifica" formnovalidate>
                            Rivedi i dati
                        </button>
                        <button class="button primary access-action" type="submit" name="azione" value="conferma">
                            Conferma comunque
                        </button>
                    <?php else: ?>
                        <button class="button primary access-action" type="submit" name="azione" value="registra">
                            Conferma registrazione
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
