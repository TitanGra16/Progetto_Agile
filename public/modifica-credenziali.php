<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\ModificaCredenzialiService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$idUtente = $sessione->getIdUtente();

if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_modifica_credenziali'])) {
    $_SESSION['csrf_modifica_credenziali'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_modifica_credenziali'];
$risultatoEmail = null;
$risultatoPassword = null;
$valoreEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRicevuto = $_POST['csrf_token'] ?? '';

    if (!is_string($tokenRicevuto) || !hash_equals($csrfToken, $tokenRicevuto)) {
        http_response_code(403);
        exit('Richiesta non valida. Ricarica la pagina e riprova.');
    }

    $azione = $_POST['azione'] ?? '';
    $passwordAttuale = $_POST['password_attuale'] ?? '';

    if ($azione === 'modifica_email') {
        $nuovaEmail = $_POST['nuova_email'] ?? '';

        if (!is_string($nuovaEmail) || !is_string($passwordAttuale)) {
            $risultatoEmail = ['esito' => 'errore', 'messaggio' => 'Dati inseriti non validi.'];
        } else {
            try {
                $servizio = new ModificaCredenzialiService();
                $risultatoEmail = $servizio->modificaEmail($idUtente, $nuovaEmail, $passwordAttuale);
            } catch (Throwable $errore) {
                error_log((string) $errore);
                $risultatoEmail = ['esito' => 'errore', 'messaggio' => 'Impossibile aggiornare l’email. Riprova.'];
            }
        }

        if (is_string($nuovaEmail) && $risultatoEmail['esito'] === 'errore') {
            $valoreEmail = trim($nuovaEmail);
        }
    } elseif ($azione === 'modifica_password') {
        $nuovaPassword = $_POST['nuova_password'] ?? '';
        $confermaPassword = $_POST['conferma_password'] ?? '';

        if (!is_string($passwordAttuale) || !is_string($nuovaPassword) || !is_string($confermaPassword)) {
            $risultatoPassword = ['esito' => 'errore', 'messaggio' => 'Dati inseriti non validi.'];
        } else {
            try {
                $servizio = new ModificaCredenzialiService();
                $risultatoPassword = $servizio->modificaPassword($idUtente, $passwordAttuale, $nuovaPassword, $confermaPassword);
            } catch (Throwable $errore) {
                error_log((string) $errore);
                $risultatoPassword = ['esito' => 'errore', 'messaggio' => 'Impossibile aggiornare la password. Riprova.'];
            }
        }
    }
}

$emailAggiornata = ($risultatoEmail['esito'] ?? null) === 'successo';
$passwordAggiornata = ($risultatoPassword['esito'] ?? null) === 'successo';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifica credenziali - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
    <link rel="stylesheet" href="css/profilo.css">
    <link rel="stylesheet" href="css/accesso.css">
    <link rel="stylesheet" href="css/modifica-credenziali.css">
    <script src="js/login.js" defer></script>
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="homepage.php">Le mie aree</a>
            <form class="logout-form" method="post" action="logout.php">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="profile-heading">
            <a class="profile-back" href="profilo.php">
                <span aria-hidden="true">&larr;</span> Torna al profilo
            </a>
            <h1>Modifica <span>credenziali</span></h1>
        </header>

        <div class="credentials-grid">
            <section class="panel profile-panel credentials-panel" id="modifica-email" aria-labelledby="titolo-modifica-email">
                <h2 id="titolo-modifica-email">Modifica email</h2>

                <?php if ($risultatoEmail !== null): ?>
                    <p class="access-message <?= $emailAggiornata ? 'credentials-success' : 'access-error' ?>"
                       role="<?= $emailAggiornata ? 'status' : 'alert' ?>">
                        <?= htmlspecialchars($risultatoEmail['messaggio'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <form class="access-form" action="modifica-credenziali.php#modifica-email" method="post">
                    <input type="hidden" name="azione" value="modifica_email">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="access-field">
                        <label for="nuova_email">Nuova email</label>
                        <input type="email" id="nuova_email" name="nuova_email" maxlength="255" autocomplete="email"
                               value="<?= htmlspecialchars($valoreEmail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
                    </div>

                    <div class="access-field">
                        <label for="password_email">Password attuale</label>
                        <div class="access-password">
                            <input type="password" id="password_email" name="password_attuale" autocomplete="current-password" required>
                            <button class="access-toggle" type="button" data-password-toggle
                                    aria-controls="password_email" aria-label="Mostra password" hidden>Mostra</button>
                        </div>
                    </div>

                    <button class="button primary access-action" type="submit">Aggiorna email</button>
                </form>
            </section>

            <section class="panel profile-panel credentials-panel" id="modifica-password" aria-labelledby="titolo-modifica-password">
                <h2 id="titolo-modifica-password">Modifica password</h2>

                <?php if ($risultatoPassword !== null): ?>
                    <p class="access-message <?= $passwordAggiornata ? 'credentials-success' : 'access-error' ?>"
                       role="<?= $passwordAggiornata ? 'status' : 'alert' ?>">
                        <?= htmlspecialchars($risultatoPassword['messaggio'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <form class="access-form" action="modifica-credenziali.php#modifica-password" method="post">
                    <input type="hidden" name="azione" value="modifica_password">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="access-field">
                        <label for="password_attuale">Password attuale</label>
                        <input type="password" id="password_attuale" name="password_attuale" autocomplete="current-password" required>
                    </div>

                    <div class="access-field">
                        <label for="nuova_password">Nuova password</label>
                        <input type="password" id="nuova_password" name="nuova_password" autocomplete="new-password"
                               minlength="8" aria-describedby="requisiti-password" required>
                        <p class="access-hint" id="requisiti-password">Usa almeno 8 caratteri e una password diversa da quella attuale.</p>
                    </div>

                    <div class="access-field">
                        <label for="conferma_password">Conferma nuova password</label>
                        <input type="password" id="conferma_password" name="conferma_password"
                               autocomplete="new-password" minlength="8" required>
                    </div>

                    <button class="access-toggle" type="button" data-password-toggle
                            aria-controls="password_attuale nuova_password conferma_password" hidden>Mostra password</button>
                    <button class="button primary access-action" type="submit">Aggiorna password</button>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
