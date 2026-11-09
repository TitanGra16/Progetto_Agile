<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Controller;

use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RicercaIscrittoService;
use Throwable;

/** Legge il form e prepara i risultati della ricerca, senza eseguire query SQL. */
class RicercaIscrittoController
{
    private ?RicercaIscrittoService $servizio;

    public function __construct(?RicercaIscrittoService $servizio = null)
    {
        $this->servizio = $servizio;
    }

    public function gestisciRichiesta(string $metodo, array $richiesta): array
    {
        $pagina = [
            'esito' => 'iniziale',
            'messaggio' => '',
            'termine' => '',
            'risultati' => [],
        ];

        if ($metodo !== 'POST') {
            return $pagina;
        }

        if (!isset($richiesta['termine']) || !is_string($richiesta['termine'])) {
            $pagina['esito'] = 'dati_non_validi';
            $pagina['messaggio'] = 'Il termine di ricerca deve contenere testo.';
            return $pagina;
        }

        $pagina['termine'] = trim($richiesta['termine']);
        if ($pagina['termine'] === '') {
            $pagina['esito'] = 'dati_non_validi';
            $pagina['messaggio'] = 'Inserisci un nome, un cognome o un codice iscritto.';
            return $pagina;
        }

        try {
            if ($this->servizio === null) {
                $this->servizio = new RicercaIscrittoService(new UtenteRepository());
            }
            $risultato = $this->servizio->cerca($pagina['termine']);
        } catch (Throwable $errore) {
            $pagina['esito'] = 'errore';
            $pagina['messaggio'] = 'Non è stato possibile completare la ricerca. Riprova più tardi o avvisa il responsabile.';
            return $pagina;
        }

        $pagina['risultati'] = $risultato['risultati'];
        $pagina['messaggio'] = $risultato['messaggio'];
        $pagina['esito'] = $pagina['risultati'] === [] ? 'non_trovato' : 'ricercato';

        return $pagina;
    }
}