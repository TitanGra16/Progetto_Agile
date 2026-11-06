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

    public function __construct(UtenteRepository $archivioUtenti)
    {
        $this->archivioUtenti = $archivioUtenti;
        $this->validatore = new ValidatoreIscritto();
    }

    /**
     * Registra l'iscritto oppure restituisce un avviso senza salvarlo.
     *
     * @param array $datiIscritto Dati ricevuti dal modulo di registrazione.
     * @param bool $confermaDuplicato True solo dopo la conferma esplicita della reception.
     * @return array Esito, messaggio e codice_iscritto (id MySQL, null se non salvato).
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

        $passwordProtetta = password_hash($datiIscritto['password'], PASSWORD_DEFAULT);
        $utente = new Utente(
            null,
            $datiIscritto['nome'],
            $datiIscritto['cognome'],
            $datiIscritto['data_nascita'],
            $datiIscritto['recapito'],
            $datiIscritto['email'],
            $passwordProtetta
        );
        $codiceIscritto = $this->archivioUtenti->salva($utente);

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
