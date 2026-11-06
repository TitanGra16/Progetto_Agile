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
            'email' => 'anna@example.test',
            'recapito' => 'Recapito di prova',
        ];
    }

    public function testRegistraDatiValidi(): void
    {
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('esisteEmail')->willReturn(false);
        $archivio->expects($this->once())->method('esistePotenzialeDuplicato')->willReturn(false);
        $archivio->expects($this->once())->method('salva')
            ->with(new Utente(null, 'Anna', "D'Angelo", '2000-02-29', 'Recapito di prova', 'iscritto@example.test'))
            ->willReturn(73);

        $dati = $this->dati();
        $dati['email'] = '  iscritto@example.test  ';
        $risultato = (new RegistrazioneIscrittoService($archivio))->registra($dati);

        $this->assertSame('registrato', $risultato['esito']);
        $this->assertSame(73, $risultato['codice_iscritto']);
    }

    public function testRifiutaDatiNonValidi(): void
    {
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->never())->method('esistePotenzialeDuplicato');
        $archivio->expects($this->never())->method('salva');
        $servizio = new RegistrazioneIscrittoService($archivio);

        foreach ([['nome' => '   '], ['email' => 'email-non-valida'], ['data_nascita' => '2000-02-30']] as $modifica) {
            $dati = $this->dati();
            $dati = array_merge($dati, $modifica);
            $risultato = $servizio->registra($dati);
            $this->assertSame('dati_non_validi', $risultato['esito']);
        }
    }

    public function testGestisceDuplicatoConConferma(): void
    {
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->exactly(2))->method('esisteEmail')->willReturn(false);
        $archivio->expects($this->exactly(2))->method('esistePotenzialeDuplicato')->willReturn(true);
        $archivio->expects($this->once())->method('salva')->willReturn(74);
        $servizio = new RegistrazioneIscrittoService($archivio);
        $dati = $this->dati();

        $avviso = $servizio->registra($dati);
        $this->assertSame('possibile_duplicato', $avviso['esito']);

        $risultato = $servizio->registra($dati, true);
        $this->assertSame('registrato', $risultato['esito']);
        $this->assertSame(74, $risultato['codice_iscritto']);
    }

    public function testBloccaEmailGiaUsataAncheConConferma(): void
    {
        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('esisteEmail')->willReturn(true);
        $archivio->expects($this->never())->method('esistePotenzialeDuplicato');
        $archivio->expects($this->never())->method('salva');

        $risultato = (new RegistrazioneIscrittoService($archivio))
            ->registra($this->dati(), true);

        $this->assertSame('email_duplicata', $risultato['esito']);
        $this->assertSame('Email già associata a un iscritto', $risultato['messaggio']);
        $this->assertNull($risultato['codice_iscritto']);

        $archivio = $this->createMock(UtenteRepository::class);
        $archivio->expects($this->once())->method('esisteEmail')->willReturn(false);
        $archivio->expects($this->once())->method('esistePotenzialeDuplicato')->willReturn(false);
        $archivio->expects($this->once())->method('salva')
            ->willThrowException(new mysqli_sql_exception('Duplicate entry', 1062));

        $risultato = (new RegistrazioneIscrittoService($archivio))->registra($this->dati());
        $this->assertSame('email_duplicata', $risultato['esito']);
    }
}
