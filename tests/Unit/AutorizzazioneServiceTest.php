<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;
use PHPUnit\Framework\TestCase;

final class AutorizzazioneServiceTest extends TestCase
{
    public function testRestituisceIruoliDellUtenteAutenticato(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->expects($this->once())
            ->method('trovaRuoli')
            ->with(7)
            ->willReturn(['ISCRITTO', 'ISTRUTTORE']);

        $sessione = $this->createStub(SessioneService::class);
        $sessione->method('getIdUtente')->willReturn(7);

        $servizio = new AutorizzazioneService($accountRepository, $sessione);

        $this->assertSame(['ISCRITTO', 'ISTRUTTORE'], $servizio->getRuoliUtente());
    }

    public function testUtenteNonAutenticatoNonHaRuoli(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $accountRepository->expects($this->never())->method('trovaRuoli');

        $sessione = $this->createStub(SessioneService::class);
        $sessione->method('getIdUtente')->willReturn(null);

        $servizio = new AutorizzazioneService($accountRepository, $sessione);

        $this->assertSame([], $servizio->getRuoliUtente());
    }

    public function testControllaLaPresenzaDiUnRuolo(): void
    {
        $accountRepository = $this->createStub(AccountRepository::class);
        $accountRepository->method('trovaRuoli')
            ->willReturn(['ISCRITTO', 'ISTRUTTORE']);

        $sessione = $this->createStub(SessioneService::class);
        $sessione->method('getIdUtente')->willReturn(7);

        $servizio = new AutorizzazioneService($accountRepository, $sessione);

        $this->assertTrue($servizio->haRuolo('ISTRUTTORE'));
        $this->assertFalse($servizio->haRuolo('GESTORE'));
    }
}
