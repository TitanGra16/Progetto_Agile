<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class TokenCsrf
{
    public static function genera(array &$sessione, string $chiave): string
    {
        if (!isset($sessione[$chiave]) || !is_string($sessione[$chiave])) {
            $sessione[$chiave] = bin2hex(random_bytes(32));
        }

        return $sessione[$chiave];
    }

    public static function valida(array $sessione, string $chiave, mixed $tokenRicevuto): bool
    {
        $tokenAtteso = $sessione[$chiave] ?? null;

        return is_string($tokenAtteso)
            && is_string($tokenRicevuto)
            && hash_equals($tokenAtteso, $tokenRicevuto);
    }
}
