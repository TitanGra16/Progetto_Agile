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

    public function testRifiutaUnFileLocale(): void
    {
        $nomeFile = (new ArchivioPdfCertificati())->salva([
            'error' => UPLOAD_ERR_OK,
            'tmp_name' => __FILE__,
        ]);

        self::assertNull($nomeFile);
    }

    public function testRifiutaPercorsiFuoriDallArchivio(): void
    {
        $archivio = new ArchivioPdfCertificati();

        self::assertNull($archivio->percorso('../certificato.pdf'));
    }
}
