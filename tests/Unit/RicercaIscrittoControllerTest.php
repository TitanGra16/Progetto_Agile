<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RicercaIscrittoController;
use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Service\RicercaIscrittoService;
use PHPUnit\Framework\TestCase;

final class RicercaIscrittoControllerTest extends TestCase
{
    public function testMostraFormSenzaCercare(): void
    {
        $servizio = $this->createMock(RicercaIscrittoService::class);
        $servizio->expects($this->never())->method('cerca');
        $controllore = new RicercaIscrittoController($servizio);

        $pagina = $controllore->gestisciRichiesta('GET', []);

        $this->assertSame('iniziale', $pagina['esito']);
        $this->assertSame('', $pagina['termine']);
        $this->assertSame([], $pagina['risultati']);
    }

    public function testCercaIscrittoConservandoIlTermine(): void
    {
        $utente = new Utente(1, 'Anna', 'Rossi', '2000-01-01', '3331234567', 'anna@example.test');
        $servizio = $this->createMock(RicercaIscrittoService::class);
        $servizio->expects($this->once())->method('cerca')
            ->with('Rossi')
            ->willReturn(['risultati' => [$utente], 'messaggio' => '']);
        $controllore = new RicercaIscrittoController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', ['termine' => '  Rossi  ']);

        $this->assertSame('ricercato', $pagina['esito']);
        $this->assertSame('Rossi', $pagina['termine']);
        $this->assertSame([$utente], $pagina['risultati']);
    }

    public function testMostraAvvisoQuandoNonTrovaIscritti(): void
    {
        $servizio = $this->createMock(RicercaIscrittoService::class);
        $servizio->expects($this->once())->method('cerca')
            ->with('Tessari')
            ->willReturn(['risultati' => [], 'messaggio' => 'Iscritto non trovato']);
        $controllore = new RicercaIscrittoController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', ['termine' => 'Tessari']);

        $this->assertSame('non_trovato', $pagina['esito']);
        $this->assertSame('Iscritto non trovato', $pagina['messaggio']);
        $this->assertSame([], $pagina['risultati']);
    }

    public function testRifiutaTermineMancanteOVuoto(): void
    {
        $servizio = $this->createMock(RicercaIscrittoService::class);
        $servizio->expects($this->never())->method('cerca');
        $controllore = new RicercaIscrittoController($servizio);

        $pagina = $controllore->gestisciRichiesta('POST', []);
        $this->assertSame('dati_non_validi', $pagina['esito']);

        $pagina = $controllore->gestisciRichiesta('POST', ['termine' => '   ']);
        $this->assertSame('dati_non_validi', $pagina['esito']);
    }
}
