<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Tests\Unit;

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CaricamentoCertificatoService;
use mysqli_sql_exception;
use PHPUnit\Framework\TestCase;

final class CaricamentoCertificatoServiceTest extends TestCase
{
    public function testRifiutaIdUtenteNonValido(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $repo->expects(self::never())->method('trovaUtente');
        $archivio->expects(self::never())->method('salva');

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame('Utente non trovato.', $service->carica(0, []));
    }

    public function testRifiutaUtenteInesistente(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $repo->method('trovaUtente')->willReturn(null);
        $repo->expects(self::never())->method('salvaInAttesa');
        $archivio->expects(self::never())->method('salva');

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame('Utente non trovato.', $service->carica(999, []));
    }

    public function testImpedisceCaricamentoDuplicato(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('trovaInAttesaPerUtente')->willReturn([
            'id' => 1,
            'utente_id' => 7,
            'nome_file' => 'esistente.pdf',
        ]);

        $repo->expects(self::never())->method('salvaInAttesa');
        $archivio->expects(self::never())->method('salva');

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame(
            'Hai già un certificato in attesa di verifica.',
            $service->carica(7, [])
        );
    }

    public function testRifiutaPdfNonValido(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('trovaInAttesaPerUtente')->willReturn(null);

        $archivio->expects(self::once())
            ->method('salva')
            ->willReturn(null);

        $repo->expects(self::never())->method('salvaInAttesa');

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame(
            'Caricamento non riuscito. Seleziona un PDF valido di massimo 5 MB.',
            $service->carica(7, ['error' => UPLOAD_ERR_NO_FILE])
        );
    }

    public function testCaricamentoRiuscito(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $pdf = ['name' => 'certificato.pdf'];
        $nomeSalvato = str_repeat('a', 32) . '.pdf';

        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('trovaInAttesaPerUtente')->willReturn(null);

        $archivio->expects(self::once())
            ->method('salva')
            ->with($pdf)
            ->willReturn($nomeSalvato);

        $repo->expects(self::once())
            ->method('salvaInAttesa')
            ->with(7, $nomeSalvato);

        $archivio->expects(self::never())->method('elimina');

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertNull($service->carica(7, $pdf));
    }

    public function testEliminaPdfSeDatabaseFallisce(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $nomeSalvato = str_repeat('b', 32) . '.pdf';

        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('trovaInAttesaPerUtente')->willReturn(null);

        $archivio->method('salva')->willReturn($nomeSalvato);

        $repo->method('salvaInAttesa')->willThrowException(
            new mysqli_sql_exception('Errore database', 1000)
        );

        $archivio->expects(self::once())
            ->method('elimina')
            ->with($nomeSalvato);

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame(
            'Impossibile registrare il certificato. Riprova più tardi.',
            $service->carica(7, [])
        );
    }

    public function testGestisceDuplicatoDuranteIlSalvataggio(): void
    {
        $repo = $this->createMock(CertificatoMedicoRepository::class);
        $archivio = $this->createMock(ArchivioPdfCertificati::class);

        $nomeSalvato = str_repeat('c', 32) . '.pdf';

        $repo->method('trovaUtente')->willReturn(['id' => 7]);
        $repo->method('trovaInAttesaPerUtente')->willReturn(null);
        $archivio->method('salva')->willReturn($nomeSalvato);

        $repo->method('salvaInAttesa')->willThrowException(
            new mysqli_sql_exception('Duplicato', 1062)
        );

        $archivio->expects(self::once())
            ->method('elimina')
            ->with($nomeSalvato);

        $service = new CaricamentoCertificatoService($repo, $archivio);

        self::assertSame(
            'Hai già un certificato in attesa di verifica.',
            $service->carica(7, [])
        );
    }
}

