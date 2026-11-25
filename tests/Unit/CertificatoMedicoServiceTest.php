<?php
declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use PHPUnit\Framework\TestCase;

final class CertificatoMedicoServiceTest extends TestCase
{
    public function testRifiutaScadenzaPassata(): void
    {
        $errore = CertificatoMedicoService::validaScadenza(
            '2026-11-15',
            new DateTimeImmutable('2026-11-16')
        );
        self::assertSame('Il certificato è già scaduto.', $errore);
    }

    public function testAccettaScadenzaOdierna(): void
    {
        self::assertNull(CertificatoMedicoService::validaScadenza(
            '2026-11-16',
            new DateTimeImmutable('2026-11-16')
        ));
    }

    public function testRichiedeConfermaPrimaDiRegistrare(): void
    {
        $repository = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $archivio->expects(self::never())->method('salva');

        $errore = (new CertificatoMedicoService($repository, $archivio))
            ->registra(7, date('Y-m-d'), [], false);

        self::assertSame('Conferma di aver verificato il certificato cartaceo.', $errore);
    }

    public function testClassificaCertificatoAssente(): void
    {
        self::assertSame('ASSENTE', CertificatoMedicoService::stato(null, new DateTimeImmutable('2026-11-16')));
    }

    public function testRifiutaDataDiScadenzaImpossibile(): void
    {
        self::assertSame(
            'Inserisci una data di scadenza valida.',
            CertificatoMedicoService::validaScadenza('2026-02-30', new DateTimeImmutable('2026-01-01'))
        );
    }
}
