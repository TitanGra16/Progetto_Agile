<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use PHPUnit\Framework\TestCase;

final class UtenteRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private UtenteRepository $archivio;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();
        $this->connessione->query('
            CREATE TEMPORARY TABLE utenti (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                cognome VARCHAR(100) NOT NULL,
                data_nascita DATE NOT NULL,
                email VARCHAR(255) NOT NULL,
                recapito VARCHAR(190) NOT NULL,
                UNIQUE KEY email_unica (email)
            ) ENGINE=InnoDB
        ');
        $this->connessione->query('
            CREATE TEMPORARY TABLE account (
                id BIGINT UNSIGNED PRIMARY KEY,
                password_hash VARCHAR(255) NULL,
                stato ENUM("PENDING", "ACTIVE") NOT NULL DEFAULT "PENDING"
            ) ENGINE=InnoDB
        ');
        $this->connessione->begin_transaction();
        $this->archivio = new UtenteRepository($this->connessione);
    }

    protected function tearDown(): void
    {
        $this->connessione?->rollback();
        $this->connessione?->close();
    }

    private function utente(string $nome = 'Anna', string $email = 'anna@example.test'): Utente
    {
        return new Utente(null, $nome, "D'Angelo", '2000-02-29', 'Recapito di prova', $email);
    }

    private function salvaUtente(string $nome, string $cognome, string $email): int
    {
        return $this->archivio->salva(new Utente(
            null,
            $nome,
            $cognome,
            '2000-02-29',
            'Recapito di prova',
            $email
        ));
    }

    public function testSalvaUtente(): void
    {
        $id = $this->archivio->salva($this->utente(email: 'iscritto@example.test'));
        $riga = $this->connessione->query('SELECT * FROM utenti WHERE id = ' . $id)->fetch_assoc();

        $this->assertGreaterThan(0, $id);
        $this->assertSame('Anna', $riga['nome']);
        $this->assertSame('iscritto@example.test', $riga['email']);
    }

    public function testTrovaDuplicati(): void
    {
        $this->assertFalse($this->archivio->esistePotenzialeDuplicato('Anna', "D'Angelo", '2000-02-29'));
        $this->archivio->salva($this->utente());
        $this->assertTrue($this->archivio->esistePotenzialeDuplicato('Anna', "D'Angelo", '2000-02-29'));
        $this->assertFalse($this->archivio->esistePotenzialeDuplicato('Anna', 'Rossi', '2000-02-29'));
    }

    public function testEmailUnica(): void
    {
        $this->archivio->salva($this->utente(email: 'usata@example.test'));

        $this->assertTrue($this->archivio->esisteEmail('usata@example.test'));
        $this->assertFalse($this->archivio->esisteEmail('altra@example.test'));

        $this->expectException(mysqli_sql_exception::class);
        $this->archivio->salva($this->utente('Altro nome', 'usata@example.test'));
    }

    public function testGestisceTestoSpeciale(): void
    {
        $nome = "Prova' OR '1'='1";
        $this->archivio->salva($this->utente($nome, 'sql@example.test'));

        $this->assertTrue($this->archivio->esistePotenzialeDuplicato($nome, "D'Angelo", '2000-02-29'));
        $this->assertFalse($this->archivio->esistePotenzialeDuplicato("' OR 1=1 -- ", "D'Angelo", '2000-02-29'));
    }

    public function testCercaPerNomeCognomeNomeCompletoECodice(): void
    {
        $idPrimo = $this->salvaUtente('Marco', 'Bianchi', 'marco.bianchi@example.test');
        $idSecondo = $this->salvaUtente('Marco', 'Rossi', 'marco.rossi@example.test');

        $perNome = $this->archivio->cerca('Marco');
        $this->assertCount(2, $perNome);

        $perCognome = $this->archivio->cerca('Bianchi');
        $this->assertCount(1, $perCognome);
        $this->assertSame($idPrimo, $perCognome[0]->getId());

        $perNomeCompleto = $this->archivio->cerca('Marco Rossi');
        $this->assertCount(1, $perNomeCompleto);
        $this->assertSame($idSecondo, $perNomeCompleto[0]->getId());

        $perCodice = $this->archivio->cerca((string) $idPrimo);
        $this->assertCount(1, $perCodice);
        $this->assertSame($idPrimo, $perCodice[0]->getId());
    }

    public function testCercaDistingueGliAccenti(): void
    {
        $idConAccento = $this->salvaUtente('Mò', 'Rossi', 'mo.rossi@example.test');
        $this->salvaUtente('Mo', 'Bianchi', 'mo.bianchi@example.test');

        $risultati = $this->archivio->cerca('Mò');

        $this->assertCount(1, $risultati);
        $this->assertSame($idConAccento, $risultati[0]->getId());
    }

    public function testCercaRestituisceArrayVuotoSeNonTrova(): void
    {
        $this->assertSame([], $this->archivio->cerca('Inesistente'));
    }

    public function testRestituisceLoStatoDellAccount(): void
    {
        $id = $this->archivio->salva($this->utente(email: 'stato@example.test'));
        $query = $this->connessione->prepare('INSERT INTO account (id, stato) VALUES (?, ?)');
        $stato = 'ACTIVE';
        $query->bind_param('is', $id, $stato);
        $query->execute();
        $query->close();

        $this->assertSame('ACTIVE', $this->archivio->getStatoIscritto($id));
    }

    public function testRestituiscePendingPerAccountAssente(): void
    {
        $id = $this->archivio->salva($this->utente(email: 'senza-account@example.test'));

        $this->assertSame('PENDING', $this->archivio->getStatoIscritto($id));
    }
}
