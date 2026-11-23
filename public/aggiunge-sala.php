<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\AggiuntaSalaController;
use Ladythelemons\Fitslot\DataModel\Sala;
use Ladythelemons\Fitslot\Repository\SaleRepository;

require_once dirname(__DIR__) . '/bootstrap.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);

$controllore = new AggiuntaSalaController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['risultato_aggiunta_sala'] = $controllore->gestisciRichiesta('POST', $_POST);
    header('Location: aggiunge-sala.php', true, 303);
    exit;
}

$pagina = $controllore->gestisciRichiesta('GET', []);
if (isset($_SESSION['risultato_aggiunta_sala'])) {
    $pagina = $_SESSION['risultato_aggiunta_sala'];
    unset($_SESSION['risultato_aggiunta_sala']);
}

$sale = [];
$erroreSale = '';
try {
    $sale = (new SaleRepository())->trovaTutte();
} catch (Throwable $errore) {
    $erroreSale = 'Non è stato possibile caricare l’elenco delle sale. Riprova più tardi.';
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aggiungi sala - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="index.php">Portale</a>
        </nav>
    </header>
    <main class="page-shell">
        <div class="page-heading">
            <h1>Aggiungi una sala</h1>
            <p>Inserisci nome, capienza e orari di occupazione della sala.</p>
        </div>

        <section class="panel" aria-label="Modulo aggiunta sala">
            <?php if ($pagina['esito'] !== 'iniziale'): ?>
                <aside class="operation-summary" aria-live="polite" aria-label="Resoconto operazione">
                    <?php if ($pagina['esito'] === 'successo' && $pagina['codice_sala'] !== null): ?>
                        <h2 class="operation-title">Sala aggiunta con successo</h2>
                    <?php else: ?>
                        <h2 class="operation-title">Errore durante l’aggiunta della sala</h2>
                        <h2 class="operation-title"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <?php endif; ?>
                </aside>
            <?php endif; ?>

            <form class="registration-form" id="form-aggiunta-sala" method="post" action="aggiunge-sala.php">
                <p>I campi contrassegnati con <span class="obbligatorio">*</span> sono obbligatori.</p>

                <p>
                    <label for="nome">Nome <span class="obbligatorio">*</span></label><br>
                    <input
                        id="nome"
                        name="nome"
                        type="text"
                        maxlength="100"
                        placeholder="Es. Sala Prova"
                        value="<?= htmlspecialchars((string) $pagina['dati']['nome'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </p>

                <p>
                    <label for="capienza">Capienza <span class="obbligatorio">*</span></label><br>
                    <input
                        id="capienza"
                        name="capienza"
                        type="number"
                        min="1"
                        step="1"
                        placeholder="Es. 20"
                        value="<?= htmlspecialchars((string) $pagina['dati']['capienza'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </p>

                <p>
                    <label for="inizio_occupazione">Inizio occupazione <span class="obbligatorio">*</span></label><br>
                    <input
                        id="inizio_occupazione"
                        name="inizio_occupazione"
                        type="time"
                        step="60"
                        value="<?= htmlspecialchars((string) $pagina['dati']['inizio_occupazione'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </p>

                <p>
                    <label for="fine_occupazione">Fine occupazione <span class="obbligatorio">*</span></label><br>
                    <input
                        id="fine_occupazione"
                        name="fine_occupazione"
                        type="time"
                        step="60"
                        value="<?= htmlspecialchars((string) $pagina['dati']['fine_occupazione'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </p>

                <p>
                    <button type="submit">Aggiungi sala</button>
                </p>
            </form>
        </section>

        <section class="panel rooms-panel" aria-labelledby="elenco-sale">
            <div class="section-heading">
                <h2 id="elenco-sale">Tutte le sale</h2>
                <p>Elenco aggiornato delle sale disponibili nella struttura.</p>
            </div>

            <?php if ($erroreSale !== '') { ?>
                <p role="alert"><?= htmlspecialchars($erroreSale, ENT_QUOTES, 'UTF-8') ?></p>
            <?php } elseif ($sale === []) { ?>
                <p role="status">Non sono ancora presenti sale.</p>
            <?php } else { ?>
                <div class="rooms-table-wrapper">
                    <table class="rooms-table">
                        <caption class="visually-hidden">Elenco completo delle sale</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nome</th>
                                <th scope="col">Capienza</th>
                                <th scope="col">Orario di occupazione</th>
                                <th scope="col">Codice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sale as $sala) { ?>
                                <?php if (!$sala instanceof Sala) {
                                    continue;
                                } ?>
                                <tr>
                                    <th scope="row"><?= htmlspecialchars($sala->getNome(), ENT_QUOTES, 'UTF-8') ?></th>
                                    <td><?= (int) $sala->getCapienza() ?> persone</td>
                                    <td><?= htmlspecialchars(substr($sala->getInizioOccupazione(), 0, 5), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars(substr($sala->getFineOccupazione(), 0, 5), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) $sala->getId() ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>