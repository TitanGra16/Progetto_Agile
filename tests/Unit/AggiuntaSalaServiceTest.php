<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\DataModel\Sala;
use Ladythelemons\Fitslot\Repository\SaleRepository;
use Ladythelemons\Fitslot\Service\AggiuntaSalaService;
use PHPUnit\Framework\TestCase;

final class AggiuntaSalaServiceTest extends TestCase
{
    public function testAggiungeSalaValida(): void
    {
        $datiSala = [
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ];

        $salaRepositoryMock = $this->createMock(SaleRepository::class);
        $salaRepositoryMock->expects($this->once())
            ->method('esisteSalaConNome')
            ->with('Sala Prova')
            ->willReturn(false);

        $salaRepositoryMock->expects($this->once())
            ->method('salva')
            ->with($this->callback(function (Sala $sala) use ($datiSala) {
                return $sala->getNome() === $datiSala['nome'] &&
                    $sala->getCapienza() === $datiSala['capienza'] &&
                    $sala->getInizioOccupazione() === $datiSala['inizio_occupazione'] &&
                    $sala->getFineOccupazione() === $datiSala['fine_occupazione'];
            }));

        $service = new AggiuntaSalaService($salaRepositoryMock);
        $result = $service->aggiunge($datiSala);

        $this->assertSame('successo', $result['esito']);
        $this->assertArrayHasKey('codice_sala', $result);
    }

    public function testAggiungeSalaConNomeDuplicato(): void
    {
        $datiSala = [
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ];

        $salaRepositoryMock = $this->createMock(SaleRepository::class);
        $salaRepositoryMock->expects($this->once())
            ->method('esisteSalaConNome')
            ->with('Sala Prova')
            ->willReturn(true);

        $service = new AggiuntaSalaService($salaRepositoryMock);
        $result = $service->aggiunge($datiSala);

        $this->assertSame('nome_duplicato', $result['esito']);
        $this->assertSame('Nome della sala già esistente.', $result['messaggio']);
    }

    public function testAggiungeSalaConCapienzaNegativa(): void
    {
        $datiSala = [
            'nome' => 'Sala Prova',
            'capienza' => -5,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ];

        $salaRepositoryMock = $this->createMock(SaleRepository::class);
        $salaRepositoryMock->expects($this->never())
            ->method('esisteSalaConNome');

        $service = new AggiuntaSalaService($salaRepositoryMock);
        $result = $service->aggiunge($datiSala);

        $this->assertSame('dati_non_validi', $result['esito']);
        $this->assertSame('I dati della sala sono mancanti o non validi.', $result['messaggio']);
    }

    public function testAggiungeSalaConOrarioInizioMaggioreDiFine(): void
    {
        $datiSala = [
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '21:00',
            'fine_occupazione' => '20:00',
        ];

        $salaRepositoryMock = $this->createMock(SaleRepository::class);
        $salaRepositoryMock->expects($this->never())
            ->method('esisteSalaConNome');

        $service = new AggiuntaSalaService($salaRepositoryMock);
        $result = $service->aggiunge($datiSala);

        $this->assertSame('dati_non_validi', $result['esito']);
        $this->assertSame('I dati della sala sono mancanti o non validi.', $result['messaggio']);
    }
}
