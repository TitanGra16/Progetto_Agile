<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\SaleRepository;
use Ladythelemons\Fitslot\Service\AggiuntaSalaService;
use PHPUnit\Framework\TestCase;

final class AggiuntaSalaServiceTest extends TestCase
{
    public function testAggiungeSalaValida(): void
    {
        $repository = $this->createMock(SaleRepository::class);
        $repository->method('esisteSalaConNome')->willReturn(false);
        $repository->expects($this->once())->method('salva')->willReturn(42);

        $risultato = (new AggiuntaSalaService($repository))->aggiunge([
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ]);

        self::assertSame('successo', $risultato['esito']);
    }

    public function testRifiutaDatiSalaNonValidi(): void
    {
        $repository = $this->createMock(SaleRepository::class);
        $repository->expects($this->never())->method('esisteSalaConNome');

        $risultato = (new AggiuntaSalaService($repository))->aggiunge([
            'nome' => 'Sala Prova',
            'capienza' => 0,
            'inizio_occupazione' => '21:00',
            'fine_occupazione' => '20:00',
        ]);

        self::assertSame('dati_non_validi', $risultato['esito']);
    }

    public function testRifiutaNomeSalaGiaEsistente(): void
    {
        $repository = $this->createMock(SaleRepository::class);
        $repository->method('esisteSalaConNome')->willReturn(true);
        $repository->expects($this->never())->method('salva');

        $risultato = (new AggiuntaSalaService($repository))->aggiunge([
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ]);

        self::assertSame('nome_duplicato', $risultato['esito']);
    }
}
