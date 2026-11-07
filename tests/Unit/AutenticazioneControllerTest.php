<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Controller\AutenticazioneController;
use Ladythelemons\Fitslot\Service\AutenticazioneService;
use PHPUnit\Framework\TestCase;

final class AutenticazioneControllerTest extends TestCase
{
    // TEST 1: Credenziali corrette
    public function testAccessoRiuscito(): void
    {
        $service = $this->createMock(AutenticazioneService::class);

        $service->expects($this->once())
            ->method('verificaCredenziali')
            ->with('test@example.test', 'Password123!')
            ->willReturn(5);

        $controller = new AutenticazioneController($service);

        $risultato = $controller->accedi(
            'test@example.test',
            'Password123!'
        );

        $this->assertSame(5, $risultato);
    }

    // TEST 2: Credenziali non valide
    public function testAccessoFallito(): void
    {
        $service = $this->createMock(AutenticazioneService::class);

        $service->expects($this->once())
            ->method('verificaCredenziali')
            ->with('test@example.test', 'PasswordSbagliata')
            ->willReturn(null);

        $controller = new AutenticazioneController($service);

        $risultato = $controller->accedi(
            'test@example.test',
            'PasswordSbagliata'
        );

        $this->assertNull($risultato);
    }
}
