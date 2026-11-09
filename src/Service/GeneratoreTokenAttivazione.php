<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class GeneratoreTokenAttivazione
{
    public function genera(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function creaHash(string $token): string
    {
        return hash('sha256', $token);
    }
}
