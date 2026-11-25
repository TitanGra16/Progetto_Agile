<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use mysqli_sql_exception;
use PHPUnit\Framework\TestCase;

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

    public function testDataImpossibileOMancanteRifiutata(): void
    {
        $errore = CertificatoMedicoService::validaScadenza('2026-02-30', new DateTimeImmutable('2026-01-01'));

        self::assertSame('Inserisci una data di scadenza valida.', $errore);
        self::assertSame('Inserisci una data di scadenza valida.', CertificatoMedicoService::validaScadenza(''));
    }

    public function testStatoCalcolatoDallaScadenza(): void
    {
        $oggi = new DateTimeImmutable('2026-11-16');

        self::assertSame('ASSENTE', CertificatoMedicoService::stato(null, $oggi));
        self::assertSame('SCADUTO', CertificatoMedicoService::stato('2026-11-15', $oggi));
        self::assertSame('VALIDO', CertificatoMedicoService::stato('2026-11-16', $oggi));
        self::assertSame('VALIDO', CertificatoMedicoService::stato('2026-11-17', $oggi));
    }

    public function testRichiedeConfermaManualePrimaDiCaricareIlPdf(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $archivio->expects(self::never())->method('salva');

        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, date('Y-m-d'), []);

        self::assertSame('Conferma di aver verificato il certificato cartaceo.', $errore);
    }

    public function testRegistrazioneCaricaPdfEArchiviaScadenza(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'certificato.pdf'];
        $scadenza = (new DateTimeImmutable('+2 days'))->format('Y-m-d');

        $repo->expects(self::once())->method('trovaUtente')->with(7)->willReturn(['id' => 7]);
        $archivio->expects(self::once())->method('salva')->with($pdf)->willReturn('archiviato.pdf');
        $repo->expects(self::once())->method('salvaOaggiorna')->with(7, $scadenza, 'archiviato.pdf')->willReturn(null);
        $archivio->expects(self::never())->method('elimina');

        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf, true);

        self::assertNull($errore);
    }

    public function testEliminaIlPdfPrecedenteDopoLAggiornamento(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('salvaOaggiorna')->willReturn('precedente.pdf');
        $archivio->method('salva')->willReturn('nuovo.pdf');
        $archivio->expects(self::once())->method('elimina')->with('precedente.pdf');

        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, date('Y-m-d'), [], true);

        self::assertNull($errore);
    }

    public function testNonRegistraCertificatoSeLIscrittoNonEsiste(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $repo->method('trovaUtente')->willReturn(null);
        $archivio->expects(self::never())->method('salva');

        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, [], true);

        self::assertSame('Iscritto non trovato.', $errore);
    }

    public function testNonRegistraCertificatoSeIlPdfNonVieneSalvato(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createStub(ArchivioPdfCertificati::class);
        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->expects(self::never())->method('salvaOaggiorna');
        $archivio->method('salva')->willReturn(null);

        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, [], true);

        self::assertNotNull($errore);
    }

    public function testEliminaSoloIlNuovoPdfSeIlSalvataggioDatabaseFallisce(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);
        $pdf = ['name' => 'nuovo.pdf'];
        $scadenza = (new DateTimeImmutable('+1 day'))->format('Y-m-d');
        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $archivio->method('salva')->willReturn('archiviato.pdf');
        $repo->method('salvaOaggiorna')->willThrowException(new mysqli_sql_exception('Salvataggio fallito'));
        $archivio->expects(self::once())->method('elimina')->with('archiviato.pdf');

        $errore = (new CertificatoMedicoService($repo, $archivio))->registra(7, $scadenza, $pdf, true);

        self::assertSame('Non è stato possibile salvare il certificato.', $errore);
    }

    public function testDownloadSenzaCertificatoOFileRestituisceNull(): void
    {
        $repo = $this->createStub(CertificatoMedicoRepository::class);
        $repo->method('trovaNomeFile')->willReturn(null, str_repeat('a', 32) . '.pdf');
        $servizio = new CertificatoMedicoService($repo, new ArchivioPdfCertificati());

        self::assertNull($servizio->percorsoDownload(7));
        self::assertNull($servizio->percorsoDownload(9));
    }
}
