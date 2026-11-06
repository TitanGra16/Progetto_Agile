<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RegistrazioneIscrittoController;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$controllore = new RegistrazioneIscrittoController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['risultato_registrazione'] = $controllore->gestisciRichiesta('POST', $_POST, $_SESSION);
    // Il redirect evita che un aggiornamento della pagina ripeta l'invio.
    header('Location: index.php', true, 303);
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
</head>
<body>
    <h1>Registrazione iscritto</h1>
    <?php if ($pagina['messaggio'] !== ''): ?>
        <p role="status"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($pagina['esito'] === 'registrato'): ?>
        <p>Codice iscritto: <strong><?= (int) $pagina['codice_iscritto'] ?></strong></p>
    <?php endif; ?>

    <form method="post" action="index.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($pagina['token'], ENT_QUOTES, 'UTF-8') ?>">
        <p>I campi contrassegnati con * sono obbligatori.</p>
        <p>
            <label for="nome">Nome *</label><br>
            <input id="nome" name="nome" type="text" maxlength="100" placeholder="Es. Anna" value="<?= htmlspecialchars($pagina['dati']['nome'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="cognome">Cognome *</label><br>
            <input id="cognome" name="cognome" type="text" maxlength="100" placeholder="Es. Rossi" value="<?= htmlspecialchars($pagina['dati']['cognome'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="data_nascita">Data di nascita *</label><br>
            <input id="data_nascita" name="data_nascita" type="date" max="<?= date('Y-m-d') ?>" placeholder="Es. 15/05/2000" value="<?= htmlspecialchars($pagina['dati']['data_nascita'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="email">Email *</label><br>
            <input id="email" name="email" type="email" maxlength="255" placeholder="Es. anna.rossi@email.com" value="<?= htmlspecialchars($pagina['dati']['email'], ENT_QUOTES, 'UTF-8') ?>" required>
        </p>
        <p>
            <label for="recapito">Telefono *</label><br>
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
</body>
</html>
