<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\RegistrazioneIscrittoController;
use Ladythelemons\Fitslot\Service\RegistrazioneIscrittoService;
use PHPUnit\Framework\TestCase;

final class RegistrazioneIscrittoControllerTest extends TestCase
{
    private function dati(): array
    {
        return [
            'nome' => 'Anna',
            'cognome' => "D'Angelo",
            'data_nascita' => '2000-02-29',
            'email' => 'anna@example.test',
            'recapito' => '3331234567',
        ];
    }

    private function richiesta(string $azione = 'registra'): array
    {
        return $this->dati() + ['token' => 'token-di-prova', 'azione' => $azione];
    }

    public function testFormERegistrazione(): void
    {
        $servizio = $this->createMock(RegistrazioneIscrittoService::class);
        $servizio->expects($this->once())->method('registra')
            ->with($this->dati(), false)
            ->willReturn(['esito' => 'registrato', 'messaggio' => 'Registrato', 'codice_iscritto' => 42]);
        $controllore = new RegistrazioneIscrittoController($servizio);
        $sessione = [];

        $pagina = $controllore->gestisciRichiesta('GET', [], $sessione);
        $this->assertSame('iniziale', $pagina['esito']);
        $this->assertNotEmpty($pagina['token']);

        $sessione['token_registrazione'] = 'token-di-prova';
        $pagina = $controllore->gestisciRichiesta('POST', $this->richiesta(), $sessione);
        $this->assertSame('registrato', $pagina['esito']);
        $this->assertSame(42, $pagina['codice_iscritto']);
        $this->assertSame('', $pagina['dati']['nome']);
    }

    public function testDuplicatoEConferma(): void
    {
        $servizio = $this->createMock(RegistrazioneIscrittoService::class);
        $servizio->expects($this->exactly(2))->method('registra')
            ->willReturnOnConsecutiveCalls(
                ['esito' => 'possibile_duplicato', 'messaggio' => 'Possibile duplicato', 'codice_iscritto' => null],
                ['esito' => 'registrato', 'messaggio' => 'Registrato', 'codice_iscritto' => 43]
            );
        $controllore = new RegistrazioneIscrittoController($servizio);
        $sessione = ['token_registrazione' => 'token-di-prova'];

        $pagina = $controllore->gestisciRichiesta('POST', $this->richiesta(), $sessione);
        $this->assertTrue($pagina['richiede_conferma']);
        $this->assertSame($this->dati(), $sessione['iscritto_da_confermare']['dati']);

        $pagina = $controllore->gestisciRichiesta('POST', $this->richiesta('conferma'), $sessione);
        $this->assertSame('registrato', $pagina['esito']);
        $this->assertSame(43, $pagina['codice_iscritto']);
        $this->assertArrayNotHasKey('iscritto_da_confermare', $sessione);
    }

    public function testRifiutaRichiesteNonValide(): void
    {
        $servizio = $this->createMock(RegistrazioneIscrittoService::class);
        $servizio->expects($this->never())->method('registra');
        $controllore = new RegistrazioneIscrittoController($servizio);

        $sessione = ['token_registrazione' => 'token-diverso'];
        $pagina = $controllore->gestisciRichiesta('POST', $this->richiesta(), $sessione);
        $this->assertSame('errore', $pagina['esito']);

        $sessione = [
            'token_registrazione' => 'token-di-prova',
            'iscritto_da_confermare' => ['dati' => $this->dati(), 'messaggio' => 'Possibile duplicato'],
        ];
        $richiesta = $this->richiesta('conferma');
        $richiesta['nome'] = 'Nome diverso';
        $pagina = $controllore->gestisciRichiesta('POST', $richiesta, $sessione);
        $this->assertSame('errore', $pagina['esito']);
        $this->assertArrayNotHasKey('iscritto_da_confermare', $sessione);
    }
}
