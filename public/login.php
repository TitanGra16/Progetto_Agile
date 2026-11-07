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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Accedi - FitSlot</title>

    <link rel="stylesheet" href="css/login.css">
    <script src="js/login.js" defer></script>
</head>

<body>

    <main class="login-page">

        <section class="login-card" aria-labelledby="login-title">

            <!-- Logo -->
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
                <p class="slogan">
                    SUPERA I TUOI <span>LIMITI.</span>
                </p>
            </div>

            <!-- Titolo -->
            <div class="login-heading">
                <h2 id="login-title">Accedi al tuo account</h2>
                <p>Torna ad allenarti con FitSlot.</p>
            </div>

            <!-- Messaggi di errore -->
            <?php if ($errore !== null): ?>
                <p class="error-message" role="alert">
                    <?= htmlspecialchars($errore, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <!-- Form di login -->
            <form action="login.php" method="POST" class="login-form">

                <!-- Email -->
                <div class="input-group">
                    <label for="email" class="sr-only">
                        Indirizzo email
                    </label>

                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>
                    </span>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Indirizzo email"
                        autocomplete="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="input-group">
                    <label for="password" class="sr-only">
                        Password
                    </label>

                    <span class="field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <rect x="5" y="10" width="14" height="11" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>
                    </span>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="toggle-password"
                        data-password-toggle
                        aria-label="Mostra password"
                        aria-pressed="false"
                    >
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>

                <!-- Pulsante Accedi -->
                <button type="submit" class="login-button">
                    ACCEDI
                </button>

            </form>

            <!-- Registrazione -->
            <p class="register-text">
                Non hai ancora un account? Passa in una nostra sede!
                <!--<span class="register-highlight">Registrati</span> -->
            </p>

        </section>

    </main>

</body>
</html>
