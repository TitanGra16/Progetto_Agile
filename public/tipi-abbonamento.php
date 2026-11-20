<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\TipoAbbonamentoService;

require_once dirname(__DIR__) . '/bootstrap.php';

header('Cache-Control: no-store');

// UE-04 verifica il ruolo del gestore.
if (!class_exists(AutorizzazioneService::class)) {
    http_response_code(403);
    exit('Gestione listino in attesa di UE-04.');
}

try {
    $autorizzato = (new AutorizzazioneService())->haRuolo('GESTORE');
} catch (Throwable $errore) {
    error_log('UE-08 autorizzazione: ' . $errore->getMessage());
    http_response_code(503);
    exit('Impossibile verificare i permessi.');
}

if (!$autorizzato) {
    http_response_code(403);
    exit('Accesso riservato al gestore.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
}
$_SESSION['ue08_token'] ??= bin2hex(random_bytes(32));

function escapeUe08(mixed $valore): string
{
    return htmlspecialchars(is_scalar($valore) ? (string) $valore : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$inviato = $_SERVER['REQUEST_METHOD'] === 'POST';
$modificaId = filter_input(
    $inviato ? INPUT_POST : INPUT_GET,
    $inviato ? 'id' : 'modifica',
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
) ?: null;

$servizi = TipoAbbonamentoService::SERVIZI;
$modulo = $inviato ? $_POST : [
    'categoria' => 'PACCHETTO',
    'servizi_inclusi' => array_keys($servizi),
    'attivo' => true,
];
$messaggio = $_SESSION['ue08_flash'] ?? '';
unset($_SESSION['ue08_flash']);
$errori = [];

try {
    $servizio = new TipoAbbonamentoService(new TipoAbbonamentoRepository());

    if ($inviato) {
        $token = $_POST['ue08_token'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['ue08_token'], $token)) {
            http_response_code(400);
            $messaggio = 'Richiesta non valida. Ricarica la pagina e riprova.';
        } else {
            $risultato = $modificaId === null
                ? $servizio->crea($modulo)
                : $servizio->aggiorna($modificaId, $modulo);
            $messaggio = $risultato['messaggio'];
            if ($risultato['esito'] === 'ok') {
                $_SESSION['ue08_flash'] = $messaggio;
                header('Location: tipi-abbonamento.php', true, 303);
                exit;
            }
            $errori = $risultato['errori'];
        }
    } elseif ($modificaId !== null) {
        $piano = $servizio->trovaPerId($modificaId);
        if ($piano === null) {
            http_response_code(404);
            $modificaId = null;
            $messaggio = 'Tipo di abbonamento non trovato.';
        } else {
            $modulo = $piano;
            $modulo['agevolazione_studenti'] = $piano['sconto_studenti_percentuale'] === 20;
        }
    }

    $catalogo = $servizio->elenca();
} catch (Throwable $errore) {
    error_log('UE-08 listino: ' . $errore->getMessage());
    http_response_code(503);
    exit('Il listino non è disponibile.');
}

$selezionati = $modulo['servizi_inclusi'] ?? [];
if (!is_array($selezionati)) {
    $selezionati = [];
}
$categorie = [
    'SINGOLO' => 'Ingresso singolo',
    'PACCHETTO' => 'Pacchetto ingressi',
    'TEMPO' => 'Illimitato a tempo',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tipi di abbonamento - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <style>
        .listino .panel + .panel { margin-top: 20px; }
        .listino .tabella-listino { overflow-x: auto; }
        .listino table { width: 100%; border-collapse: collapse; text-align: left; }
        .listino th, .listino td { padding: 10px; border-bottom: 1px solid var(--border); }
        .listino .registration-form input[type="checkbox"] { width: auto; margin: 0; }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
        <nav><a href="homepage.php">Area riservata</a></nav>
    </header>
    <main class="page-shell listino">
        <h1>Tipi di abbonamento</h1>
        <?php if ($messaggio !== ''): ?>
            <p role="status"><?= escapeUe08($messaggio) ?></p>
        <?php endif; ?>

        <section class="panel" aria-labelledby="listino">
            <h2 id="listino">Listino attuale</h2>
            <?php if ($catalogo === []): ?>
                <p>Il listino è vuoto. Aggiungi un piano dal modulo.</p>
            <?php else: ?>
                <div class="tabella-listino">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Piano</th>
                                <th scope="col">Prezzo</th>
                                <th scope="col">Ingressi o durata</th>
                                <th scope="col">Servizi</th>
                                <th scope="col">Studenti</th>
                                <th scope="col">Stato</th>
                                <th scope="col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catalogo as $piano): ?>
                                <?php
                                $etichette = [];
                                foreach ($piano['servizi_inclusi'] as $codice) {
                                    $etichette[] = $servizi[$codice] ?? $codice;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= escapeUe08($piano['nome']) ?></strong><br>
                                        <small><?= escapeUe08($piano['codice']) ?></small>
                                    </td>
                                    <td><?= escapeUe08($piano['prezzo']) ?> €</td>
                                    <td>
                                        <?php if ($piano['durata_mesi'] === null): ?>
                                            <?= (int) $piano['ingressi_totali'] ?> ingressi, senza scadenza
                                        <?php else: ?>
                                            <?= (int) $piano['durata_mesi'] ?> mesi, illimitato
                                        <?php endif; ?>
                                    </td>
                                    <td><?= escapeUe08(implode(', ', $etichette)) ?></td>
                                    <td>
                                        <?php if ($piano['prezzo_studenti'] !== null): ?>
                                            <?= escapeUe08($piano['prezzo_studenti']) ?> €<br>
                                            <small>Avvio attività <?= escapeUe08($piano['studenti_dalle']) ?>–<?= escapeUe08($piano['studenti_alle']) ?></small>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $piano['attivo'] ? 'Disponibile' : 'Disattivato' ?></td>
                                    <td><a href="?modifica=<?= (int) $piano['id'] ?>#piano">Modifica</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel" aria-labelledby="piano">
            <h2 id="piano"><?= $modificaId === null ? 'Crea nuovo piano' : 'Modifica piano' ?></h2>
            <?php if ($errori !== []): ?>
                <ul role="alert">
                    <?php foreach ($errori as $errore): ?>
                        <li><?= escapeUe08($errore) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form class="registration-form" method="post" action="tipi-abbonamento.php">
                <input type="hidden" name="ue08_token" value="<?= escapeUe08($_SESSION['ue08_token']) ?>">
                <?php if ($modificaId !== null): ?>
                    <input type="hidden" name="id" value="<?= $modificaId ?>">
                <?php endif; ?>

                <p>
                    <label for="codice">Codice del piano</label>
                    <input id="codice" name="codice" maxlength="40" required
                           value="<?= escapeUe08($modulo['codice'] ?? '') ?>" <?= $modificaId !== null ? 'readonly' : '' ?>>
                </p>
                <p>
                    <label for="nome">Nome</label>
                    <input id="nome" name="nome" maxlength="100" required value="<?= escapeUe08($modulo['nome'] ?? '') ?>">
                </p>
                <p>
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria">
                        <?php foreach ($categorie as $codice => $nome): ?>
                            <option value="<?= escapeUe08($codice) ?>" <?= ($modulo['categoria'] ?? '') === $codice ? 'selected' : '' ?>>
                                <?= escapeUe08($nome) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <p>
                    <label for="prezzo">Prezzo (€)</label>
                    <input id="prezzo" name="prezzo" inputmode="decimal" required value="<?= escapeUe08($modulo['prezzo'] ?? '') ?>">
                </p>
                <p>Compila gli ingressi o la durata in base alla categoria.</p>
                <p>
                    <label for="ingressi">Numero di ingressi</label>
                    <input id="ingressi" name="ingressi_totali" type="number" min="1" max="1000"
                           value="<?= escapeUe08($modulo['ingressi_totali'] ?? '') ?>">
                </p>
                <p>
                    <label for="durata">Durata (mesi)</label>
                    <input id="durata" name="durata_mesi" type="number" min="1" max="120"
                           value="<?= escapeUe08($modulo['durata_mesi'] ?? '') ?>">
                </p>
                <fieldset>
                    <legend>Servizi inclusi</legend>
                    <?php foreach ($servizi as $codice => $etichetta): ?>
                        <label>
                            <input type="checkbox" name="servizi_inclusi[]" value="<?= escapeUe08($codice) ?>"
                                   <?= in_array($codice, $selezionati, true) ? 'checked' : '' ?>>
                            <?= escapeUe08($etichetta) ?>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <p>
                    <label>
                        <input type="checkbox" name="agevolazione_studenti" value="1"
                               <?= !empty($modulo['agevolazione_studenti']) ? 'checked' : '' ?>>
                        Sconto studenti del 20% sui pacchetti, per attività con inizio tra le 08:00 e le 13:30
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="attivo" value="1" <?= !empty($modulo['attivo']) ? 'checked' : '' ?>>
                        Piano disponibile nel listino
                    </label>
                </p>
                <button type="submit"><?= $modificaId === null ? 'Crea piano' : 'Salva modifiche' ?></button>
                <?php if ($modificaId !== null): ?>
                    <a href="tipi-abbonamento.php#piano">Annulla modifica</a>
                <?php endif; ?>
            </form>
        </section>
    </main>
</body>
</html>
