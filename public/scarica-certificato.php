<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\CertificatoMedicoController;

header('Cache-Control: no-store');

// Sostituire questo return con il controllo dei permessi di UE-04.
return;

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}

try {
    $controller = new CertificatoMedicoController();
    $percorso = $controller->percorsoDownload($_GET['id'] ?? null);
} catch (mysqli_sql_exception) {
    http_response_code(503);
    exit('Il servizio dei certificati medici non è disponibile.');
}

$file = $percorso === null ? false : @fopen($percorso, 'rb');
if ($file === false) {
    http_response_code(404);
    exit('PDF del certificato non disponibile.');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="certificato-medico.pdf"');
header('X-Content-Type-Options: nosniff');
fpassthru($file);
fclose($file);
