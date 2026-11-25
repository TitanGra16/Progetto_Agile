<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();

if ($sessione->getIdUtente() === null) {
    header('Location: login.php');
    exit;
}

$ruoli = (new AutorizzazioneService())->getRuoliUtente();
$isGestore = in_array('GESTORE', $ruoli, true);
$aree = [
    'GESTORE' => [
        'percorso' => 'vista-gestore.php',
        'titolo' => 'Area gestore',
        'descrizione' => 'Gestisci le sale e il listino del centro sportivo.',
    ],
    'RECEPTION' => [
        'percorso' => 'vista-reception.php',
        'titolo' => 'Area reception',
        'descrizione' => 'Cerca e registra gli iscritti, gestisci i certificati medici.',
    ],
    'ISCRITTO' => [
        'percorso' => 'vista-iscritto.php',
        'titolo' => 'Area iscritto',
        'descrizione' => 'Consulta il tuo profilo e lo stato del certificato medico.',
    ],
    'ISTRUTTORE' => [
        'percorso' => 'vista-istruttore.php',
        'titolo' => 'Area istruttore',
        'descrizione' => 'Spazio dedicato alle lezioni e alle disponibilità degli istruttori.',
    ],
];
$areeVisibili = array_filter(
    $aree,
    static fn (string $ruolo): bool => $isGestore || in_array($ruolo, $ruoli, true),
    ARRAY_FILTER_USE_KEY
);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Le mie aree - FitSlot</title>
    <link rel="stylesheet" href="css/portale.css">
    <link rel="stylesheet" href="css/area-iscritto.css">
</head>
<body class="member-page">
    <header class="topbar">
        <a class="wordmark" href="index.php" aria-label="FitSlot, pagina iniziale">FIT<span>SLOT</span></a>
        <nav aria-label="Navigazione principale">
            <a href="profilo.php">Il mio profilo</a>
            <form class="logout-form" action="logout.php" method="post">
                <button type="submit">Esci</button>
            </form>
        </nav>
    </header>

    <main class="page-shell">
        <header class="dashboard-heading">
            <h1>Le mie <span>aree</span></h1>
            <?php if ($isGestore): ?>
                <a class="button secondary member-action" href="tipi-abbonamento.php">
                    Listino abbonamenti <span aria-hidden="true">&rarr;</span>
                </a>
            <?php endif; ?>
        </header>

        <?php if ($areeVisibili === []): ?>
            <p class="panel">Nessuna area disponibile per questo account.</p>
        <?php else: ?>
            <nav class="card-grid" aria-label="Aree disponibili">
                <?php foreach ($areeVisibili as $area): ?>
                    <a class="feature-card dashboard-card" href="<?= $area['percorso'] ?>">
                        <h2><?= $area['titolo'] ?></h2>
                        <p><?= $area['descrizione'] ?></p>
                        <span class="dashboard-card-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </main>
</body>
</html>
