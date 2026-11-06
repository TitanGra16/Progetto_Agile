<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RegistrazioneIscrittoService;
use PHPUnit\Framework\TestCase;

final class RegistrazioneIscrittoServiceTest extends TestCase
{
    private function dati(): array
    {
        return [
            'nome' => 'Anna',
            'cognome' => "D'Angelo",
            'data_nascita' => '2000-02-29',
            'recapito' => 'Recapito di prova',
            'email' => 'iscritto@example.test',
            'password' => 'password_di_prova',
        ];
    }

    public function testRegistraValido(): void
    {
        // createMock non esegue il costruttore del Repository e non apre MySQL.
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->once())
            ->method('esistePotenzialeDuplicato')
            ->with('Anna', "D'Angelo", '2000-02-29')
            ->willReturn(false);

        // Questa funzione controlla l'Utente ricevuto al posto del vero salvataggio.
        $archivioUtenti->expects($this->once())
            ->method('salva')
            ->willReturnCallback(function (Utente $utente): int {
                $this->assertSame('Anna', $utente->getNome());
                $this->assertSame("D'Angelo", $utente->getCognome());
                $this->assertSame('2000-02-29', $utente->getDataNascita());
                $this->assertSame('Recapito di prova', $utente->getRecapito());
                $this->assertSame('iscritto@example.test', $utente->getEmail());
                $this->assertNotSame('password_di_prova', $utente->getPassword());
                $this->assertTrue(password_verify('password_di_prova', $utente->getPassword()));
                return 73;
            });

        $servizio = new RegistrazioneIscrittoService($archivioUtenti);
        $risultato = $servizio->registra($this->dati());

        $this->assertSame('registrato', $risultato['esito']);
        $this->assertSame(73, $risultato['codice_iscritto']);
        $this->assertNotEmpty($risultato['messaggio']);
    }

    public function testRifiutaNonValido(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->never())->method('esistePotenzialeDuplicato');
        $archivioUtenti->expects($this->never())->method('salva');

        $servizio = new RegistrazioneIscrittoService($archivioUtenti);
        $dati = $this->dati();
        $dati['email'] = 'email-non-valida';
        $risultato = $servizio->registra($dati);

        $this->assertSame('dati_non_validi', $risultato['esito']);
        $this->assertNull($risultato['codice_iscritto']);
    }

    public function testSegnalaDuplicato(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->once())->method('esistePotenzialeDuplicato')->willReturn(true);
        $archivioUtenti->expects($this->never())->method('salva');

        $servizio = new RegistrazioneIscrittoService($archivioUtenti);
        $risultato = $servizio->registra($this->dati());

        $this->assertSame('possibile_duplicato', $risultato['esito']);
        $this->assertStringContainsString('Possibile duplicato', $risultato['messaggio']);
        $this->assertNull($risultato['codice_iscritto']);
    }

    public function testConfermaDuplicato(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->exactly(2))->method('esistePotenzialeDuplicato')->willReturn(true);
        $archivioUtenti->expects($this->once())->method('salva')->willReturn(74);
        $servizio = new RegistrazioneIscrittoService($archivioUtenti);
        $dati = $this->dati();

        $avviso = $servizio->registra($dati);
        $this->assertSame('possibile_duplicato', $avviso['esito']);
        $this->assertNull($avviso['codice_iscritto']);

        $confermato = $servizio->registra($dati, true);
        $this->assertSame('registrato', $confermato['esito']);
        $this->assertSame(74, $confermato['codice_iscritto']);
        $this->assertStringContainsString('conferma esplicita', $confermato['messaggio']);
    }

    public function testConfermaNonValida(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->never())->method('esistePotenzialeDuplicato');
        $archivioUtenti->expects($this->never())->method('salva');
        $servizio = new RegistrazioneIscrittoService($archivioUtenti);

        $risultato = $servizio->registra([], true);

        $this->assertSame('dati_non_validi', $risultato['esito']);
        $this->assertNull($risultato['codice_iscritto']);
    }

    public function testErroreRicerca(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->once())->method('esistePotenzialeDuplicato')
            ->willThrowException(new RuntimeException('Ricerca non riuscita'));
        $archivioUtenti->expects($this->never())->method('salva');
        $servizio = new RegistrazioneIscrittoService($archivioUtenti);

        $this->expectException(RuntimeException::class);
        $servizio->registra($this->dati());
    }

    public function testErroreSalvataggio(): void
    {
        $archivioUtenti = $this->createMock(UtenteRepository::class);
        $archivioUtenti->expects($this->once())->method('esistePotenzialeDuplicato')->willReturn(false);
        $archivioUtenti->expects($this->once())->method('salva')->willThrowException(new RuntimeException('Salvataggio non riuscito'));
        $servizio = new RegistrazioneIscrittoService($archivioUtenti);

        $this->expectException(RuntimeException::class);
        $servizio->registra($this->creaDatiDiProva());
    }
}
