<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\TipoAbbonamentoRepository;
use mysqli_sql_exception;

class TipoAbbonamentoService
{
    public const SERVIZI = [
        'SALA_PESI' => 'Sala pesi',
        'PISCINA' => 'Piscina',
        'CORSI' => 'Corsi',
    ];

    private const SCONTO_STUDENTI = 20;
    private const ERRORE_CODICE_DUPLICATO = 1062;

    public function __construct(private TipoAbbonamentoRepository $repository)
    {
    }

    /** @return list<array<string,mixed>> */
    public function elenca(): array
    {
        $piani = [];
        foreach ($this->repository->elenca() as $piano) {
            $piani[] = $this->aggiungiPrezzoStudenti($piano);
        }

        return $piani;
    }

    /** @return array<string,mixed>|null */
    public function trovaPerId(int $id): ?array
    {
        $riga = $this->repository->trovaPerId($id);

        return $riga === null ? null : $this->aggiungiPrezzoStudenti($riga);
    }

    public function crea(array $input): array
    {
        $verifica = $this->valida($input);
        if ($verifica['errori'] !== []) {
            return $this->errore($verifica['errori']);
        }

        try {
            $id = $this->repository->crea($verifica['dati']);
        } catch (mysqli_sql_exception $errore) {
            if ($errore->getCode() === self::ERRORE_CODICE_DUPLICATO) {
                return $this->errore(['codice' => 'Questo codice esiste già.']);
            }
            throw $errore;
        }

        return ['esito' => 'ok', 'messaggio' => 'Tipo di abbonamento creato.', 'errori' => [], 'id' => $id];
    }

    public function aggiorna(int $id, array $input): array
    {
        $esistente = $id > 0 ? $this->repository->trovaPerId($id) : null;
        if ($esistente === null) {
            return $this->errore(['id' => 'Tipo di abbonamento inesistente.']);
        }
        // L'aggiornamento usa il codice già salvato.
        $input['codice'] = $esistente['codice'];
        $verifica = $this->valida($input);
        if ($verifica['errori'] !== []) {
            return $this->errore($verifica['errori']);
        }
        $this->repository->aggiorna($id, $verifica['dati']);
        return ['esito' => 'ok', 'messaggio' => 'Tipo di abbonamento aggiornato.', 'errori' => [], 'id' => $id];
    }

