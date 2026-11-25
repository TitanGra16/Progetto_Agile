<?php

declare (strict_types=1);

namespace Ladythelemons\Fitslot\Repository;

use Ladythelemons\Fitslot\Config\Database;
use Ladythelemons\Fitslot\DataModel\Sala;
use mysqli;

class SaleRepository
{
    private mysqli $connessione;

    /**
     * Crea il repository con una connessione esistente o ne apre una nuova.
     *
     * @param mysqli|null $connessione Connessione da riutilizzare, se disponibile.
     */
    public function __construct(?mysqli $connessione = null)
    {
        if ($connessione === null) {
            $connessione = Database::connetti();
        }
        $this->connessione = $connessione;
    }

    /**
     * Salva una sala nella tabella sale.
     *
     * @param Sala $sala Sala da inserire.
     * @return int Identificativo assegnato da MySQL.
     *
     * @throws \mysqli_sql_exception Se l'inserimento fallisce.
     * @throws \InvalidArgumentException Se esiste già una sala con lo stesso nome.
     */
    public function salva(Sala $sala): int
    {
        $nome = $sala->getNome();
        $capienza = $sala->getCapienza();
        $inizioOccupazione = $sala->getInizioOccupazione();
        $fineOccupazione = $sala->getFineOccupazione();

        $nome = $this->connessione->real_escape_string($nome);
        $capienza = (int) $capienza;
        $inizioOccupazione = $this->connessione->real_escape_string($inizioOccupazione);
        $fineOccupazione = $this->connessione->real_escape_string($fineOccupazione);

        if($this->esisteSalaConNome($nome)) {
            throw new \InvalidArgumentException("Esiste già una sala con il nome '$nome'.");
        }

        $this->connessione->query(
            'INSERT INTO sale (nome, capienza, inizio_occupazione, fine_occupazione)
             VALUES ("' . $nome. '", "' . $capienza . '", "' . $inizioOccupazione . '", "' . $fineOccupazione . '")'
        );
        return (int) $this->connessione->insert_id;
    }

    /**
     * Verifica se esiste una sala con lo stesso nome
     * 
     * @param string $nome Nome della sala da verificare.
     * @return bool True se esiste una sala con lo stesso nome, false altrimenti.
     * @throws \mysqli_sql_exception Se la query fallisce.
     */
    public function esisteSalaConNome(string $nome): bool
    {
        $nome = $this->connessione->real_escape_string($nome);
        $query = $this->connessione->query(
            'SELECT COUNT(*) AS count FROM sale WHERE nome = "' . $nome . '"'
        );
        $result = $query->fetch_assoc();
        // Restituisce true se il conteggio è maggiore di 0, altrimenti false
        return (bool) $result['count'];
    }

    /**
     * Recupera tutte le sale ordinate per nome.
     *
     * @return list<Sala>
     */
    public function trovaTutte(): array
    {
        $risultato = $this->connessione->query(
            'SELECT id, nome, capienza, inizio_occupazione, fine_occupazione
             FROM sale
             ORDER BY id ASC'
        );
        $sale = [];

        while ($riga = $risultato->fetch_assoc()) {
            $sale[] = new Sala(
                (int) $riga['id'],
                $riga['nome'],
                (int) $riga['capienza'],
                $riga['inizio_occupazione'],
                $riga['fine_occupazione']
            );
        }

        $risultato->free();

        return $sale;
    }
}