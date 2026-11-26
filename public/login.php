<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\AutenticazioneController;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$sessione->avvia();

$errore = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $emailInviata = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!is_string($emailInviata) || !is_string($password)) {
        $errore = 'Dati non validi.';
    } else {
        $email = trim($emailInviata);

        if ($email === '' || $password === '') {
            $errore = 'Inserisci email e password.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errore = 'Inserisci un indirizzo email valido.';
        } else {
            try {
                $controller = new AutenticazioneController();
                $idUtente = $controller->accedi($email, $password);
                
                if ($idUtente === null) {
                    $errore = 'Email o password errata.';
                } else {
                    $sessione->registraAccesso($idUtente);
                    header('Location: homepage.php', true, 303);
                    exit;
                }
            } catch (Throwable $e) {
                error_log($e->getMessage());
                $errore = 'Si è verificato un problema durante il login.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accedi - FitSlot</title>
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
                <h1 id="access-title">Accedi</h1>

                <?php if ($errore !== null): ?>
                    <p class="access-message access-error" role="alert">
                        <?= htmlspecialchars($errore, ENT_QUOTES, 'UTF-8') ?>
                    </p>
                <?php endif; ?>

                <form class="access-form" method="post" action="login.php">
                    <div class="access-field">
                        <label for="email">Indirizzo email</label>
                        <input id="email" name="email" type="email" autocomplete="username"
                               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="access-field">
                        <label for="password">Password</label>
                        <div class="access-password">
                            <input id="password" name="password" type="password" autocomplete="current-password" required>
                            <button class="access-toggle" type="button" data-password-toggle
                                    aria-controls="password" aria-label="Mostra password" hidden>Mostra</button>
                        </div>
                    </div>

                    <button class="button primary access-action" type="submit">Accedi</button>
                </form>

                <div class="access-footer">
                    <p>Primo accesso? <a href="richiesta-attivazione.php">Attiva account</a></p>
                    <p>Per registrarti, rivolgiti alla reception.</p>
                </div>
            </div>

            <div class="access-visual" aria-hidden="true"></div>
        </section>
    </main>
</body>
</html>