    /** Restituisce i dati del modulo e gli eventuali errori. */
    public function valida(array $input): array
    {
        $errori = [];
        $codice = strtoupper(self::testo($input['codice'] ?? null));
        $nome = self::testo($input['nome'] ?? null);
        $categoria = strtoupper(self::testo($input['categoria'] ?? null));
        $prezzoCentesimi = self::prezzoInCentesimi(self::testo($input['prezzo'] ?? null));

        if (!preg_match('/^[A-Z0-9_]{2,40}$/D', $codice)) {
            $errori['codice'] = 'Usa da 2 a 40 caratteri: lettere maiuscole, cifre e underscore.';
        }
        if (strlen($nome) < 2 || strlen($nome) > 100) {
            $errori['nome'] = 'Il nome deve contenere da 2 a 100 caratteri.';
        }
        if (!in_array($categoria, ['SINGOLO', 'PACCHETTO', 'TEMPO'], true)) {
            $errori['categoria'] = 'Categoria non riconosciuta.';
        }
        if ($prezzoCentesimi === null) {
            $errori['prezzo'] = 'Inserisci un prezzo positivo, con massimo due decimali.';
        }

        $ingressi = null;
        $durata = null;
        $ingressiInput = self::testo($input['ingressi_totali'] ?? null);
        $durataInput = self::testo($input['durata_mesi'] ?? null);
        if ($categoria === 'SINGOLO' || $categoria === 'PACCHETTO') {
            $singolo = $categoria === 'SINGOLO';
            $ingressi = $singolo
                ? self::interoTra($ingressiInput, 1, 1)
                : self::interoTra($ingressiInput, 2, 1000);
            if ($ingressi === null) {
                $errori['ingressi_totali'] = $singolo
                    ? 'L’ingresso singolo deve avere esattamente un credito.'
                    : 'Il pacchetto deve avere da 2 a 1000 crediti.';
            }
            if ($durataInput !== '') {
                $errori['durata_mesi'] = 'I piani a ingressi non hanno scadenza temporale.';
            }
        } elseif ($categoria === 'TEMPO') {
            $durata = self::interoTra($durataInput, 1, 120);
            if ($durata === null) {
                $errori['durata_mesi'] = 'La durata deve essere da 1 a 120 mesi.';
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
            if (!is_string($servizio) || !isset(self::SERVIZI[$servizio])) {
                $sconosciuti = true;
                continue;
            }
            $servizi[$servizio] = true;
        }
        $servizi = array_keys($servizi);
        if ($servizi === [] || $sconosciuti) {
            $errori['servizi_inclusi'] = 'Seleziona almeno un servizio valido: sala pesi, piscina o corsi.';
        }

        $valoriSpuntati = [true, 1, '1', 'on'];
        $agevolazione = in_array($input['agevolazione_studenti'] ?? false, $valoriSpuntati, true);
        $attivo = in_array($input['attivo'] ?? false, $valoriSpuntati, true);
        if ($agevolazione && $categoria !== 'PACCHETTO') {
            $errori['agevolazione_studenti'] = 'Lo sconto studenti vale solo sui pacchetti a ingressi.';
        }

        return [
            'dati' => [
                'codice' => $codice,
                'nome' => $nome,
                'categoria' => $categoria,
                'prezzo' => $prezzoCentesimi === null ? null : self::formattaPrezzo($prezzoCentesimi),
                'ingressi_totali' => $ingressi,
                'durata_mesi' => $durata,
                'servizi_inclusi' => $servizi,
                'sconto_studenti_percentuale' => $agevolazione ? self::SCONTO_STUDENTI : 0,
                'studenti_dalle' => $agevolazione ? '08:00' : null,
                'studenti_alle' => $agevolazione ? '13:30' : null,
                'attivo' => $attivo ? 1 : 0,
            ],
            'errori' => $errori,
        ];
    }

    public static function calcolaPrezzoStudente(string $prezzo, int $scontoPercentuale): string
    {
        $centesimi = self::prezzoInCentesimi($prezzo);
        if ($centesimi === null || !in_array($scontoPercentuale, [0, self::SCONTO_STUDENTI], true)) {
            throw new \InvalidArgumentException('Prezzo o sconto non valido.');
        }

        $ridotto = (int) round($centesimi * (100 - $scontoPercentuale) / 100);
        return self::formattaPrezzo($ridotto);
    }

    private function aggiungiPrezzoStudenti(array $riga): array
    {
        $sconto = $riga['sconto_studenti_percentuale'];
        $riga['prezzo_studenti'] = $sconto === self::SCONTO_STUDENTI
            ? self::calcolaPrezzoStudente((string) $riga['prezzo'], $sconto)
            : null;
        return $riga;
    }

    private static function testo(mixed $valore): string
    {
        return is_string($valore) || is_int($valore) ? trim((string) $valore) : '';
    }

    private static function interoTra(string $testo, int $minimo, int $massimo): ?int
    {
        if (!ctype_digit($testo)) {
            return null;
        }

        $numero = (int) $testo;
        return $numero >= $minimo && $numero <= $massimo ? $numero : null;
    }

    private static function prezzoInCentesimi(string $prezzo): ?int
    {
        $prezzo = str_replace(',', '.', trim($prezzo));
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $prezzo)) {
            return null;
        }
        $centesimi = (int) round((float) $prezzo * 100);
        return $centesimi > 0 ? $centesimi : null;
    }

    private static function formattaPrezzo(int $centesimi): string
    {
        return number_format($centesimi / 100, 2, '.', '');
    }

    private function errore(array $errori): array
    {
        return ['esito' => 'errore', 'messaggio' => 'Controlla i dati inseriti.', 'errori' => $errori, 'id' => null];
    }
}
