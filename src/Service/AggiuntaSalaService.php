<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\SaleRepository;
use Ladythelemons\Fitslot\DataModel\Sala;

class AggiuntaSalaService
{
    private SaleRepository $sale;
    private ValidatoreSala $validatore;

    /**
     * Crea il servizio usando il repository delle sale.
     */
    public function __construct(SaleRepository $sale)
    {
        $this->sale = $sale;
        $this->validatore = new ValidatoreSala();
    }

    /**
     * Aggiunge una nuova sala al sistema.
     *
     * @param array<string, mixed> $datiSala Dati ricevuti dal modulo di aggiunta sala.
     * @return array{esito: string, messaggio: string, codice_sala?: int}
     */
    public function aggiunge(array $datiSala): array
    {
        if (!$this->validatore->validaSala($datiSala)) {
            return [
                'esito' => 'dati_non_validi',
                'messaggio' => 'I dati della sala sono mancanti o non validi.',
            ];
        }

        if($this->sale->esisteSalaConNome(trim($datiSala['nome']))) {
            return [
                'esito' => 'nome_duplicato',
                'messaggio' => 'Nome della sala già esistente.',
            ];
        }

        $sala = new Sala(
            null,
            trim($datiSala['nome']),
            (int) $datiSala['capienza'],
            trim($datiSala['inizio_occupazione']),
            trim($datiSala['fine_occupazione'])
        );

        try {
            $codiceSala = $this->sale->salva($sala);
        } catch (\mysqli_sql_exception $e) {
            return [
                'esito' => 'errore_database',
                'messaggio' => 'Errore durante il salvataggio della sala: ' . $e->getMessage(),
            ];
        }

        return [
            'esito' => 'successo',
            'messaggio' => 'Sala aggiunta con successo.',
            'codice_sala' => $codiceSala,
        ];
    }
}