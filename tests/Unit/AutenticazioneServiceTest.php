<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\AutenticazioneService;
use PHPUnit\Framework\TestCase;

final class AutenticazioneServiceTest extends TestCase
{
    public function testAccettaCredenzialiValide(): void
    {
        $hash = password_hash('FitSlotTest123!', PASSWORD_DEFAULT);
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerEmail')->willReturn([
            'id' => 5,
            'password_hash' => $hash,
            'stato' => 'ACTIVE',
        ]);

        self::assertSame(5, (new AutenticazioneService($repository))
            ->verificaCredenziali('test@example.test', 'FitSlotTest123!'));
    }

    public function testRifiutaPasswordErrataOAccountInattivo(): void
    {
        $hash = password_hash('FitSlotTest123!', PASSWORD_DEFAULT);
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerEmail')->willReturn([
            'id' => 5,
            'password_hash' => $hash,
            'stato' => 'PENDING',
        ]);

        self::assertNull((new AutenticazioneService($repository))
            ->verificaCredenziali('test@example.test', 'FitSlotTest123!'));
    }

    public function testRifiutaEmailInesistente(): void
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaPerEmail')->willReturn(null);

        self::assertNull((new AutenticazioneService($repository))
            ->verificaCredenziali('inesistente@example.test', 'FitSlotTest123!'));
    }
}
