<?php

/* specifico al compilatore di PHP di usare il tipo di dato corretto per le variabili */
declare(strict_types=1);

use Ladythelemons\Fitslot\Service\ModificaCredenzialiService;
use Ladythelemons\Fitslot\Service\SessioneService;

/* carico l'ambiente di lavoro e le dipendenze del progetto */
require_once dirname(__DIR__) . '/bootstrap.php';

/* recupero la sessione dell'utente loggato e il suo ID*/
$sessione = new SessioneService();
$idUtente = $sessione->getIdUtente();

/* se l'utente non risulta loggato viene rimandato alla pagina di login */
if ($idUtente === null) {
    header('Location: login.php');
    exit;
}

/* genera un token CSRF di sicurezza se non esiste già nella sessione dell'utente */
if (empty($_SESSION['csrf_modifica_credenziali'])) {
    $_SESSION['csrf_modifica_credenziali'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_modifica_credenziali'];

$risultatoEmail = null;
$risultatoPassword = null;

/* verifico che la richiesta sia di tipo POST e quindi che l'utente abbia inviato il form per modificare le credenziali */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* recupero il token CSRF inviato dal form e lo confronto con quello presente nella sessione dell'utente per motivi di sicurezza */
    $tokenRicevuto = $_POST['csrf_token'] ?? '';


    /* se il token CSRF non è valido, restituisco un errore 403 e interrompo l'esecuzione dello script */
    if (!is_string($tokenRicevuto) ||!hash_equals($csrfToken, $tokenRicevuto)) {
        http_response_code(403);
        exit('Richiesta non valida. Ricarica la pagina e riprova.');
    }

    /* se l'utente sta richiedendo di modificare l'email entro in questo blocco di codice */
    if (($_POST['azione'] ?? '') === 'modifica_email') {
        
        /* recupero i dati inseriti dall'utente nel form di modifica email */
        $nuovaEmail = $_POST['nuova_email'] ?? '';
        $passwordAttuale = $_POST['password_attuale'] ?? '';

        /* se i dati inseriti risultano non validi viene restituito un messaggio di errore*/ 
        if (!is_string($nuovaEmail) || !is_string($passwordAttuale)) {
            $risultatoEmail = ['esito' => 'errore', 'messaggio' => 'Dati inseriti non validi.'];
        } else {

            /* altrimenti viene chiamato il servizio di modifica credenziali e si prova ad aggiornare l'email*/
            try {
                $service = new ModificaCredenzialiService();

                $risultatoEmail = $service->modificaEmail($idUtente, $nuovaEmail, $passwordAttuale);
            } catch (\Throwable $errore) {
                error_log((string) $errore);

                /* in caso di errore viene restituito un messaggio generico all'utente*/
                $risultatoEmail = ['esito' => 'errore', 'messaggio' => 'Impossibile aggiornare l’email. Riprova.'];
            }
        }
    }

    /* se l'utente sta richiedendo di modificare la password entro in questo blocco di codice */
    if (($_POST['azione'] ?? '') === 'modifica_password') {

        /* recupero i dati inseriti dall'utente nel form di modifica password */
        $passwordAttuale = $_POST['password_attuale'] ?? '';
        $nuovaPassword = $_POST['nuova_password'] ?? '';
        $confermaPassword = $_POST['conferma_password'] ?? '';

        /* se i dati inseriti risultano non validi viene restituito un messaggio di errore*/
        if (!is_string($passwordAttuale) ||!is_string($nuovaPassword) ||!is_string($confermaPassword)) {
            $risultatoPassword = [
                'esito' => 'errore',
                'messaggio' => 'Dati inseriti non validi.'
            ];
        } else {

            /* altrimenti viene chiamato il servizio di modifica credenziali e si prova ad aggiornare la password*/
            try {
                $service = new ModificaCredenzialiService();

                $risultatoPassword = $service->modificaPassword($idUtente, $passwordAttuale, $nuovaPassword, $confermaPassword);
            } catch (\Throwable $errore) {
                error_log((string) $errore);

                /* in caso di errore viene restituito un messaggio generico all'utente*/
                $risultatoPassword = ['esito' => 'errore','messaggio' => 'Impossibile aggiornare la password. Riprova.'];
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

    <title>Modifica credenziali - FitSlot</title>

    <link rel="stylesheet" href="css/portale.css">
</head>

<body>

    <header class="topbar">
        <a class="wordmark" href="index.php">
            FIT<span>SLOT</span>
        </a>

        <nav>
            <!-- pulsante per tornare alla pagina del profilo dell'utente prima o dopo aver modificato le credendiali -->
            <a href="profilo.php">Torna al profilo</a>
        </nav>
    </header>

    <main class="page-shell">

        <div class="page-heading">
            <span class="eyebrow">Sicurezza account</span>
            <h1>Modifica credenziali</h1>
            <p>
                Gestisci le tue credenziali di accesso in modo sicuro.
            </p>
        </div>

        
    <section class="panel">
        <h2>Modifica email</h2>

        <!-- se l'utente ha inviato il form di modifica email 
        viene mostrato un messaggio basato sull'esito dell'operazione-->
        <?php if ($risultatoEmail !== null): ?>
            <p role="status">
                <?= htmlspecialchars(
                    $risultatoEmail['messaggio'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        <?php endif; ?>

        <p>
            Aggiorna l'indirizzo email associato al tuo account.
        </p>

        <!-- modulo di modifica email -->
        <form
            class="registration-form"
            action="modifica-credenziali.php"
            method="POST"
        >

        <!-- riconosce l'azione di modifica email-->
        <input
            type="hidden"
            name="azione"
            value="modifica_email"
        >
        
        <!-- essocia un codice csrf di sicurezza all'operazione -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
        >

        <p>
            <label for="nuova_email">
                Nuova email
            </label>

            <!-- spazio di inserimento della nuova email, con i rispettivi limiti -->
            <input
                type="email"
                id="nuova_email"
                name="nuova_email"
                maxlength="255"
                placeholder="Inserisci la nuova email"
                autocomplete="email"
                required
            >
        </p>

        <p>
            <label for="password_email">
                Password attuale
            </label>

            <!-- spazio per l'inserimento della password attuale per assicurarsi che chi sta cambiando l'email sia l'utente giusto-->
            <input
                type="password"
                id="password_email"
                name="password_attuale"
                placeholder="Inserisci la password attuale"
                autocomplete="current-password"
                required
            >
        </p>

        <!-- tasto per inviare la richiesta di modifica email-->
        <button type="submit">
            Aggiorna email
        </button>

        </form>
    </section>


        
    <section class="panel panel-password">
        <h2>Modifica password</h2>

        <!-- se l'utente ha inviato il form di modifica password viene visualizzato un messaggio basato sull'esito dell'operazione-->
        <?php if ($risultatoPassword !== null): ?>
            <p role="status">
                <?= htmlspecialchars(
                    $risultatoPassword['messaggio'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>
        <?php endif; ?>

        <p>
            Scegli una nuova password per proteggere il tuo account.
        </p>

        <!-- modulo per la modifica della password-->
        <form
            class="registration-form"
            action="modifica-credenziali.php"
            method="POST"
        >

        <!-- identifico l'azione di modifica password-->
        <input
            type="hidden"
            name="azione"
            value="modifica_password"
        >

        <!-- associo all'azione un token csrf per garantire sicurezza durante l'operazione -->
        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>"
        >

        <p>
            <label for="password_attuale">
                Password attuale
            </label>

            <!-- spazio per inserire la vecchia password-->
            <input
                type="password"
                id="password_attuale"
                name="password_attuale"
                autocomplete="current-password"
                required
            >
        </p>

            <p>
                <label for="nuova_password">
                    Nuova password
                </label>

                <!-- spazio per inserire la nuova password-->
                <input
                    type="password"
                    id="nuova_password"
                    name="nuova_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </p>

            <p>
                <label for="conferma_password">
                    Conferma nuova password
                </label>

                <input
                    type="password"
                    id="conferma_password"
                    name="conferma_password"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >
            </p>

            <!-- tasto per inviare il form di modifica password -->
            <button type="submit">
                Aggiorna password
            </button>

        </form>
    </section>


    </main>

    <!-- Script per mostrare o nascondere le password tramite l'icona dell'occhio -->
    <script>

        /*individua tutte le sezioni di inserimento password nella pagina */
        document.querySelectorAll('input[type="password"]').forEach(input => {

            // Crea un contenitore per il campo password e l'icona
            const contenitore = document.createElement('span');
            contenitore.className = 'campo-password';
            input.parentNode.insertBefore(contenitore, input);
            contenitore.appendChild(input);

            /* crea il pulsante iconico dell'occhio per mostrare o nascondere la password */
            const occhio = document.createElement('button');
            occhio.type = 'button';
            occhio.className = 'mostra-password';
            occhio.innerHTML = `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            `;
            occhio.setAttribute('aria-label', 'Mostra password');

            contenitore.appendChild(occhio);

            /* a ogni clik alterna la visualizzazione della password o la nasconde*/
            occhio.addEventListener('click', () => {
                const visibile = input.type === 'text';

                input.type = visibile ? 'password' : 'text';

                occhio.setAttribute(
                    'aria-label',
                    visibile ? 'Mostra password' : 'Nascondi password'
                );
            });
        });
    </script>
    </body>
</html>

