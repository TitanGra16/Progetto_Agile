<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Config;

use mysqli;
use InvalidArgumentException;

final class Database
{
    // Legge e controlla i dati senza collegarsi a MySQL.
    public static function leggiConfigurazione(?array $ambiente = null): array
    {
        if ($ambiente === null) {
            $ambiente = $_ENV;
        }
        foreach (['DB_HOST', 'DB_USER', 'DB_NAME', 'DB_PORT'] as $variabile) {
            if (!isset($ambiente[$variabile]) || !is_string($ambiente[$variabile]) || trim($ambiente[$variabile]) === '') {
                throw new InvalidArgumentException("Configurazione mancante o vuota: $variabile");
            }
        }

        // La chiave resta obbligatoria anche quando la password e vuota.
        if (!isset($ambiente['DB_PASSWORD']) || !is_string($ambiente['DB_PASSWORD'])) {
            throw new InvalidArgumentException('Configurazione mancante o non valida: DB_PASSWORD');
        }
        $nomeDatabase = $ambiente['DB_NAME'];
        $lettere = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $caratteriConsentiti = $lettere . '0123456789';

        // Il nome deve iniziare con una lettera o un trattino basso.
        if (strlen($nomeDatabase) > 64 || !str_contains($lettere, $nomeDatabase[0])) {
            throw new InvalidArgumentException('DB_NAME non valido');
        }

        // Gli altri caratteri possono essere anche numeri.
        for ($indice = 0; $indice < strlen($nomeDatabase); $indice++) {
            if (!str_contains($caratteriConsentiti, $nomeDatabase[$indice])) {
                throw new InvalidArgumentException('DB_NAME contiene caratteri non consentiti');
            }
        }
        if (!ctype_digit($ambiente['DB_PORT'])) {
            throw new InvalidArgumentException('DB_PORT deve contenere solo numeri');
        }
        $porta = (int) $ambiente['DB_PORT'];
        if ($porta < 1 || $porta > 65535) {
            throw new InvalidArgumentException('DB_PORT non valida');
        }

        return [
            'indirizzo' => $ambiente['DB_HOST'],
            'utente' => $ambiente['DB_USER'],
            'password' => $ambiente['DB_PASSWORD'],
            'nome_database' => $ambiente['DB_NAME'],
            'porta' => $porta,
        ];
    }

    public static function connetti(): mysqli
    {
        $configurazione = Database::leggiConfigurazione();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $connessione = new mysqli(
            $configurazione['indirizzo'],
            $configurazione['utente'],
            $configurazione['password'],
            $configurazione['nome_database'],
            $configurazione['porta']
        );

        $connessione->set_charset('utf8mb4');

        return $connessione;
    }
}
