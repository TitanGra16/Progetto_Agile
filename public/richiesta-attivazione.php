<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
use Ladythelemons\Fitslot\Service\InvioEmailAttivazione;
use Ladythelemons\Fitslot\Service\ServizioAttivazioneAccount;

require_once dirname(__DIR__) . '/bootstrap.php';

$messaggio = '';
$esito = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailRicevuta = $_POST['email'] ?? '';
    if (is_string($emailRicevuta)) {
        $email = trim($emailRicevuta);
        try {
            $servizio = new ServizioAttivazioneAccount(
                new AccountRepository(),
                new GeneratoreTokenAttivazione(),
                24,
                new UtenteRepository(),
                new InvioEmailAttivazione()
            );
            $risultato = $servizio->richiediPerEmail($email);
            $esito = $risultato['esito'];
            $messaggio = $risultato['messaggio'];
        } catch (Throwable $errore) {
            error_log($errore->getMessage());
            $esito = 'errore';
            $messaggio = 'Non è stato possibile inviare la richiesta. Riprova più tardi.';
        }
    } else {
        $esito = 'dati_non_validi';
        $messaggio = 'Inserisci un indirizzo email valido.';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attiva account - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/accesso.css">
</head>
<body class="access-page">
    <header class="topbar access-header">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
    </header>

    <main class="page-shell access-main">
        <section class="access-card" aria-labelledby="access-title">
            <div class="access-content">
                <?php if ($esito === 'inviata'): ?>
                    <h1 id="access-title">Controlla la tua email</h1>
                    <p class="access-description">Apri il link che ti abbiamo inviato per impostare la password. Se non lo trovi, controlla la cartella spam.</p>

                    <div class="access-footer">
                        <p><a href="richiesta-attivazione.php">Richiedi un altro link</a></p>
                        <p><a href="login.php">Torna alla pagina di accesso</a></p>
                    </div>
                <?php else: ?>
                    <h1 id="access-title">Attiva il tuo account</h1>
                    <p class="access-description">Inserisci l’email che hai lasciato in reception per ricevere il link di attivazione.</p>

                    <?php if ($messaggio !== ''): ?>
                        <p class="access-message access-error" role="alert">
                            <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    <?php endif; ?>

                    <form class="access-form" method="post" action="richiesta-attivazione.php">
                        <div class="access-field">
                            <label for="email">Indirizzo email</label>
                            <input id="email" name="email" type="email" autocomplete="email"
                                   value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <button class="button primary access-action" type="submit">Invia il link</button>
                    </form>

                    <div class="access-footer">
                        <p><a href="login.php">Torna alla pagina di accesso</a></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="access-visual" aria-hidden="true"></div>
        </section>
    </main>
</body>
</html>
