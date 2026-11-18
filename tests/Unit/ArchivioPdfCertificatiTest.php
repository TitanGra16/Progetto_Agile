<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use PHPUnit\Framework\TestCase;

class ArchivioPdfCertificatiTest extends TestCase
{
    public function testRifiutaUnCaricamentoFallito(): void
    {
        $nomeFile = (new ArchivioPdfCertificati())->salva([
            'error' => UPLOAD_ERR_NO_FILE,
        ]);

        self::assertNull($nomeFile);
    }

    public function testRifiutaDimensioneEccessiva(): void
    {
        $nomeFile = (new ArchivioPdfCertificati())->salva([
            'error' => UPLOAD_ERR_OK,
            'size' => ArchivioPdfCertificati::DIMENSIONE_MASSIMA + 1,
        ]);

        self::assertNull($nomeFile);
    }
}
