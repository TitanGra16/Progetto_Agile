<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use mysqli_sql_exception;

/** Regole del listino UE-08. Nessuna vendita, prenotazione o consumo crediti. */
class TipoAbbonamentoService
{
    public const SERVIZI = [
        'SALA_PESI' => 'Sala pesi',
        'PISCINA' => 'Piscina',
        'CORSI' => 'Corsi',
    ];

    public function __construct(private TipoAbbonamentoRepository $repository)
    {
    }

    /** @return list<array<string,mixed>> */
    public function elenca(): array
    {
        return array_map(fn (array $riga): array => $this->arricchisci($riga), $this->repository->elenca());
    }

    /** @return array<string,mixed>|null */
    public function trovaPerId(int $id): ?array
    {
        $riga = $this->repository->trovaPerId($id);
        return $riga === null ? null : $this->arricchisci($riga);
    }

    /** @param array<string,mixed> $input @return array{esito:string,messaggio:string,errori:array<string,string>,id:?int} */
    public function crea(array $input): array
    {
        $verifica = $this->valida($input);
        if ($verifica['errori'] !== []) {
            return $this->errore($verifica['errori']);
        }
        try {
            $id = $this->repository->crea($verifica['dati']);
            return ['esito' => 'ok', 'messaggio' => 'Tipo di abbonamento creato.', 'errori' => [], 'id' => $id];
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                return $this->errore(['codice' => 'Questo codice esiste già.']);
            }
            throw $e;
        }
    }

    /** @param array<string,mixed> $input @return array{esito:string,messaggio:string,errori:array<string,string>,id:?int} */
    public function aggiorna(int $id, array $input): array
    {
        $esistente = $id > 0 ? $this->repository->trovaPerId($id) : null;
        if ($esistente === null) {
            return $this->errore(['id' => 'Tipo di abbonamento inesistente.']);
        }
        // Il codice del piano esistente non cambia mai durante le modifiche.
        $input['codice'] = $esistente['codice'];
        $verifica = $this->valida($input);
        if ($verifica['errori'] !== []) {
            return $this->errore($verifica['errori']);
        }
        $this->repository->aggiorna($id, $verifica['dati']);
        return ['esito' => 'ok', 'messaggio' => 'Tipo di abbonamento aggiornato.', 'errori' => [], 'id' => $id];
    }

    /**
     * @param array<string,mixed> $input
     * @return array{dati:array<string,mixed>,errori:array<string,string>}
     */
    public function valida(array $input): array
    {
        $errori = [];
        $codice = strtoupper(trim(self::testo($input['codice'] ?? '')));
        $nome = trim(self::testo($input['nome'] ?? ''));
        $categoria = strtoupper(trim(self::testo($input['categoria'] ?? '')));
        $prezzo = self::normalizzaPrezzo(self::testo($input['prezzo'] ?? ''));

        if (!preg_match('/^[A-Z0-9_]{2,40}$/D', $codice)) {
            $errori['codice'] = 'Usa da 2 a 40 caratteri: lettere maiuscole, cifre e underscore.';
        }
        if (strlen($nome) < 2 || strlen($nome) > 100) {
            $errori['nome'] = 'Il nome deve contenere da 2 a 100 caratteri.';
        }
        if (!in_array($categoria, ['SINGOLO', 'PACCHETTO', 'TEMPO'], true)) {
            $errori['categoria'] = 'Categoria non riconosciuta.';
        }
        if ($prezzo === null) {
            $errori['prezzo'] = 'Inserisci un prezzo positivo, con massimo due decimali.';
        }

        $ingressi = null;
        $durata = null;
        $ingressiInput = trim(self::testo($input['ingressi_totali'] ?? ''));
        $durataInput = trim(self::testo($input['durata_mesi'] ?? ''));
        if ($categoria === 'SINGOLO' || $categoria === 'PACCHETTO') {
            $minimo = $categoria === 'SINGOLO' ? 1 : 2;
            if (!ctype_digit($ingressiInput) || (int) $ingressiInput < $minimo || (int) $ingressiInput > 1000
                || ($categoria === 'SINGOLO' && (int) $ingressiInput !== 1)) {
                $errori['ingressi_totali'] = $categoria === 'SINGOLO'
                    ? 'L’ingresso singolo deve avere esattamente un credito.'
                    : 'Il pacchetto deve avere da 2 a 1000 crediti.';
            } else {
                $ingressi = (int) $ingressiInput;
            }
            if ($durataInput !== '') {
                $errori['durata_mesi'] = 'I piani a ingressi non hanno scadenza temporale.';
            }
        } elseif ($categoria === 'TEMPO') {
            if (!ctype_digit($durataInput) || (int) $durataInput < 1 || (int) $durataInput > 120) {
                $errori['durata_mesi'] = 'La durata deve essere da 1 a 120 mesi.';
            } else {
                $durata = (int) $durataInput;
            }
            if ($ingressiInput !== '') {
                $errori['ingressi_totali'] = 'Un abbonamento illimitato non prevede crediti.';
            }
        }

        $selezionati = $input['servizi_inclusi'] ?? [];
        if (!is_array($selezionati)) {
            $selezionati = [];
        }
        $servizi = [];
        $sconosciuti = false;
        foreach ($selezionati as $servizio) {
            if (!is_string($servizio) || !array_key_exists($servizio, self::SERVIZI)) {
                $sconosciuti = true;
                continue;
            }
            $servizi[$servizio] = true;
        }
        $servizi = array_keys($servizi);
        if ($servizi === [] || $sconosciuti) {
            $errori['servizi_inclusi'] = 'Seleziona almeno un servizio valido: sala pesi, piscina o corsi.';
        }

        $agevolazione = $input['agevolazione_studenti'] ?? false;
        $agevolazione = $agevolazione === true || $agevolazione === 1 || $agevolazione === '1' || $agevolazione === 'on';
        if ($agevolazione && $categoria !== 'PACCHETTO') {
            $errori['agevolazione_studenti'] = 'Lo sconto studenti vale solo sui pacchetti a ingressi.';
        }

        $attivo = $input['attivo'] ?? false;
        $attivo = $attivo === true || $attivo === 1 || $attivo === '1' || $attivo === 'on';

        return [
            'dati' => [
                'codice' => $codice,
                'nome' => $nome,
                'categoria' => $categoria,
                'prezzo' => $prezzo,
                'ingressi_totali' => $ingressi,
                'durata_mesi' => $durata,
                'servizi_inclusi' => $servizi,
                'sconto_studenti_percentuale' => $agevolazione ? 20 : 0,
                'studenti_dalle' => $agevolazione ? '08:00' : null,
                'studenti_alle' => $agevolazione ? '13:30' : null,
                'attivo' => $attivo ? 1 : 0,
            ],
            'errori' => $errori,
        ];
    }

    /** Applica unicamente la tariffa ridotta del catalogo. Non verifica l'idoneita' del cliente. */
    public static function calcolaPrezzoStudente(string $prezzo, int $scontoPercentuale): string
    {
        $normale = self::normalizzaPrezzo($prezzo);
        if ($normale === null || !in_array($scontoPercentuale, [0, 20], true)) {
            throw new \InvalidArgumentException('Prezzo o sconto non valido.');
        }
        [$euro, $centesimi] = explode('.', $normale);
        $totaleCentesimi = (int) $euro * 100 + (int) $centesimi;
        $ridotto = intdiv($totaleCentesimi * (100 - $scontoPercentuale) + 50, 100);
        return sprintf('%d.%02d', intdiv($ridotto, 100), $ridotto % 100);
    }

    /** @param array<string,mixed> $riga @return array<string,mixed> */
    private function arricchisci(array $riga): array
    {
        $riga['prezzo_studenti'] = $riga['sconto_studenti_percentuale'] === 20
            ? self::calcolaPrezzoStudente((string) $riga['prezzo'], 20)
            : null;
        return $riga;
    }

    private static function testo(mixed $valore): string
    {
        return is_string($valore) || is_int($valore) ? (string) $valore : '';
    }

    private static function normalizzaPrezzo(string $prezzo): ?string
    {
        $prezzo = str_replace(',', '.', trim($prezzo));
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $prezzo)) {
            return null;
        }
        [$euro, $centesimi] = array_pad(explode('.', $prezzo, 2), 2, '');
        $cent = (int) $euro * 100 + (int) str_pad($centesimi, 2, '0');
        if ($cent <= 0) {
            return null;
        }
        return sprintf('%d.%02d', intdiv($cent, 100), $cent % 100);
    }

    /** @param array<string,string> $errori @return array{esito:string,messaggio:string,errori:array<string,string>,id:?int} */
    private function errore(array $errori): array
    {
        return ['esito' => 'errore', 'messaggio' => 'Controlla i dati inseriti.', 'errori' => $errori, 'id' => null];
    }
}
