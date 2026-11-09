<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\SessioneService;

// richiesta di logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

require_once dirname(__DIR__) . '/bootstrap.php';

$sessione = new SessioneService();
$sessione->termina();

// Torniamo alla pagina di login
header('Location: login.php', true, 303);
exit;
