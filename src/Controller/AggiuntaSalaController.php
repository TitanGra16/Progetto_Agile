<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Controller;

use Ladythelemons\Fitslot\Repository\SaleRepository;
use Ladythelemons\Fitslot\Service\AggiuntaSalaService;
use Ladythelemons\Fitslot\Service\ValidatoreSala;
use Throwable;

/** Legge il form di aggiunta sala e prepara i dati della pagina. */
class AggiuntaSalaController
{
    private ?AggiuntaSalaService $servizio;
    private ValidatoreSala $validatore;

    public function __construct(?AggiuntaSalaService $servizio = null)
    {
        $this->servizio = $servizio;
        $this->validatore = new ValidatoreSala();
    }

    public function gestisciRichiesta(string $metodo, array $richiesta): array
    {
        $datiVuoti = [
            'nome' => '',
            'capienza' => '',
            'inizio_occupazione' => '',
            'fine_occupazione' => '',
        ];
        $pagina = [
            'esito' => 'iniziale',
            'messaggio' => '',
            'codice_sala' => null,
            'dati' => $datiVuoti,
        ];

        if ($metodo !== 'POST') {
            return $pagina;
        }

        foreach (array_keys($datiVuoti) as $campo) {
            if (!array_key_exists($campo, $richiesta)) {
                $pagina['esito'] = 'dati_non_validi';
                $pagina['messaggio'] = 'I dati della sala sono mancanti o non validi.';
                return $pagina;
            }

            $valore = $richiesta[$campo];
            if ($campo === 'capienza') {
                if (!is_int($valore) && (!is_string($valore) || trim($valore) === '')) {
                    $pagina['esito'] = 'dati_non_validi';
                    $pagina['messaggio'] = 'I dati della sala sono mancanti o non validi.';
                    return $pagina;
                }
                $pagina['dati'][$campo] = is_string($valore) ? (int) trim($valore) : $valore;
                continue;
            }

            if (!is_string($valore) || trim($valore) === '') {
                $pagina['esito'] = 'dati_non_validi';
                $pagina['messaggio'] = 'I dati della sala sono mancanti o non validi.';
                return $pagina;
            }
            $pagina['dati'][$campo] = trim($valore);
        }

        $inizio = $pagina['dati']['inizio_occupazione'];
        $fine = $pagina['dati']['fine_occupazione'];
        if (
            $this->validatore->orarioInSecondi($inizio)
            >= $this->validatore->orarioInSecondi($fine)
        ) {
            $pagina['esito'] = 'dati_non_validi';
            $pagina['messaggio'] = 'L’orario di fine occupazione deve essere successivo a quello di inizio.';
            return $pagina;
        }

        try {
            if ($this->servizio === null) {
                $this->servizio = new AggiuntaSalaService(new SaleRepository());
            }
            $risultato = $this->servizio->aggiunge($pagina['dati']);
        } catch (Throwable $errore) {
            $pagina['esito'] = 'errore';
            $pagina['messaggio'] = 'Non è stato possibile aggiungere la sala. Riprova più tardi o avvisa il responsabile.';
            return $pagina;
        }

        $pagina['esito'] = $risultato['esito'];
        $pagina['messaggio'] = $risultato['messaggio'];
        $pagina['codice_sala'] = $risultato['codice_sala'] ?? null;

        if ($risultato['esito'] === 'successo') {
            $pagina['dati'] = $datiVuoti;
        }

        return $pagina;
    }
}