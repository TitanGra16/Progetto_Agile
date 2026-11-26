<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\TipoAbbonamentoService;

require_once dirname(__DIR__) . '/bootstrap.php';

header('Cache-Control: no-store');

if (!(new AutorizzazioneService())->haRuolo('GESTORE')) {
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
$messaggioSuccesso = $_SESSION['ue08_flash'] ?? '';
unset($_SESSION['ue08_flash']);
$messaggioErrore = '';
$errori = [];

try {
    $servizio = new TipoAbbonamentoService(new TipoAbbonamentoRepository());

    if ($inviato) {
        $token = $_POST['ue08_token'] ?? null;
        if (!is_string($token) || !hash_equals($_SESSION['ue08_token'], $token)) {
            http_response_code(400);
            $messaggioErrore = 'Richiesta non valida. Ricarica la pagina e riprova.';
        } else {
            $risultato = $modificaId === null
                ? $servizio->crea($modulo)
                : $servizio->aggiorna($modificaId, $modulo);
            if ($risultato['esito'] === 'ok') {
                $_SESSION['ue08_flash'] = $risultato['messaggio'];
                header('Location: tipi-abbonamento.php#listino', true, 303);
                exit;
            }
            $messaggioErrore = $risultato['messaggio'];
            $errori = $risultato['errori'];
        }
    } elseif ($modificaId !== null) {
        $piano = $servizio->trovaPerId($modificaId);
        if ($piano === null) {
            http_response_code(404);
            $modificaId = null;
            $messaggioErrore = 'Tipo di abbonamento non trovato.';
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
    <link rel="stylesheet" href="css/area-iscritto.css">
    <link rel="stylesheet" href="css/accesso.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="vista-gestore.php">Area gestore</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="dashboard-heading">
            <h1>Tipi di <span>abbonamento</span></h1>
            <a class="button primary member-action" href="tipi-abbonamento.php#piano">Nuovo piano</a>
        </header>

        <div class="panel-stack">
            <section class="panel wide-panel content-panel" id="listino" aria-labelledby="titolo-listino">
                <h2 id="titolo-listino">Listino attuale</h2>

                <?php if ($messaggioSuccesso !== ''): ?>
                    <p class="access-message form-success" role="status"><?= escapeUe08($messaggioSuccesso) ?></p>
                <?php endif; ?>

                <?php if ($catalogo === []): ?>
                    <p role="status">Il listino è vuoto.</p>
                <?php else: ?>
                    <div class="table-scroll" role="region" aria-labelledby="titolo-listino" tabindex="0">
                        <table class="data-table pricing-table">
                            <caption class="visually-hidden">Piani, prezzi, servizi inclusi e disponibilità</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Piano</th>
                                    <th scope="col">Prezzo</th>
                                    <th scope="col">Ingressi o durata</th>
                                    <th scope="col">Servizi</th>
                                    <th scope="col">Prezzo studenti</th>
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
                                        <th scope="row"><?= escapeUe08($piano['nome']) ?></th>
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
                                                <?= escapeUe08($piano['prezzo_studenti']) ?> €
                                                <small>Avvio attività <?= escapeUe08($piano['studenti_dalle']) ?>–<?= escapeUe08($piano['studenti_alle']) ?></small>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="member-status<?= $piano['attivo'] ? ' valido' : '' ?>">
                                                <?= $piano['attivo'] ? 'Disponibile' : 'Disattivato' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a class="table-action" href="?modifica=<?= (int) $piano['id'] ?>#piano"
                                               aria-label="Modifica <?= escapeUe08($piano['nome']) ?>">Modifica</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel content-panel" id="piano" aria-labelledby="titolo-piano">
                <h2 id="titolo-piano"><?= $modificaId === null ? 'Crea nuovo piano' : 'Modifica piano' ?></h2>

                <?php if ($messaggioErrore !== '' || $errori !== []): ?>
                    <div class="access-message access-error form-errors" role="alert">
                        <p><?= escapeUe08($messaggioErrore) ?></p>
                        <?php if ($errori !== []): ?>
                            <ul>
                                <?php foreach ($errori as $errore): ?>
                                    <li><?= escapeUe08($errore) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <form class="access-form two-column-form" method="post" action="tipi-abbonamento.php#piano">
                    <input type="hidden" name="ue08_token" value="<?= escapeUe08($_SESSION['ue08_token']) ?>">
                    <?php if ($modificaId !== null): ?>
                        <input type="hidden" name="id" value="<?= $modificaId ?>">
                    <?php endif; ?>

                    <div class="access-field">
                        <label for="nome">Nome</label>
                        <input id="nome" name="nome" maxlength="100" value="<?= escapeUe08($modulo['nome'] ?? '') ?>" required>
                    </div>

                    <div class="access-field">
                        <label for="categoria">Categoria</label>
                        <select id="categoria" name="categoria">
                            <?php foreach ($categorie as $codice => $nome): ?>
                                <option value="<?= escapeUe08($codice) ?>" <?= ($modulo['categoria'] ?? '') === $codice ? 'selected' : '' ?>>
                                    <?= escapeUe08($nome) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="access-field">
                        <label for="prezzo">Prezzo (€)</label>
                        <input id="prezzo" name="prezzo" inputmode="decimal" value="<?= escapeUe08($modulo['prezzo'] ?? '') ?>" required>
                    </div>

                    <div class="access-field" id="campo-ingressi">
                        <label for="ingressi">Numero di ingressi</label>
                        <input id="ingressi" name="ingressi_totali" type="number" min="1" max="1000" aria-describedby="aiuto-validita"
                               value="<?= escapeUe08($modulo['ingressi_totali'] ?? '') ?>">
                    </div>

                    <div class="access-field" id="campo-durata">
                        <label for="durata">Durata (mesi)</label>
                        <input id="durata" name="durata_mesi" type="number" min="1" max="120" aria-describedby="aiuto-validita"
                               value="<?= escapeUe08($modulo['durata_mesi'] ?? '') ?>">
                    </div>

                    <p class="access-hint form-wide" id="aiuto-validita">Gli ingressi sono validi senza scadenza; la durata indica un abbonamento illimitato a tempo.</p>

                    <fieldset class="form-wide form-options">
                        <legend>Servizi inclusi</legend>
                        <div class="form-actions">
                            <?php foreach ($servizi as $codice => $etichetta): ?>
                                <label class="form-check">
                                    <input type="checkbox" name="servizi_inclusi[]" value="<?= escapeUe08($codice) ?>"
                                           <?= in_array($codice, $selezionati, true) ? 'checked' : '' ?>>
                                    <?= escapeUe08($etichetta) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>

                    <fieldset class="form-wide form-options">
                        <legend>Opzioni</legend>
                        <label class="form-check">
                            <input type="checkbox" name="agevolazione_studenti" value="1" aria-describedby="aiuto-studenti"
                                   <?= !empty($modulo['agevolazione_studenti']) ? 'checked' : '' ?>>
                            Sconto studenti del 20%
                        </label>
                        <p class="access-hint" id="aiuto-studenti">Per i pacchetti, su attività con inizio tra le 08:00 e le 13:30.</p>
                        <input type="hidden" name="attivo" value="<?= !empty($modulo['attivo']) ? '1' : '0' ?>">
                    </fieldset>

                    <div class="form-actions form-wide">
                        <button class="button primary access-action" type="submit"><?= $modificaId === null ? 'Crea piano' : 'Salva modifiche' ?></button>
                        <?php if ($modificaId !== null): ?>
                            <a class="button secondary access-action" href="tipi-abbonamento.php#piano">Annulla modifica</a>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>
    </main>
<script>
    const categoria = document.getElementById('categoria');
    const campoIngressi = document.getElementById('campo-ingressi');
    const campoDurata = document.getElementById('campo-durata');
    const ingressi = document.getElementById('ingressi');
    const durata = document.getElementById('durata');
    const etichettaIngressi = campoIngressi.querySelector('label');

    function aggiornaTipoAbbonamento() {
        const tipo = categoria.value;
        const pianoIngresso = tipo === 'SINGOLO' || tipo === 'PACCHETTO';

        campoIngressi.hidden = !pianoIngresso;
        campoDurata.hidden = pianoIngresso;
        ingressi.disabled = !pianoIngresso;
        durata.disabled = pianoIngresso;
        ingressi.required = pianoIngresso;
        durata.required = !pianoIngresso;

        if (tipo === 'SINGOLO') {
            ingressi.value = '1';
            ingressi.readOnly = true;
            etichettaIngressi.textContent = 'Numero di ingressi';
        } else if (tipo === 'PACCHETTO') {
            ingressi.readOnly = false;
            if (ingressi.value === '1') {
                ingressi.value = '';
            }
            etichettaIngressi.textContent = 'Numero di ingressi';
        } else {
            ingressi.readOnly = false;
            durata.value = durata.value || '';
        }

        document.getElementById('aiuto-validita').textContent = pianoIngresso
            ? 'I piani a ingressi sono validi senza scadenza.'
            : 'Inserisci la durata in mesi; l’abbonamento sarà illimitato nel periodo indicato.';
    }

    categoria.addEventListener('change', aggiornaTipoAbbonamento);
    aggiornaTipoAbbonamento();
</script>
</body>
</html>
