<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use finfo;

class ArchivioPdfCertificati
{
    public const DIMENSIONE_MASSIMA = 5_000_000;
    private const CARTELLA = __DIR__ . '/../../storage/certificati-medici';

    /** @param array<string, mixed> $file Da $_FILES['certificato_pdf']. */
    public function salva(array $file): ?string
    {
        $temporaneo = $file['tmp_name'] ?? null;
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_string($temporaneo)
            || !is_uploaded_file($temporaneo)) {
            return null;
        }

        $dimensione = filesize($temporaneo);
        if ($dimensione === false || $dimensione > self::DIMENSIONE_MASSIMA) {
            return null;
        }

        if (!class_exists(finfo::class) || (new finfo(FILEINFO_MIME_TYPE))->file($temporaneo) !== 'application/pdf') {
            return null;
        }

        // Il marcatore finale controlla il formato senza interpretare il documento.
        $fineFile = file_get_contents($temporaneo, false, null, max(0, $dimensione - 1024), 1024);
        if ($fineFile === false || !str_ends_with(rtrim($fineFile), '%%EOF')) {
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

    public function percorso(?string $nomeFile): ?string
    {
        if ($nomeFile === null || !preg_match('/\A[a-f0-9]{32}\.pdf\z/', $nomeFile)) {
            return null;
        }

        $percorso = self::CARTELLA . '/' . $nomeFile;

        return is_file($percorso) && !is_link($percorso) ? $percorso : null;
    }

    public function elimina(string $nomeFile): void
    {
        $percorso = $this->percorso($nomeFile);
        if ($percorso !== null && !@unlink($percorso)) {
            error_log('UE-05: impossibile rimuovere un PDF dall’archivio.');
        }
    }
}
