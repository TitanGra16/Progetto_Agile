<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class ArchivioPdfCertificati
{
    public const DIMENSIONE_MASSIMA = 3 * 1024 * 1024; // 3 MiB
    private const CARTELLA = __DIR__ . '/../../storage/certificati-medici';

    /** @param array<string, mixed> $file Da $_FILES['certificato_pdf']. */
    public function salva(array $file): ?string
    {
        $temporaneo = $file['tmp_name'] ?? '';
        $dimensione = $file['size'] ?? 0;

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_int($dimensione)
            || $dimensione < 1
            || $dimensione > self::DIMENSIONE_MASSIMA
            || !is_string($temporaneo)
            || !is_uploaded_file($temporaneo)) {
            return null;
        }

        if (file_get_contents($temporaneo, false, null, 0, 5) !== '%PDF-') {
            return null;
        }

        if (!is_dir(self::CARTELLA)) {
            @mkdir(self::CARTELLA, 0700, true);
        }

        $nomeSalvato = bin2hex(random_bytes(16)) . '.pdf';
        $destinazione = self::CARTELLA . '/' . $nomeSalvato;
        if (!@move_uploaded_file($temporaneo, $destinazione)) {
            return null;
        }
        @chmod($destinazione, 0600);

        return $nomeSalvato;
    }
}
