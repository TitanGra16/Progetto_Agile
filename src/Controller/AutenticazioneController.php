<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Controller;

use Ladythelemons\Fitslot\Service\AutenticazioneService;

class AutenticazioneController
{
    private AutenticazioneService $service;

    public function __construct(?AutenticazioneService $service = null)
    {
        $this->service = $service ?? new AutenticazioneService();
    }

    public function accedi(string $email, string $password): ?int
    {
        return $this->service->verificaCredenziali($email, $password);
    }
}
