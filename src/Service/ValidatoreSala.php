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
            if (!array_key_exists($campo, $sala)) {
                return false;
            }

            if ($campo === 'capienza') {
                continue;
            }

            if (!is_string($sala[$campo]) || trim($sala[$campo]) === '') {
                return false;
            }
            if (mb_strlen($sala[$campo]) > $lunghezzaMassima) {
                return false;
            }
        }

        if  (!(is_int($sala['capienza']) 
            || (is_string($sala['capienza']) 
            && ctype_digit($sala['capienza'])))
            || (int) $sala['capienza'] <= 0
        ) {
            return false;
        }

        if (!$this->validaOrario($sala['inizio_occupazione']) 
            || !$this->validaOrario($sala['fine_occupazione'])) {
            return false;
        }

        if ($this->orarioInSecondi($sala['inizio_occupazione']) >= $this->orarioInSecondi($sala['fine_occupazione'])) {
            return false;
        }

        return true;
    }

    /**
     * Controlla se una stringa rappresenta un orario valido nel formato HH:MM oppure HH:MM:SS.
     *
     * @param string $orario Orario da validare.
     * @return bool True se l'orario è valido, false altrimenti.
     */
    private function validaOrario(string $orario): bool
    {
        $partiOrario = explode(':', $orario);
        if (count($partiOrario) !== 2 && count($partiOrario) !== 3) {
            return false;
        }

        if (!ctype_digit($partiOrario[0]) || !ctype_digit($partiOrario[1])) {
            return false;
        }

        $ore = (int) $partiOrario[0];
        $minuti = (int) $partiOrario[1];
        $secondi = 0;
        if (isset($partiOrario[2])) {
            if (!ctype_digit($partiOrario[2])) {
                return false;
            }
            $secondi = (int) $partiOrario[2];
        }

        return ($ore >= 0 && $ore < 24) && ($minuti >= 0 && $minuti < 60) && ($secondi >= 0 && $secondi < 60);
    }

    public function orarioInSecondi(string $orario): int
    {
        $parti = array_map('intval', explode(':', $orario));
        $secondi = ($parti[0] * 3600) + ($parti[1] * 60);
        if (isset($parti[2])) {
            $secondi += $parti[2];
        }
        return $secondi;
    }
}