<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use PHPUnit\Framework\TestCase;

final class CertificatiMediciInAttesaRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private CertificatoMedicoRepository $repository;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();

        // Tabelle temporanee: non toccano quelle reali.
        $this->connessione->query(
            'CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB'
        );

        $this->connessione->query(
            'CREATE TEMPORARY TABLE certificati_medici_in_attesa (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                utente_id BIGINT UNSIGNED NOT NULL,
                nome_file VARCHAR(80) NOT NULL,
                caricato_at TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_certificato_in_attesa_utente (utente_id)
            ) ENGINE=InnoDB'
        );

        $this->connessione->query(
            'CREATE TEMPORARY TABLE certificati_medici (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                utente_id BIGINT UNSIGNED NOT NULL,
                data_scadenza DATE NOT NULL,
                verificato_at DATETIME NOT NULL,
                nome_file VARCHAR(80) NOT NULL,
                updated_at TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_certificato_utente (utente_id)
            ) ENGINE=InnoDB'
        );

        $this->connessione->query(
            "INSERT INTO utenti (id, nome, cognome)
             VALUES
                (1, 'Utente', 'Uno'),
                (2, 'Utente', 'Due')"
        );

        $this->repository =
            new CertificatoMedicoRepository($this->connessione);
    }

    protected function tearDown(): void
    {
        // Chiudendo la connessione spariscono le tabelle temporanee.
        $this->connessione?->close();
        $this->connessione = null;
    }

    public function testTrovaUtenteEsistente(): void
    {
        $utente = $this->repository->trovaUtente(1);

        self::assertNotNull($utente);
        self::assertSame('Utente', $utente['nome']);
        self::assertSame('Uno', $utente['cognome']);
    }

    public function testUtenteInesistente(): void
    {
        self::assertNull($this->repository->trovaUtente(999));
    }

    public function testCertificatoInAttesaAssente(): void
    {
        self::assertNull(
            $this->repository->trovaInAttesaPerUtente(1)
        );
    }

    public function testSalvaERecuperaCertificatoInAttesa(): void
    {
        $nomeFile = str_repeat('a', 32) . '.pdf';

        $this->repository->salvaInAttesa(1, $nomeFile);

        $certificato =
            $this->repository->trovaInAttesaPerUtente(1);

        self::assertNotNull($certificato);
        self::assertGreaterThan(0, (int) $certificato['id']);
        self::assertSame(1, (int) $certificato['utente_id']);
        self::assertSame($nomeFile, $certificato['nome_file']);
        self::assertNotEmpty($certificato['caricato_at']);
    }

    public function testCertificatiSeparatiPerUtente(): void
    {
        $pdf1 = str_repeat('a', 32) . '.pdf';
        $pdf2 = str_repeat('b', 32) . '.pdf';

        $this->repository->salvaInAttesa(1, $pdf1);
        $this->repository->salvaInAttesa(2, $pdf2);

        $certificato1 =
            $this->repository->trovaInAttesaPerUtente(1);

        $certificato2 =
            $this->repository->trovaInAttesaPerUtente(2);

        self::assertSame($pdf1, $certificato1['nome_file']);
        self::assertSame($pdf2, $certificato2['nome_file']);
        self::assertNotSame(
            $certificato1['id'],
            $certificato2['id']
        );
    }

    public function testImpedisceDuplicatiNelDatabase(): void
    {
        $primoPdf = str_repeat('a', 32) . '.pdf';
        $secondoPdf = str_repeat('b', 32) . '.pdf';

        $this->repository->salvaInAttesa(1, $primoPdf);

        try {
            $this->repository->salvaInAttesa(1, $secondoPdf);
            self::fail('Il database doveva rifiutare il duplicato.');
        } catch (mysqli_sql_exception $errore) {
            self::assertSame(1062, $errore->getCode());
        }

        $certificato =
            $this->repository->trovaInAttesaPerUtente(1);

        self::assertSame($primoPdf, $certificato['nome_file']);
    }

    public function testNonModificaCertificatoGiaVerificato(): void
    {
        $this->connessione->query(
            "INSERT INTO certificati_medici
                (utente_id, data_scadenza, verificato_at, nome_file)
             VALUES
                (1, '2030-12-31', '2026-11-01 10:00:00',
                 'certificato-verificato.pdf')"
        );

        $nuovoPdf = str_repeat('c', 32) . '.pdf';

        $this->repository->salvaInAttesa(1, $nuovoPdf);

        $verificato = $this->repository->trovaPerUtente(1);
        $inAttesa = $this->repository->trovaInAttesaPerUtente(1);

        self::assertNotNull($verificato);
        self::assertNotNull($inAttesa);

        self::assertSame(
            'certificato-verificato.pdf',
            $verificato['nome_file']
        );

        self::assertSame(
            '2030-12-31',
            $verificato['data_scadenza']
        );

        self::assertSame($nuovoPdf, $inAttesa['nome_file']);
    }
}

