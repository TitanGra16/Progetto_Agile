<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class ValidatoreIscritto
{
    public function validaIscritto(array $iscritto): bool
    {
        $campiObbligatori = ['nome', 'cognome', 'data_nascita', 'recapito', 'email', 'password'];
        foreach ($campiObbligatori as $campo) {
            if (!isset($iscritto[$campo]) || !is_string($iscritto[$campo]) || trim($iscritto[$campo]) === '') {
                return false;
            }
        }

        // Le lunghezze devono rispettare le colonne della tabella utenti.
        $lunghezzeMassime = ['nome' => 100, 'cognome' => 100, 'recapito' => 190, 'email' => 255];
        foreach ($lunghezzeMassime as $campo => $lunghezzaMassima) {
            if (mb_strlen($iscritto[$campo]) > $lunghezzaMassima) {
                return false;
            }
        }

        if (filter_var($iscritto['email'], FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        // MySQL usa il formato anno-mese-giorno, per esempio 2000-02-29.
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
