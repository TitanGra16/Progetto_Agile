<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use DateTimeImmutable;
use InvalidArgumentException;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CertificatoMedicoServiceTest extends TestCase
{
    public function testScadenzaFuturaEDataOdiernaAccettate(): void
    {
        CertificatoMedicoService::validaScadenza('2026-11-16', new DateTimeImmutable('2026-11-16'));
        CertificatoMedicoService::validaScadenza('2026-12-01', new DateTimeImmutable('2026-11-16'));
        $this->addToAssertionCount(2);
    }

    public function testScadenzaPassataRifiutata(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CertificatoMedicoService::validaScadenza('2026-11-15', new DateTimeImmutable('2026-11-16'));
    }

    public function testDataImpossibileRifiutata(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CertificatoMedicoService::validaScadenza('2026-02-30', new DateTimeImmutable('2026-01-01'));
    }

    public function testRegistrazioneCaricaPdfEArchiviaScadenza(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'certificato.pdf'];
        $scadenza = (new DateTimeImmutable('+2 days'))->format('Y-m-d');

        $repo->method('esisteUtente')->with(7)->willReturn(true);
        $archivio->expects(self::once())->method('salva')->with($pdf)->willReturn('00000000000000000000000000000000.pdf');
        $repo->expects(self::once())->method('salvaOaggiorna')->with(7, $scadenza, '00000000000000000000000000000000.pdf');

        (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf);
    }

    public function testNonRegistraCertificatoSeLIscrittoNonEsiste(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $repo->method('esisteUtente')->willReturn(false);
        $archivio->expects(self::never())->method('salva');

        $this->expectException(InvalidArgumentException::class);
        (new CertificatoMedicoService($repo, $archivio))->registra(
            7,
            (new DateTimeImmutable('+1 day'))->format('Y-m-d'),
            []
        );
    }

    public function testPropagaErroreSeIlSalvataggioDatabaseFallisce(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'nuovo.pdf'];
        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $repo->method('esisteUtente')->willReturn(true);
        $archivio->method('salva')->willReturn('22222222222222222222222222222222.pdf');
        $repo->method('salvaOaggiorna')->willThrowException(new RuntimeException('Database offline'));

        $this->expectException(RuntimeException::class);
        (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf);
    }
}
