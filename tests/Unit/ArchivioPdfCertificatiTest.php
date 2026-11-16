<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ArchivioPdfCertificatiTest extends TestCase
{
    public function testRifiutaUnCaricamentoFallito(): void
    {
        $this->expectException(RuntimeException::class);

        (new ArchivioPdfCertificati())->salva([
            'error' => UPLOAD_ERR_NO_FILE,
        ]);
    }

    public function testRifiutaDimensioneEccessiva(): void
    {
        $percorso = tempnam(sys_get_temp_dir(), 'ue05_');
        self::assertIsString($percorso);
        file_put_contents($percorso, '%PDF-1.7');
        try {
            $this->expectException(RuntimeException::class);
            (new ArchivioPdfCertificati())->salva([
                'error' => UPLOAD_ERR_OK,
                'name' => 'documento.pdf',
                'tmp_name' => $percorso,
                'size' => ArchivioPdfCertificati::DIMENSIONE_MASSIMA + 1,
            ]);
        } finally {
            unlink($percorso);
        }
    }
}
