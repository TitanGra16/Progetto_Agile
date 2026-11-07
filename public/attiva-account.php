<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
use Ladythelemons\Fitslot\Service\ServizioAttivazioneAccount;

require_once dirname(__DIR__) . '/bootstrap.php';

$tokenRicevuto = $_GET['token'] ?? $_POST['token'] ?? '';
$token = is_string($tokenRicevuto) ? $tokenRicevuto : '';
$messaggio = '';
$esito = '';

try {
    $servizio = new ServizioAttivazioneAccount(
        new AccountRepository(),
        new GeneratoreTokenAttivazione()
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        if (!is_string($password)) {
            $password = '';
        }
        $risultato = $servizio->attiva($token, $password);
        $esito = $risultato['esito'];
        $messaggio = $risultato['messaggio'];
    } elseif ($servizio->verificaToken($token) === null) {
        $esito = 'token_non_valido';
        $messaggio = 'Il link non è valido o è scaduto.';
    }
} catch (Throwable $errore) {
    error_log($errore->getMessage());
    $esito = 'errore';
    $messaggio = 'Non è stato possibile completare l’attivazione.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imposta password - FitSlot</title>
</head>
<body>
    <h1>Imposta la password</h1>
    <?php if ($messaggio !== ''): ?>
        <p role="status"><?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($esito === '' || $esito === 'dati_non_validi'): ?>
        <form method="post" action="attiva-account.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <label for="password">Nuova password</label><br>
            <input id="password" name="password" type="password" minlength="8" required>
            <p><button type="submit">Attiva account</button></p>
        </form>
    <?php elseif ($esito === 'attivato'): ?>
        <p><a href="login.php">Vai al login</a></p>
    <?php endif; ?>
</body>
</html>
