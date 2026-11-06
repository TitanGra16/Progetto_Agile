<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Config;

use mysqli;
use InvalidArgumentException;

final class Database
{
    /**
     * Legge e valida la configurazione del database senza aprire una connessione.
     *
     * @param array<string, mixed>|null $ambiente Variabili di configurazione da usare.
     * @return array{indirizzo: string, utente: string, password: string, nome_database: string, porta: int}
     *
     * @throws InvalidArgumentException Se una variabile e mancante o non valida.
     */
    public static function leggiConfigurazione(?array $ambiente = null): array
    {
        if ($ambiente === null) {
            $ambiente = $_ENV;
            // Le variabili gia fornite dal terminale possono essere solo in $_SERVER.
            foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $variabile) {
                if (!isset($ambiente[$variabile]) && isset($_SERVER[$variabile])) {
                    $ambiente[$variabile] = $_SERVER[$variabile];
                }
            }
        }
        foreach (['DB_HOST', 'DB_USER', 'DB_NAME', 'DB_PORT'] as $variabile) {
            if (!isset($ambiente[$variabile]) || !is_string($ambiente[$variabile]) || trim($ambiente[$variabile]) === '') {
                throw new InvalidArgumentException("Configurazione mancante o vuota: $variabile");
            }
        }

        // Per MySQL locale la password puo essere vuota o non specificata.
        if (!isset($ambiente['DB_PASSWORD'])) {
            $ambiente['DB_PASSWORD'] = '';
        }
        if (!is_string($ambiente['DB_PASSWORD'])) {
            throw new InvalidArgumentException('DB_PASSWORD deve essere una stringa');
        }
        if ($ambiente['DB_HOST'] !== '127.0.0.1' && $ambiente['DB_HOST'] !== 'localhost') {
            throw new InvalidArgumentException('DB_HOST deve essere 127.0.0.1 oppure localhost');
        }
        if ($ambiente['DB_NAME'] !== 'fitslot') {
            throw new InvalidArgumentException('DB_NAME deve essere esattamente fitslot');
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

    /**
     * Apre una connessione MySQL usando la configurazione dell'ambiente.
     *
     * @return mysqli Connessione configurata con codifica UTF-8.
     *
     * @throws InvalidArgumentException Se la configurazione non e valida.
     * @throws mysqli_sql_exception Se MySQL rifiuta la connessione.
     */
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
