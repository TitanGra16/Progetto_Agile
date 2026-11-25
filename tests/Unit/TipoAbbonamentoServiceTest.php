<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use Ladythelemons\Fitslot\Service\TipoAbbonamentoService;
use PHPUnit\Framework\TestCase;

final class TipoAbbonamentoServiceTest extends TestCase
{
    private function servizio(): TipoAbbonamentoService
    {
        return new TipoAbbonamentoService($this->createStub(TipoAbbonamentoRepository::class));
    }

    public function testPacchettoUsaIngressiSenzaScadenza(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Pacchetto 10', 'categoria' => 'PACCHETTO', 'prezzo' => '100',
            'ingressi_totali' => '10', 'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);
        self::assertSame([], $risultato['errori']);
        self::assertSame(10, $risultato['dati']['ingressi_totali']);
        self::assertNull($risultato['dati']['durata_mesi']);
    }

    public function testAbbonamentoTempoUsaDurataIllimitata(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Mensile', 'categoria' => 'TEMPO', 'prezzo' => '200',
            'ingressi_totali' => '', 'durata_mesi' => '1',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);
        self::assertSame([], $risultato['errori']);
        self::assertNull($risultato['dati']['ingressi_totali']);
        self::assertSame(1, $risultato['dati']['durata_mesi']);
    }

    public function testIngressoSingoloHaUnSoloIngresso(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Ingresso', 'categoria' => 'SINGOLO', 'prezzo' => '15',
            'ingressi_totali' => '1', 'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);
        self::assertSame([], $risultato['errori']);
        self::assertSame(1, $risultato['dati']['ingressi_totali']);
    }

    public function testRifiutaPrezzoNonValido(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Pacchetto', 'categoria' => 'PACCHETTO', 'prezzo' => '0',
            'ingressi_totali' => '10', 'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);

        self::assertArrayHasKey('prezzo', $risultato['errori']);
    }

    public function testRifiutaPacchettoConDurata(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Pacchetto', 'categoria' => 'PACCHETTO', 'prezzo' => '100',
            'ingressi_totali' => '10', 'durata_mesi' => '1',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);

        self::assertArrayHasKey('durata_mesi', $risultato['errori']);
    }

    public function testRifiutaAbbonamentoATempoConIngressi(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Mensile', 'categoria' => 'TEMPO', 'prezzo' => '200',
            'ingressi_totali' => '10', 'durata_mesi' => '1',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);

        self::assertArrayHasKey('ingressi_totali', $risultato['errori']);
    }

    public function testRifiutaScontoStudentiSuAbbonamentoATempo(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Mensile', 'categoria' => 'TEMPO', 'prezzo' => '200',
            'ingressi_totali' => '', 'durata_mesi' => '1',
            'servizi_inclusi' => ['SALA_PESI'], 'agevolazione_studenti' => '1',
            'attivo' => '1',
        ]);

        self::assertArrayHasKey('agevolazione_studenti', $risultato['errori']);
    }

    public function testRifiutaCategoriaSconosciuta(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Piano', 'categoria' => 'SETTIMANALE', 'prezzo' => '20',
            'ingressi_totali' => '', 'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);

        self::assertArrayHasKey('categoria', $risultato['errori']);
    }

    public function testRifiutaPacchettoConUnSoloIngresso(): void
    {
        $risultato = $this->servizio()->valida([
            'nome' => 'Pacchetto', 'categoria' => 'PACCHETTO', 'prezzo' => '20',
            'ingressi_totali' => '1', 'durata_mesi' => '',
            'servizi_inclusi' => ['SALA_PESI'], 'attivo' => '1',
        ]);

        self::assertArrayHasKey('ingressi_totali', $risultato['errori']);
    }

    public function testCalcolaIlPrezzoScontatoStudenti(): void
    {
        self::assertSame('80.00', TipoAbbonamentoService::calcolaPrezzoStudente('100.00', 20));
    }
}
