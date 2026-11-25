<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Service\AutorizzazioneService;
use Ladythelemons\Fitslot\Service\SessioneService;
use PHPUnit\Framework\TestCase;

final class AutorizzazioneServiceTest extends TestCase
{
    private function servizioConRuoli(array $ruoli): AutorizzazioneService
    {
        $repository = $this->createStub(AccountRepository::class);
        $repository->method('trovaRuoli')->willReturn($ruoli);
        $sessione = $this->createStub(SessioneService::class);
        $sessione->method('getIdUtente')->willReturn(7);

        return new AutorizzazioneService($repository, $sessione);
    }

    public function testGestoreAccedeSoloAllaPropriaArea(): void
    {
        $servizio = $this->servizioConRuoli(['GESTORE']);

        self::assertTrue($servizio->haAccessoA(['GESTORE']));
        self::assertFalse($servizio->haAccessoA(['RECEPTION']));
        self::assertFalse($servizio->haAccessoA(['ISTRUTTORE']));
    }

    public function testUtenteMultiRuoloAccedeAlleAreeAssegnate(): void
    {
        $servizio = $this->servizioConRuoli(['ISCRITTO', 'ISTRUTTORE']);

        self::assertTrue($servizio->haAccessoA(['ISCRITTO']));
        self::assertTrue($servizio->haAccessoA(['ISTRUTTORE']));
        self::assertFalse($servizio->haAccessoA(['GESTORE']));
    }

    public function testUtenteSenzaRuoliNonAccede(): void
    {
        $servizio = $this->servizioConRuoli([]);

        self::assertFalse($servizio->haAccessoA(['ISCRITTO', 'RECEPTION', 'ISTRUTTORE', 'GESTORE']));
    }
}
