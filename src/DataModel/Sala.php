<?php

namespace Ladythelemons\Fitslot\DataModel;

use InvalidArgumentException;

/** Rappresenta una sala con le sue caratteristiche principali. */
class Sala 
{
    private $id;
    private $nome;
    private $capienza;
    private $inizio_occupazione;
    private $fine_occupazione;

    /**
     * Crea una nuova istanza di Sala.
     *
     * @param int $id Identificativo della sala.
     * @param string $nome Nome della sala.
     * @param int $capienza Capienza della sala.
     * @param string $inizio_occupazione Orario di inizio occupazione della sala.
     * @param string $fine_occupazione Orario di fine occupazione della sala.
     * @throws InvalidArgumentException Se i parametri non sono validi.
     */
    public function __construct($id, $nome, $capienza, $inizio_occupazione, $fine_occupazione) {
        // Validazione della capienza
        if (!is_int($capienza) || $capienza <= 0) {
            throw new InvalidArgumentException("La capienza deve essere un intero positivo.");
        }
        $this->id = $id;
        $this->nome = $nome;
        $this->capienza = $capienza;
        $this->inizio_occupazione = $inizio_occupazione;
        $this->fine_occupazione = $fine_occupazione;
    }

    /** Restituisce l'identificativo della sala. */
    public function getId() {
        return $this->id;
    }

    /** Restituisce il nome della sala. */
    public function getNome() {
        return $this->nome;
    }

    /** Restituisce la capienza della sala. */
    public function getCapienza() {
        return $this->capienza;
    }

    /** Restituisce l'orario di inizio occupazione della sala. */
    public function getInizioOccupazione() {
        return $this->inizio_occupazione;
    }

    /** Restituisce l'orario di fine occupazione della sala. */
    public function getFineOccupazione() {
        return $this->fine_occupazione;
    }
}