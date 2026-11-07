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
$password = '';

try {
    $servizio = new ServizioAttivazioneAccount(
        new AccountRepository(),
        new GeneratoreTokenAttivazione()
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $passwordRicevuta = $_POST['password'] ?? '';
        $confermaRicevuta = $_POST['conferma_password'] ?? '';
        $password = is_string($passwordRicevuta) ? $passwordRicevuta : '';
        $confermaPassword = is_string($confermaRicevuta) ? $confermaRicevuta : '';
        if ($password !== $confermaPassword) {
            $esito = 'dati_non_validi';
            $messaggio = 'Le password inserite non coincidono.';
        } else {
            $risultato = $servizio->attiva($token, $password);
            $esito = $risultato['esito'];
            $messaggio = $risultato['messaggio'];
        }
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
    <link rel="stylesheet" href="css/activation.css">
</head>
<body>
    <main class="activation-page">
        <section class="activation-card" aria-labelledby="activation-title">
            <div class="brand">
                <svg class="brand-icon" viewBox="0 0 100 60" aria-hidden="true">
                    <g fill="currentColor">
                        <rect x="7" y="20" width="10" height="20" rx="3"/>
                        <rect x="19" y="12" width="12" height="36" rx="3"/>
                        <rect x="69" y="12" width="12" height="36" rx="3"/>
                        <rect x="83" y="20" width="10" height="20" rx="3"/>
                        <rect x="28" y="26" width="44" height="8" rx="3"/>
                    </g>
                </svg>
                <h1>FITSLOT</h1>
                <p class="slogan">SUPERA I TUOI <span>LIMITI.</span></p>
            </div>

            <div class="activation-heading">
                <h2 id="activation-title">Imposta la password</h2>
                <p>Scegli una password per completare l’attivazione del tuo account.</p>
            </div>

            <?php if ($messaggio !== ''): ?>
                <p class="message <?= $esito === 'attivato' ? 'success' : 'error' ?>" role="status">
                    <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if ($esito === '' || $esito === 'dati_non_validi'): ?>
                <form class="activation-form" method="post" action="attiva-account.php">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="input-group">
                        <label for="password">Nuova password</label>
                        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <div class="input-group">
                        <label for="conferma_password">Conferma password</label>
                        <input id="conferma_password" name="conferma_password" type="password" minlength="8" autocomplete="new-password" required>
                    </div>
                    <button class="activation-button" type="submit">ATTIVA ACCOUNT</button>
                </form>
            <?php elseif ($esito === 'attivato'): ?>
                <p class="activation-footer"><a class="activation-link" href="login.php">Vai al login</a></p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
