<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use Ladythelemons\Fitslot\Service\TipoAbbonamentoService;
use PHPUnit\Framework\TestCase;

final class TipoAbbonamentoServiceTest extends TestCase
{
    public function testPacchettoSenzaScadenzaUsaSoloGliIngressi(): void
    {
        $servizio = new TipoAbbonamentoService($this->createMock(TipoAbbonamentoRepository::class));

        $risultato = $servizio->valida([
            'nome' => 'Pacchetto 10 ingressi',
            'categoria' => 'PACCHETTO',
            'prezzo' => '100',
            'ingressi_totali' => '10',
            'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI', 'PISCINA', 'CORSI'],
            'attivo' => '1',
        ]);

        self::assertSame([], $risultato['errori']);
        self::assertSame(10, $risultato['dati']['ingressi_totali']);
        self::assertNull($risultato['dati']['durata_mesi']);
    }

    public function testAbbonamentoAtempoUsaSoloLaDurata(): void
    {
        $servizio = new TipoAbbonamentoService($this->createMock(TipoAbbonamentoRepository::class));

        $risultato = $servizio->valida([
            'nome' => 'Mensile illimitato',
            'categoria' => 'TEMPO',
            'prezzo' => '200',
            'ingressi_totali' => '',
            'durata_mesi' => '1',
            'servizi_inclusi' => ['SALA_PESI', 'PISCINA', 'CORSI'],
            'attivo' => '1',
        ]);

        self::assertSame([], $risultato['errori']);
        self::assertNull($risultato['dati']['ingressi_totali']);
        self::assertSame(1, $risultato['dati']['durata_mesi']);
    }

    public function testIngressoSingoloVieneForzatoAUnIngresso(): void
    {
        $servizio = new TipoAbbonamentoService($this->createMock(TipoAbbonamentoRepository::class));

        $risultato = $servizio->valida([
            'nome' => 'Ingresso singolo',
            'categoria' => 'SINGOLO',
            'prezzo' => '15',
            'ingressi_totali' => '1',
            'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'],
            'attivo' => '1',
        ]);

        self::assertSame([], $risultato['errori']);
        self::assertSame(1, $risultato['dati']['ingressi_totali']);
        self::assertNull($risultato['dati']['durata_mesi']);
    }
}
