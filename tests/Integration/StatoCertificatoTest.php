<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;
use PHPUnit\Framework\TestCase;

final class UE53StatoCertificatoTest extends TestCase
{
    private ?mysqli $connessione = null;
    private CertificatoMedicoRepository $repository;
    private int $idMario;
    private int $idGiulia;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();

        // Le tabelle temporanee non modificano i dati reali.
        $this->connessione->query(
            'CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                data_nascita DATE NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                recapito VARCHAR(190) NOT NULL
            ) ENGINE=InnoDB'
        );

        $this->connessione->query(
            'CREATE TEMPORARY TABLE certificati_medici (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                utente_id BIGINT UNSIGNED NOT NULL,
                data_scadenza DATE NOT NULL,
                verificato_at DATETIME NOT NULL,
                nome_file VARCHAR(80) NOT NULL,
                created_at TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_certificato_utente (utente_id)
            ) ENGINE=InnoDB'
        );

        $this->repository = new CertificatoMedicoRepository(
            $this->connessione
        );

        $this->idMario = $this->creaUtente(
            'Mario',
            'mario-test@example.test'
        );

        $this->idGiulia = $this->creaUtente(
            'Giulia',
            'giulia-test@example.test'
        );
    }

    protected function tearDown(): void
    {
        // Alla chiusura le tabelle temporanee vengono eliminate.
        $this->connessione?->close();
        $this->connessione = null;
    }

    private function creaUtente(string $nome, string $email): int
    {
        $this->connessione->execute_query(
            'INSERT INTO utenti (
                nome, cognome, data_nascita, email, recapito
            ) VALUES (?, "Rossi", "2000-05-15", ?, "0000000000")',
            [$nome, $email]
        );

        return (int) $this->connessione->insert_id;
    }

    private function inserisciCertificato(
        int $idUtente,
        string $scadenza,
        string $nomeFile = 'ue53-test.pdf'
    ): void {
        $this->connessione->execute_query(
            'INSERT INTO certificati_medici (
                utente_id,
                data_scadenza,
                verificato_at,
                nome_file
            ) VALUES (?, ?, "2026-11-20 10:00:00", ?)',
            [$idUtente, $scadenza, $nomeFile]
        );
    }

    // TEST 1: certificato assente
    public function testCertificatoAssente(): void
    {
        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertNull($certificato);

        self::assertSame(
            'ASSENTE',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'] ?? null
            )
        );
    }

    // TEST 2: certificato con scadenza futura
    public function testCertificatoValido(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertNotNull($certificato);
        self::assertSame(
            '2027-06-15',
            $certificato['data_scadenza']
        );

        self::assertSame(
            'VALIDO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }

    // TEST 3: il certificato è valido nel giorno di scadenza
    public function testCertificatoValidoIlGiornoDellaScadenza(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2026-11-25'
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertSame(
            'VALIDO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }

    // TEST 4: certificato scaduto
    public function testCertificatoScaduto(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2026-11-24'
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertSame(
            'SCADUTO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }

    // TEST 5: un iscritto non vede il certificato di un altro
    public function testCertificatoAssociatoSoloAlProprietario(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        self::assertNotNull(
            $this->repository->trovaPerUtente($this->idMario)
        );

        self::assertNull(
            $this->repository->trovaPerUtente($this->idGiulia)
        );
    }

    // TEST 6: due iscritti con stati differenti
    public function testDueIscrittiHannoStatiIndipendenti(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        $this->inserisciCertificato(
            $this->idGiulia,
            '2026-01-01',
            'giulia-test.pdf'
        );

        $mario = $this->repository->trovaPerUtente(
            $this->idMario
        );

        $giulia = $this->repository->trovaPerUtente(
            $this->idGiulia
        );

        $oggi = new DateTimeImmutable('2026-11-25');

        self::assertSame(
            'VALIDO',
            CertificatoMedicoService::stato(
                $mario['data_scadenza'],
                $oggi
            )
        );

        self::assertSame(
            'SCADUTO',
            CertificatoMedicoService::stato(
                $giulia['data_scadenza'],
                $oggi
            )
        );
    }

    // TEST 7: identificativo inesistente
    public function testUtenteInesistenteNonHaCertificato(): void
    {
        self::assertNull(
            $this->repository->trovaPerUtente(999999)
        );
    }

    // TEST 8: un certificato valido può diventare scaduto
    public function testAggiornamentoDaValidoAScaduto(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        $this->connessione->execute_query(
            'UPDATE certificati_medici
             SET data_scadenza = ?
             WHERE utente_id = ?',
            ['2026-01-01', $this->idMario]
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertSame(
            'SCADUTO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }

    // TEST 9: dopo il rinnovo torna valido
    public function testAggiornamentoDaScadutoAValido(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2026-01-01'
        );

        $this->connessione->execute_query(
            'UPDATE certificati_medici
             SET data_scadenza = ?
             WHERE utente_id = ?',
            ['2027-06-15', $this->idMario]
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertSame(
            'VALIDO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }

    // TEST 10: eliminando il certificato lo stato diventa ASSENTE
    public function testEliminazioneCertificato(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        $this->connessione->execute_query(
            'DELETE FROM certificati_medici WHERE utente_id = ?',
            [$this->idMario]
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertNull($certificato);

        self::assertSame(
            'ASSENTE',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'] ?? null
            )
        );
    }

    // TEST 11: legge correttamente i dati del certificato
    public function testRecuperaDatiCompletiDelCertificato(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15',
            'documento-mario.pdf'
        );

        $certificato = $this->repository->trovaPerUtente(
            $this->idMario
        );

        self::assertNotNull($certificato);
        self::assertSame(
            $this->idMario,
            (int) $certificato['utente_id']
        );
        self::assertSame(
            '2027-06-15',
            $certificato['data_scadenza']
        );
        self::assertSame(
            'documento-mario.pdf',
            $certificato['nome_file']
        );
        self::assertSame(
            '2026-11-20 10:00:00',
            $certificato['verificato_at']
        );
    }

    // TEST 12: la ricerca Reception usa l'ID corretto
    public function testRicercaReceptionRecuperaIlCertificatoGiusto(): void
    {
        $this->inserisciCertificato(
            $this->idMario,
            '2027-06-15'
        );

        $this->inserisciCertificato(
            $this->idGiulia,
            '2026-01-01',
            'giulia-test.pdf'
        );

        $archivioUtenti = new UtenteRepository(
            $this->connessione
        );

        $risultati = $archivioUtenti->cerca('Mario');

        self::assertCount(1, $risultati);
        self::assertSame(
            $this->idMario,
            $risultati[0]->getId()
        );

        $certificato = $this->repository->trovaPerUtente(
            (int) $risultati[0]->getId()
        );

        self::assertSame(
            'VALIDO',
            CertificatoMedicoService::stato(
                $certificato['data_scadenza'],
                new DateTimeImmutable('2026-11-25')
            )
        );
    }
}
