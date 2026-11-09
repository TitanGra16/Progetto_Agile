<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\GeneratoreTokenAttivazione;
use PHPUnit\Framework\TestCase;

final class GeneratoreTokenAttivazioneTest extends TestCase
{
    public function testGeneraTokenDiLunghezzaCorretta(): void
    {
        $generatore = new GeneratoreTokenAttivazione();

        $token = $generatore->genera();

        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token);
    }

    public function testGeneraTokenDiversi(): void
    {
        $generatore = new GeneratoreTokenAttivazione();

        $primoToken = $generatore->genera();
        $secondoToken = $generatore->genera();

        $this->assertNotSame($primoToken, $secondoToken);
    }

    public function testCreaHashRipetibile(): void
    {
        $generatore = new GeneratoreTokenAttivazione();

        $hash = $generatore->creaHash('token-di-prova');

        $this->assertSame(
            hash('sha256', 'token-di-prova'),
            $hash
        );
        $this->assertSame(64, strlen($hash));
    }
}
