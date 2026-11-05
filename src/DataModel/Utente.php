<?php
namespace Ladythelemons\Fitslot\DataModel;

class Utente {
    private $id;
    private $nome;
    private $cognome;
    private $data_nascita;
    private $recapito;
    private $email;
    private $password;

    public function __construct($id, $nome, $cognome, $data_nascita, $recapito, $email, $password) {
        $this->id = $id;
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->data_nascita = $data_nascita;
        $this->recapito = $recapito;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function getDataNascita() {
        return $this->data_nascita;
    }

    public function getCognome() {
        return $this->cognome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRecapito() {
        return $this->recapito;
    }

    public function getPassword() {
        return $this->password;
    }
}