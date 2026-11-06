<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\DataModel\Utente;
use Ladythelemons\Fitslot\Repository\UtenteRepository;

/**
 * Coordina la validazione, il controllo dei duplicati e il salvataggio.
 */
class RegistrazioneIscrittoService
{
    private UtenteRepository $archivioUtenti;
    private ValidatoreIscritto $validatore;

    /**
     * Crea il servizio usando il repository degli iscritti.
     */
    public function __construct(UtenteRepository $archivioUtenti)
    {
        $this->archivioUtenti = $archivioUtenti;
        $this->validatore = new ValidatoreIscritto();
    }

    /**
     * Registra l'iscritto oppure restituisce un avviso senza salvarlo.
     *
     * @param array<string, mixed> $datiIscritto Dati ricevuti dal modulo di registrazione.
     * @param bool $confermaDuplicato True solo dopo la conferma esplicita della reception.
     * @return array{esito: string, messaggio: string, codice_iscritto: int|null}
     *
     * @throws \mysqli_sql_exception Se il repository non riesce a cercare o salvare.
     */
    public function registra(array $datiIscritto, bool $confermaDuplicato = false): array
    {
        if (!$this->validatore->validaIscritto($datiIscritto)) {
            return [
                'esito' => 'dati_non_validi',
                'messaggio' => "I dati dell'iscritto sono mancanti o non validi.",
                'codice_iscritto' => null,
            ];
        }

        if ($this->archivioUtenti->esisteEmail(trim($datiIscritto['email']))) {
            return [
                'esito' => 'email_duplicata',
                'messaggio' => 'Email già associata a un iscritto',
                'codice_iscritto' => null,
            ];
        }

        $possibileDuplicato = $this->archivioUtenti->esistePotenzialeDuplicato(
            $datiIscritto['nome'],
            $datiIscritto['cognome'],
            $datiIscritto['data_nascita']
        );

        if ($possibileDuplicato && !$confermaDuplicato) {
            return [
                'esito' => 'possibile_duplicato',
                'messaggio' => 'Possibile duplicato: verificare nome, cognome e data di nascita. Confermare per registrare comunque.',
                'codice_iscritto' => null,
            ];
        }

        $utente = new Utente(
            null,
            $datiIscritto['nome'],
            $datiIscritto['cognome'],
            $datiIscritto['data_nascita'],
            trim($datiIscritto['recapito'] ?? ''),
            trim($datiIscritto['email'])
        );
        try {
            $codiceIscritto = $this->archivioUtenti->salva($utente);
        } catch (\mysqli_sql_exception $errore) {
            if ($errore->getCode() !== 1062) {
                throw $errore;
            }

            return [
                'esito' => 'email_duplicata',
                'messaggio' => 'Email già associata a un iscritto',
                'codice_iscritto' => null,
            ];
        }

        $messaggio = 'Iscritto registrato correttamente.';
        if ($possibileDuplicato) {
            $messaggio = 'Iscritto registrato dopo conferma esplicita del possibile duplicato.';
        }

        return [
            'esito' => 'registrato',
            'messaggio' => $messaggio,
            'codice_iscritto' => $codiceIscritto,
        ];
    }
}
