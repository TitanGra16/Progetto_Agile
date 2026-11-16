<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class ValidatoreSala
{
    /**
     * Controlla presenza, formato e lunghezza dei dati di una sala.
     *
     * @param array<string, mixed> $sala Dati da validare.
     * @return bool True quando tutti i dati rispettano le regole.
     */
    public function validaSala(array $sala): bool
    {
        $campiObbligatori = [
            'nome' => 100,
            'capienza' => 11,
            'inizio_occupazione' => 8,
            'fine_occupazione' => 8,
        ];
        foreach ($campiObbligatori as $campo => $lunghezzaMassima) {
            if (!isset($sala[$campo]) || !is_string($sala[$campo]) || trim($sala[$campo]) === '') {
                return false;
            }
            if (mb_strlen($sala[$campo]) > $lunghezzaMassima) {
                return false;
            }
        }

        if (!ctype_digit($sala['capienza']) || (int) $sala['capienza'] <= 0) {
            return false;
        }

        if (!$this->validaOrario($sala['inizio_occupazione']) || !$this->validaOrario($sala['fine_occupazione'])) {
            return false;
        }

        if ($sala['inizio_occupazione'] >= $sala['fine_occupazione']) {
            return false;
        }

        return true;
    }

    /**
     * Controlla se una stringa rappresenta un orario valido nel formato HH:MM:SS.
     *
     * @param string $orario Orario da validare.
     * @return bool True se l'orario è valido, false altrimenti.
     */
    private function validaOrario(string $orario): bool
    {
        $partiOrario = explode(':', $orario);
        if (count($partiOrario) !== 3) {
            return false;
        }
        [$ore, $minuti, $secondi] = $partiOrario;

        if (!ctype_digit($ore) || !ctype_digit($minuti) || !ctype_digit($secondi)) {
            return false;
        }

        $ore = (int) $ore;
        $minuti = (int) $minuti;
        $secondi = (int) $secondi;

        return ($ore >= 0 && $ore < 24) && ($minuti >= 0 && $minuti < 60) && ($secondi >= 0 && $secondi < 60);
    }
}