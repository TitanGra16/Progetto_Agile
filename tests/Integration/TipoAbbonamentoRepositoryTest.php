<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use PHPUnit\Framework\TestCase;

final class TipoAbbonamentoRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;
    private ?TipoAbbonamentoRepository $repository = null;

    protected function setUp(): void
    {
        $this->connessione = Database::connetti();
        $this->connessione->begin_transaction();
        $this->repository = new TipoAbbonamentoRepository($this->connessione);
    }

    protected function tearDown(): void
    {
        $this->connessione?->rollback();
        $this->connessione?->close();
    }

    public function testCreaELeggeUnPiano(): void
    {
        $dati = $this->dati();
        $id = $this->repository->crea($dati);

        self::assertGreaterThan(0, $id);
        $piano = $this->repository->trovaPerId($id);
        self::assertNotNull($piano);
        self::assertSame($dati, array_intersect_key($piano, $dati));
    }

    public function testAggiornaNomePrezzoEStato(): void
    {
        $id = $this->repository->crea($this->dati());
        $dati = $this->dati();
        $dati['nome'] = 'Piano aggiornato';
        $dati['prezzo'] = '95.00';
        $dati['attivo'] = 0;

        $this->repository->aggiorna($id, $dati);
        $piano = $this->repository->trovaPerId($id);

        self::assertNotNull($piano);
        self::assertSame('Piano aggiornato', $piano['nome']);
        self::assertSame('95.00', $piano['prezzo']);
        self::assertSame(0, $piano['attivo']);
    }

    public function testElencaIncludeIlPianoCreato(): void
    {
        $id = $this->repository->crea($this->dati());
        $ids = array_column($this->repository->elenca(), 'id');

        self::assertContains($id, $ids);
    }

    public function testRestituisceNullPerIdInesistente(): void
    {
        self::assertNull($this->repository->trovaPerId(999999999));
    }

    private function dati(): array
    {
        return [
            'nome' => 'Test ' . bin2hex(random_bytes(5)),
            'categoria' => 'PACCHETTO',
            'prezzo' => '85.00',
            'ingressi_totali' => 8,
            'durata_mesi' => null,
            'servizi_inclusi' => ['SALA_PESI', 'CORSI'],
            'sconto_studenti_percentuale' => 20,
            'studenti_dalle' => '08:00',
            'studenti_alle' => '13:30',
            'attivo' => 1,
        ];
    }
}
