<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\AggiuntaSalaController;
use Ladythelemons\Fitslot\Service\AggiuntaSalaService;
use PHPUnit\Framework\TestCase;

final class AggiuntaSalaControllerTest extends TestCase
{
    private function dati(): array
    {
        return [
            'nome' => 'Sala Prova',
            'capienza' => 20,
            'inizio_occupazione' => '08:00',
            'fine_occupazione' => '20:00',
        ];
    }

    public function testMostraFormSenzaAggiungereSale(): void
    {
        $servizio = $this->createMock(AggiuntaSalaService::class);
        $servizio->expects($this->never())->method('aggiunge');
        $controllore = new AggiuntaSalaController($servizio);

        $pagina = $controllore->gestisciRichiesta('GET', []);

        $this->assertSame('iniziale', $pagina['esito']);
        $this->assertSame('', $pagina['dati']['nome']);
    }

    public function testAggiungeSalaEConservaIlCodice(): void
    {
        $servizio = $this->createMock(AggiuntaSalaService::class);
        $servizio->expects($this->once())->method('aggiunge')
            ->with($this->dati())
            ->willReturn([
                'esito' => 'successo',
                'messaggio' => 'Sala aggiunta con successo.',
                'codice_sala' => 42,
            ]);
        $controllore = new AggiuntaSalaController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', $this->dati());

        $this->assertSame('successo', $pagina['esito']);
        $this->assertSame(42, $pagina['codice_sala']);
        $this->assertSame('', $pagina['dati']['nome']);
    }

    public function testRifiutaDatiMancanti(): void
    {
        $servizio = $this->createMock(AggiuntaSalaService::class);
        $servizio->expects($this->never())->method('aggiunge');
        $controllore = new AggiuntaSalaController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', []);

        $this->assertSame('dati_non_validi', $pagina['esito']);
    }

    public function testRifiutaOrarioDiFineNonSuccessivo(): void
    {
        $servizio = $this->createMock(AggiuntaSalaService::class);
        $servizio->expects($this->never())->method('aggiunge');
        $controllore = new AggiuntaSalaController($servizio);
        $dati = $this->dati();
        $dati['inizio_occupazione'] = '21:00';
        $dati['fine_occupazione'] = '20:00';

        $pagina = $controllore->gestisciRichiesta('POST', $dati);

        $this->assertSame('dati_non_validi', $pagina['esito']);
        $this->assertSame(
            'L’orario di fine occupazione deve essere successivo a quello di inizio.',
            $pagina['messaggio']
        );
    }

    public function testRifiutaOrariMalformatiPrimaDiConvertirliInSecondi(): void
    {
        foreach (['08:xx', '25:00'] as $inizio) {
            $servizio = $this->createMock(AggiuntaSalaService::class);
            $servizio->expects($this->never())->method('aggiunge');
            $controllore = new AggiuntaSalaController($servizio);
            $dati = $this->dati();
            $dati['inizio_occupazione'] = $inizio;

            $pagina = $controllore->gestisciRichiesta('POST', $dati);

            $this->assertSame('dati_non_validi', $pagina['esito']);
            $this->assertSame('I dati della sala sono mancanti o non validi.', $pagina['messaggio']);
        }
    }

    public function testNomeDuplicatoPassaConOrariValidi(): void
    {
        $servizio = $this->createMock(AggiuntaSalaService::class);
        $servizio->expects($this->once())->method('aggiunge')
            ->with($this->dati())
            ->willReturn([
                'esito' => 'nome_duplicato',
                'messaggio' => 'Nome della sala già esistente.',
            ]);
        $controllore = new AggiuntaSalaController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', $this->dati());

        $this->assertSame('nome_duplicato', $pagina['esito']);
        $this->assertSame('Nome della sala già esistente.', $pagina['messaggio']);
    }
}
