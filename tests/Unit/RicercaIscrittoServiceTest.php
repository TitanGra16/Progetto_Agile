<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RicercaIscrittoService;
use PHPUnit\Framework\TestCase;

final class RicercaIscrittoServiceTest extends TestCase
{
    public function testTrovaRisultatiPerCognome(): void
    {
        $repository = $this->createMock(UtenteRepository::class);
        $repository->expects($this->once())->method('cerca')->with('Rossi')->willReturn([]);

        $risultato = (new RicercaIscrittoService($repository))->cerca('Rossi');
        self::assertSame([], $risultato['risultati']);
    }

    public function testSegnalaIscrittoNonTrovato(): void
    {
        $repository = $this->createStub(UtenteRepository::class);
        $repository->method('cerca')->willReturn([]);

        $risultato = (new RicercaIscrittoService($repository))->cerca('NomeInesistente');
        self::assertSame('Iscritto non trovato', $risultato['messaggio']);
    }
}
