<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class ValidatoreIscritto
{
    /**
     * Controlla presenza, formato e lunghezza dei dati di un iscritto.
     *
     * @param array<string, mixed> $iscritto Dati da validare.
     * @return bool True quando tutti i dati rispettano le regole.
     */
    public function validaIscritto(array $iscritto): bool
    {
        $campiObbligatori = [
            'nome' => 100,
            'cognome' => 100,
            'data_nascita' => 10,
            'email' => 255,
            'recapito' => 190,
        ];
        foreach ($campiObbligatori as $campo => $lunghezzaMassima) {
            if (!isset($iscritto[$campo]) || !is_string($iscritto[$campo]) || trim($iscritto[$campo]) === '') {
                return false;
            }
            if (mb_strlen($iscritto[$campo]) > $lunghezzaMassima) {
                return false;
            }
        }

        if (filter_var(trim($iscritto['email']), FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        $partiData = explode('-', $iscritto['data_nascita']);
        if (count($partiData) !== 3) {
            return false;
        }
        $anno = $partiData[0];
        $mese = $partiData[1];
        $giorno = $partiData[2];

        if (strlen($anno) !== 4 || strlen($mese) !== 2 || strlen($giorno) !== 2) {
            return false;
        }
        if (!ctype_digit($anno) || !ctype_digit($mese) || !ctype_digit($giorno)) {
            return false;
        }
        if (!checkdate((int) $mese, (int) $giorno, (int) $anno)) {
            return false;
        }
        if ($iscritto['data_nascita'] > date('Y-m-d')) {
            return false;
        }

        return true;
    }
}
