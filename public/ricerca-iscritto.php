<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RicercaIscrittoController;
use Ladythelemons\Fitslot\DataModel\Utente;

require_once dirname(__DIR__) . '/bootstrap.php';

$controllore = new RicercaIscrittoController();
$pagina = $controllore->gestisciRichiesta($_SERVER['REQUEST_METHOD'], $_POST);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cerca iscritto - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
</head>
<body>
    <header class="topbar">
        <a class="wordmark" href="index.php">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="index.php">Portale</a>
            <a href="registrazione-iscritto.php">Registra iscritto</a>
        </nav>
    </header>

    <main class="page-shell">
        <div class="page-heading">
            <span class="eyebrow">UE-02 · Reception</span>
            <h1>Cerca un iscritto</h1>
            <p>Cerca per nome, cognome o codice dell'iscritto.</p>
        </div>

        <section class="panel search-panel" aria-label="Ricerca iscritto">
            <?php if ($pagina['messaggio'] !== ''): ?>
                <p role="status"><?= htmlspecialchars($pagina['messaggio'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form class="registration-form search-form" method="post" action="ricerca-iscritto.php">
                <p>
                    <label for="termine">Termine di ricerca</label><br>
                    <input
                        id="termine"
                        name="termine"
                        type="search"
                        maxlength="100"
                        placeholder="Es. Rossi o 42"
                        value="<?= htmlspecialchars($pagina['termine'], ENT_QUOTES, 'UTF-8') ?>"
                        autofocus
                        required
                    >
                </p>
                <p><button type="submit">Cerca iscritto</button></p>
            </form>

            <?php if ($pagina['risultati'] !== []): ?>
                <div class="search-results" aria-live="polite">
                    <h2>Risultati della ricerca: <?= count($pagina['risultati']) ?></h2>
                
                    <div class="result-list">
                        <?php foreach ($pagina['risultati'] as $utente): ?>
                            <?php if (!$utente instanceof Utente) {
                                continue;
                            } ?>
                            <article class="result-card">
                                <div>
                                    <h3><?= htmlspecialchars($utente->getNome(), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($utente->getCognome(), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <p>Codice iscritto: <strong><?= (int) $utente->getId() ?></strong></p>
                                </div>
                                <dl>
                                    <div>
                                        <dt>Data di nascita:<?= htmlspecialchars($utente->getDataNascita(), ENT_QUOTES, 'UTF-8') ?></dt>
                                    </div>
                                    <div>
                                        <dt>Recapito: <?= htmlspecialchars($utente->getRecapito(), ENT_QUOTES, 'UTF-8') ?></dt>                                        
                                    </div>
                                    <?php if ($utente->getEmail() !== null): ?>
                                        <div>
                                            <dt>Email: <?= htmlspecialchars($utente->getEmail(), ENT_QUOTES, 'UTF-8') ?></dt>                                            
                                        </div>
                                    <?php endif; ?>
                                </dl>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <footer class="site-footer">FitSlot · Progetto universitario Agile Lady!TheLemons!</footer>
</body>
</html>
