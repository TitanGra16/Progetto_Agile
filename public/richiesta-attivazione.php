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
                <h2 id="activation-title">Attiva il tuo account</h2>
                <p>Inserisci l’email con cui sei stato registrato per ricevere il link di attivazione.</p>
            </div>

            <?php if ($messaggio !== ''): ?>
                <p class="message <?= $esito === 'inviata' ? 'success' : 'error' ?>" role="status">
                    <?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form class="activation-form" method="post" action="richiesta-attivazione.php">
                <div class="input-group">
                    <label for="email">Indirizzo email</label>
                    <input id="email" name="email" type="email" autocomplete="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <button class="activation-button" type="submit">INVIA LINK</button>
            </form>
            <p class="activation-footer"><a class="activation-link" href="login.php">Torna al login</a></p>
        </section>
    </main>
</body>
</html>
