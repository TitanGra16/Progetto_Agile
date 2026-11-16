<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use RuntimeException;

/** Conserva i PDF fuori dalla cartella public, con nomi non prevedibili. */
class ArchivioPdfCertificati
{
    public const DIMENSIONE_MASSIMA = 3 * 1024 * 1024; // 3 MiB
    private string $cartella = __DIR__ . '/../../storage/certificati-medici';

    /** @param array<string, mixed> $file Da $_FILES['certificato_pdf']. */
    public function salva(array $file): string
    {
        $temporaneo = $file['tmp_name'] ?? null;
        $dimensione = $file['size'] ?? null;
        $fileTemporaneoValido = is_string($temporaneo) && is_file($temporaneo);

        if (($file['error'] ?? null) !== UPLOAD_ERR_OK
            || !is_string($temporaneo)
            || !$fileTemporaneoValido
            || !is_int($dimensione)
            || $dimensione < 1
            || $dimensione > self::DIMENSIONE_MASSIMA
            || filesize($temporaneo) !== $dimensione) {
            throw new RuntimeException('Il caricamento del certificato non è valido.');
        }

        $nomeSalvato = bin2hex(random_bytes(16)) . '.pdf';
        $destinazione = $this->cartella . DIRECTORY_SEPARATOR . $nomeSalvato;
        if (!move_uploaded_file($file['tmp_name'], $destinazione)) {
            throw new RuntimeException('Non è stato possibile conservare il PDF.');
        }
        @chmod($destinazione, 0600);

        return $nomeSalvato;
    }
}
