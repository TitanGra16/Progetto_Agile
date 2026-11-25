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
    <title>Completa l’attivazione - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/accesso.css">
    <script src="js/login.js" defer></script>
</head>
<body class="access-page">
    <header class="topbar access-header">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
    </header>

    <main class="page-shell access-main">
        <section class="access-card" aria-labelledby="access-title">
            <div class="access-content">
                <?php if ($esito === '' || $esito === 'dati_non_validi'): ?>
                    <h1 id="access-title">Imposta la password</h1>

                    <?php if ($messaggio !== ''): ?>
                        <p class="access-message access-error" role="alert">
                            <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>

                    <form class="access-form" method="post" action="attiva-account.php">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                        <div class="access-field">
                            <label for="password">Nuova password</label>
                            <input id="password" name="password" type="password" minlength="8"
                                   autocomplete="new-password" aria-describedby="password-help" required>
                            <p class="access-hint" id="password-help">Almeno 8 caratteri.</p>
                        </div>

                        <div class="access-field">
                            <label for="conferma_password">Conferma password</label>
                            <input id="conferma_password" name="conferma_password" type="password" minlength="8"
                                   autocomplete="new-password" required>
                        </div>

                        <button class="access-toggle" type="button" data-password-toggle
                                aria-controls="password conferma_password" hidden>Mostra password</button>
                        <button class="button primary access-action" type="submit">Attiva account</button>
                    </form>
                <?php elseif ($esito === 'attivato'): ?>
                    <h1 id="access-title">Account attivato</h1>
                    <p class="access-description">Ora puoi accedere a FitSlot con la password che hai appena scelto.</p>
                    <a class="button primary access-action access-next" href="login.php">Vai alla pagina di accesso</a>
                <?php else: ?>
                    <h1 id="access-title">Attivazione non completata</h1>
                    <p class="access-message access-error" role="alert">
                        <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <a class="button primary access-action access-next" href="richiesta-attivazione.php">Richiedi un nuovo link</a>
                <?php endif; ?>
            </div>

            <div class="access-visual" aria-hidden="true"></div>
        </section>
    </main>
</body>
</html>
