<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RicercaIscrittoService;
use PHPUnit\Framework\TestCase;

final class RicercaIscrittoServiceTest extends TestCase
{
    public function testTrovaIscrittoEsistente(): void
    {
        $primo = new Utente(1, 'Giuseppe', 'Maglione', '1990-01-01', '3331234567', 'lemons@example.com');
        $secondo = new Utente(2, 'Francesco', 'Maglione', '1988-05-10', '3456789291', 'telavivapproved@example.com');
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('cerca')
            ->with('Maglione')
            ->willReturn([$primo, $secondo]);

        $servizio = new RicercaIscrittoService($archivio);
        $risultati = $servizio->cerca('Maglione');

        $this->assertCount(2, $risultati['risultati']);
        $this->assertSame([$primo, $secondo], $risultati['risultati']);
        $this->assertSame('', $risultati['messaggio']);
    }

    public function testMostraAvvisoQuandoIscrittoNonTrovato(): void
    {
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('cerca')
            ->with('Tessari')
            ->willReturn([]);

        $servizio = new RicercaIscrittoService($archivio);
        $risultato = $servizio->cerca('Tessari');

        $this->assertSame('Iscritto non trovato', $risultato['messaggio']);
        $this->assertSame([], $risultato['risultati']);
    }
}
