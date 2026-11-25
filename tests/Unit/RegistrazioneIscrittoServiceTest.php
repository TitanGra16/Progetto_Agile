<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RegistrazioneIscrittoService;
use PHPUnit\Framework\TestCase;

final class RegistrazioneIscrittoServiceTest extends TestCase
{
    private function dati(): array
    {
        return [
            'nome' => 'Anna',
            'cognome' => 'Rossi',
            'data_nascita' => '2000-02-29',
            'email' => 'anna@example.test',
            'recapito' => '3331234567',
        ];
    }

    public function testRegistraIscrittoValido(): void
    {
        $repository = $this->createMock(UtenteRepository::class);
        $repository->method('esisteEmail')->willReturn(false);
        $repository->method('esistePotenzialeDuplicato')->willReturn(false);
        $repository->expects($this->once())->method('salva')->willReturn(73);

        $risultato = (new RegistrazioneIscrittoService($repository))->registra($this->dati());
        self::assertSame('registrato', $risultato['esito']);
    }

    public function testRifiutaDuplicatoNonConfermato(): void
    {
        $repository = $this->createMock(UtenteRepository::class);
        $repository->method('esisteEmail')->willReturn(false);
        $repository->method('esistePotenzialeDuplicato')->willReturn(true);
        $repository->expects($this->never())->method('salva');

        $risultato = (new RegistrazioneIscrittoService($repository))->registra($this->dati());
        self::assertSame('possibile_duplicato', $risultato['esito']);
    }

    public function testRifiutaEmailGiaUtilizzata(): void
    {
        $repository = $this->createMock(UtenteRepository::class);
        $repository->method('esisteEmail')->willReturn(true);
        $repository->expects($this->never())->method('salva');

        $risultato = (new RegistrazioneIscrittoService($repository))->registra($this->dati());
        self::assertSame('email_duplicata', $risultato['esito']);
    }
}
