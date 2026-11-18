<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CertificatoMedicoServiceTest extends TestCase
{
    public function testScadenzaFuturaEDataOdiernaAccettate(): void
    {
        $oggi = new DateTimeImmutable('2026-11-16');

        self::assertNull(CertificatoMedicoService::validaScadenza('2026-11-16', $oggi));
        self::assertNull(CertificatoMedicoService::validaScadenza('2026-12-01', $oggi));
    }

    public function testScadenzaPassataRifiutata(): void
    {
        $errore = CertificatoMedicoService::validaScadenza('2026-11-15', new DateTimeImmutable('2026-11-16'));

        self::assertSame('Il certificato è già scaduto.', $errore);
    }

    public function testDataImpossibileRifiutata(): void
    {
        $errore = CertificatoMedicoService::validaScadenza('2026-02-30', new DateTimeImmutable('2026-01-01'));

        self::assertSame('Inserisci una data di scadenza valida.', $errore);
    }

    public function testRegistrazioneCaricaPdfEArchiviaScadenza(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'certificato.pdf'];
        $scadenza = (new DateTimeImmutable('+2 days'))->format('Y-m-d');

        $repo->expects(self::once())->method('esisteUtente')->with(7)->willReturn(true);
        $archivio->expects(self::once())->method('salva')->with($pdf)->willReturn('archiviato.pdf');
        $repo->expects(self::once())->method('salvaOaggiorna')->with(7, $scadenza, 'archiviato.pdf');

        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf);

        self::assertNull($errore);
    }

    public function testNonRegistraCertificatoSeLIscrittoNonEsiste(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $repo->method('esisteUtente')->willReturn(false);
        $archivio->expects(self::never())->method('salva');

        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, []);

        self::assertSame('Iscritto non trovato.', $errore);
    }

    public function testNonRegistraCertificatoSeIlPdfNonVieneSalvato(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createStub(ArchivioPdfCertificati::class);
        $repo->method('esisteUtente')->willReturn(true);
        $repo->expects(self::never())->method('salvaOaggiorna');
        $archivio->method('salva')->willReturn(null);

        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, []);

        self::assertNotNull($errore);
    }

    public function testPropagaErroreSeIlSalvataggioDatabaseFallisce(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createStub(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'nuovo.pdf'];
        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $repo->method('esisteUtente')->willReturn(true);
        $archivio->method('salva')->willReturn('archiviato.pdf');
        $repo->method('salvaOaggiorna')->willThrowException(new RuntimeException('Database offline'));

        $this->expectException(RuntimeException::class);
        (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf);
    }
}
