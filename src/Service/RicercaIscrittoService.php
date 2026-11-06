<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;

class RicercaIscrittoService
{
    private UtenteRepository $archivio;

    public function __construct(UtenteRepository $archivio)
    {
        $this->archivio = $archivio;
    }


    public function cerca(string $termine): array
    {
        $risultati = $this->archivio->cerca($termine);
        $messaggio = '';

        if ($risultati === []) {
            $messaggio = 'Iscritto non trovato';
        }

        return ['risultati' => $risultati, 'messaggio' => $messaggio];
    }
}