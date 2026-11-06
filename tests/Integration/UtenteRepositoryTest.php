<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use PHPUnit\Framework\TestCase;

final class UtenteRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private UtenteRepository $archivioUtenti;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();

        // Solo questa connessione vede la tabella temporanea.
        // La tabella utenti reale e i suoi dati restano intatti.
        $this->connessione->query('
            CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                data_nascita DATE NOT NULL,
                recapito VARCHAR(190) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password_utente VARCHAR(255) NOT NULL
            )
        ');
        $this->connessione->begin_transaction();
        $this->archivioUtenti = new UtenteRepository($this->connessione);
    }

    protected function tearDown(): void
    {
        if ($this->connessione !== null) {
            try {
                $this->connessione->rollback();
            } finally {
                // La chiusura rimuove anche la tabella temporanea.
                $this->connessione->close();
            }
        }
    }

    private function creaUtenteDiProva(): Utente
    {
        return new Utente(
            null,
            'Anna',
            "D'Angelo",
            '2000-02-29',
            'Recapito di prova',
            'iscritto@example.test',
            password_hash('password_di_prova', PASSWORD_DEFAULT)
        );
    }

    public function testSalvaUnIscrittoERestituisceIlSuoId(): void
    {
        $utente = $this->creaUtenteDiProva();
        $id = $this->archivioUtenti->salva($utente);
        $this->assertGreaterThan(0, $id);

        $risultato = $this->connessione->query('SELECT * FROM utenti WHERE id = ' . $id);
        $riga = $risultato->fetch_assoc();

        $this->assertNotNull($riga);
        $this->assertSame($utente->getNome(), $riga['nome']);
        $this->assertSame($utente->getCognome(), $riga['cognome']);
        $this->assertSame($utente->getDataNascita(), $riga['data_nascita']);
        $this->assertSame($utente->getRecapito(), $riga['recapito']);
        $this->assertSame($utente->getEmail(), $riga['email']);
        $this->assertSame($utente->getPassword(), $riga['password_utente']);
        $this->assertTrue(password_verify('password_di_prova', $riga['password_utente']));
    }

    public function testRiconosceUnPotenzialeDuplicato(): void
    {
        $this->assertFalse($this->archivioUtenti->esistePotenzialeDuplicato('Anna', "D'Angelo", '2000-02-29'));
        $this->archivioUtenti->salva($this->creaUtenteDiProva());
        $this->assertTrue($this->archivioUtenti->esistePotenzialeDuplicato('Anna', "D'Angelo", '2000-02-29'));
        $this->assertFalse($this->archivioUtenti->esistePotenzialeDuplicato('Anna', "D'Angelo", '2001-02-28'));
        $this->assertFalse($this->archivioUtenti->esistePotenzialeDuplicato('Anna', 'Rossi', '2000-02-29'));
    }

    public function testIlTestoSQLVieneTrattatoComeUnDato(): void
    {
        $nome = "Prova' OR '1'='1";
        $utente = new Utente(null, $nome, 'Cognome di prova', '2000-01-01', 'Recapito di prova', 'sql@example.test', password_hash('password_di_prova', PASSWORD_DEFAULT));
        $this->archivioUtenti->salva($utente);

        $this->assertTrue($this->archivioUtenti->esistePotenzialeDuplicato($nome, 'Cognome di prova', '2000-01-01'));
        $this->assertFalse($this->archivioUtenti->esistePotenzialeDuplicato("' OR 1=1 -- ", 'Cognome di prova', '2000-01-01'));
        $this->assertSame('1', $this->connessione->query('SELECT COUNT(*) FROM utenti')->fetch_row()[0]);
    }
}
