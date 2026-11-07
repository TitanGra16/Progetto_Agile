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
</head>
<body>
    <h1>Attiva il tuo account FitSlot</h1>
    <?php if ($messaggio !== ''): ?>
        <p role="status"><?= htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="richiesta-attivazione.php">
        <label for="email">Email</label><br>
        <input id="email" name="email" type="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
        <p><button type="submit">Invia link di attivazione</button></p>
    </form>
    <p><a href="login.php">Torna al login</a></p>
</body>
</html>
