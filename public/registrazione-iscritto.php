<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RegistrazioneIscrittoController;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrazione iscritto - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="index.php">Portale</a>
        </nav>
    </header>
    <main class="page-shell">
        <div class="page-heading">
            <span class="eyebrow">UE-01 · Reception</span>
            <h1>Registra un iscritto</h1>
            <p>Inserisci i dati richiesti per creare una nuova anagrafica.</p>
        </div>
        <section class="panel" aria-label="Modulo di registrazione">
    <?php if ($pagina['messaggio'] !== ''): ?>
        <p role="status"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($pagina['esito'] === 'registrato'): ?>
        <p>Codice iscritto: <strong><?= (int) $pagina['codice_iscritto'] ?></strong></p>
    <?php endif; ?>

    <form class="registration-form" method="post" action="registrazione-iscritto.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($pagina['token'], ENT_QUOTES, 'UTF-8') ?>">
        <p>I campi contrassegnati con <span class="obbligatorio">*</span> sono obbligatori.</p>
        <p>
            <label for="nome">Nome <span class="obbligatorio">*</span></label><br>
            <input id="nome" name="nome" type="text" maxlength="100" placeholder="Es. Anna" value="<?= htmlspecialchars($pagina['dati']['nome'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="cognome">Cognome <span class="obbligatorio">*</span></label><br>
            <input id="cognome" name="cognome" type="text" maxlength="100" placeholder="Es. Rossi" value="<?= htmlspecialchars($pagina['dati']['cognome'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="data_nascita">Data di nascita <span class="obbligatorio">*</span></label><br>
            <input id="data_nascita" name="data_nascita" type="date" max="<?= date('Y-m-d') ?>" placeholder="Es. 15/05/2000" value="<?= htmlspecialchars($pagina['dati']['data_nascita'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="email">Email <span class="obbligatorio">*</span></label><br>
            <input id="email" name="email" type="email" maxlength="255" placeholder="Es. anna.rossi@email.com" value="<?= htmlspecialchars($pagina['dati']['email'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="recapito">Telefono <span class="obbligatorio">*</span></label><br>
            <input id="recapito" name="recapito" type="text" maxlength="190" placeholder="Es. 333 1234567" value="<?= htmlspecialchars($pagina['dati']['recapito'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <?php if ($pagina['richiede_conferma']): ?>
            <p>Conferma soltanto dopo aver verificato che vuoi registrare comunque questi dati.</p>
            <button type="submit" name="azione" value="modifica" formnovalidate>Rivedi i dati</button>
            <button type="submit" name="azione" value="conferma">Conferma comunque</button>
        <?php else: ?>
            <p><button type="submit" name="azione" value="registra">Conferma registrazione</button></p>
        <?php endif; ?>
    </form>
        </section>
    </main>
</body>
</html>
