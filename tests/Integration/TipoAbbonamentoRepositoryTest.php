<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use PHPUnit\Framework\TestCase;

final class TipoAbbonamentoRepositoryTest extends TestCase
{
    private ?mysqli $connessione = null;

    protected function setUp(): void
    {
        if (getenv('FITSLOT_RUN_DB_TESTS') !== '1') {
            self::markTestSkipped('Richiede FITSLOT_RUN_DB_TESTS=1 e migration UE-08 applicata.');
        }

        $this->connessione = Database::connetti();
        $this->connessione->begin_transaction();
    }

    protected function tearDown(): void
    {
        $this->connessione?->rollback();
        $this->connessione?->close();
    }

    public function testCreaLeggeEAggiornaUnTipoDiAbbonamento(): void
    {
        $repository = new TipoAbbonamentoRepository($this->connessione);
        $dati = [
            'nome' => 'Piano integrazione',
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

        $id = $repository->crea($dati);
        self::assertGreaterThan(0, $id);
        $letto = $repository->trovaPerId($id);
        self::assertNotNull($letto);
        self::assertSame($dati, array_intersect_key($letto, $dati));

        $dati['nome'] = 'Piano aggiornato';
        $dati['prezzo'] = '90.00';
        $repository->aggiorna($id, $dati);

        $aggiornato = $repository->trovaPerId($id);
        self::assertNotNull($aggiornato);
        self::assertSame($dati, array_intersect_key($aggiornato, $dati));
    }
}
