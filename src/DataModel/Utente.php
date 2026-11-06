<?php
namespace Ladythelemons\Fitslot\DataModel;

/**
 * Rappresenta i dati anagrafici e di accesso di un iscritto.
 */
class Utente {
    private $id;
    private $nome;
    private $cognome;
    private $data_nascita;
    private $recapito;
    private $email;
    private $password;

    /**
     * Inizializza un utente.
     */
    public function __construct($id, $nome, $cognome, $data_nascita, $recapito, $email, $password) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->data_nascita = $data_nascita;
        $this->recapito = $recapito;
        $this->email = $email;
        $this->password = $password;
    }

    /** Restituisce l'identificativo dell'utente. */
    public function getId() {
        return $this->id;
    }

    /** Restituisce il nome dell'utente. */
    public function getNome() {
        return $this->nome;
    }

    /** Restituisce la data di nascita dell'utente. */
    public function getDataNascita() {
        return $this->data_nascita;
    }

    /** Restituisce il cognome dell'utente. */
    public function getCognome() {
        return $this->cognome;
    }

    /** Restituisce l'indirizzo email dell'utente. */
    public function getEmail() {
        return $this->email;
    }

    /** Restituisce il recapito dell'utente. */
    public function getRecapito() {
        return $this->recapito;
    }

    /** Restituisce la password o il suo hash, secondo il momento del flusso. */
    public function getPassword() {
        return $this->password;
    }
}