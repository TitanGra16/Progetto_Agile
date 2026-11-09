<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RicercaIscrittoService;
use PHPUnit\Framework\TestCase;

final class RicercaIscrittoServiceTest extends TestCase
{
    public function testTrovaDueIscrittiCognome(): void
    {
        $primo = new Utente(1, 'Giuseppe', 'Maglione', '1990-01-01', '3331234567', 'lemons@example.com');
        $secondo = new Utente(2, 'Francesco', 'Maglione', '1988-05-10', '3456789291', 'telavivapproved@example.com');
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('cerca')
            ->with('Maglione')
            ->willReturn([$primo, $secondo]);

        $risultati = (new RicercaIscrittoService($archivio))->cerca('Maglione');

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

        $risultato = (new RicercaIscrittoService($archivio))->cerca('Tessari');

        $this->assertSame('Iscritto non trovato', $risultato['messaggio']);
        $this->assertSame([], $risultato['risultati']);
    }

    public function testTrovaIscrittoPerID(): void
    {
        $primo = new Utente(1, 'Giuseppe', 'Maglione', '1990-01-01', '3331234567', 'lemons@example.com');
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('cerca')->with('1')->willReturn([$primo]);

        $risultati = (new RicercaIscrittoService($archivio))->cerca('1');

        $this->assertCount(1, $risultati['risultati']);
        $this->assertSame([$primo], $risultati['risultati']);
    }

    public function testTrovaIscrittoPerNomeCognome(): void
    {
        $primo = new Utente(1, 'Giuseppe', 'Maglione', '1990-01-01', '3331234567', 'lemons@example.com');
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('cerca')
            ->with('giuseppe maglione')->willReturn([$primo]);

        $risultati = (new RicercaIscrittoService($archivio))->cerca('giuseppe maglione');

        $this->assertCount(1, $risultati['risultati']);
        $this->assertSame([$primo], $risultati['risultati']);
    }
}
