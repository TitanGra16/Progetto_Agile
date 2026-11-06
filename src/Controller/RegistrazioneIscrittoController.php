<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Controller;

use Ladythelemons\Fitslot\Repository\UtenteRepository;
use Ladythelemons\Fitslot\Service\RegistrazioneIscrittoService;
use Throwable;

/** Legge il form e prepara i dati della pagina, senza eseguire query SQL. */
class RegistrazioneIscrittoController
{
    private ?RegistrazioneIscrittoService $servizio;

    public function __construct(?RegistrazioneIscrittoService $servizio = null)
    {
        $this->servizio = $servizio;
    }

    /** La sessione ricorda il form e l'avviso; non rappresenta un'autenticazione. */
    public function gestisciRichiesta(string $metodo, array $richiesta, array &$sessione): array
    {
        if (!isset($sessione['token_registrazione'])) {
            $sessione['token_registrazione'] = bin2hex(random_bytes(32));
        }
        $datiVuoti = ['nome' => '', 'cognome' => '', 'data_nascita' => '', 'email' => '', 'recapito' => ''];
        $pagina = [
            'esito' => 'iniziale',
            'messaggio' => '',
            'codice_iscritto' => null,
            'dati' => $datiVuoti,
            'richiede_conferma' => false,
            'token' => $sessione['token_registrazione'],
        ];

        if ($metodo !== 'POST') {
            if (isset($sessione['iscritto_da_confermare'])) {
                $pagina['esito'] = 'possibile_duplicato';
                $pagina['dati'] = $sessione['iscritto_da_confermare']['dati'];
                $pagina['messaggio'] = $sessione['iscritto_da_confermare']['messaggio'];
                $pagina['richiede_conferma'] = true;
            }
            return $pagina;
        }

        foreach (array_keys($datiVuoti) as $campo) {
            if (isset($richiesta[$campo])) {
                if (!is_string($richiesta[$campo])) {
                    $pagina['esito'] = 'dati_non_validi';
                    $pagina['messaggio'] = 'I campi devono contenere testo.';
                    return $pagina;
                }
                $pagina['dati'][$campo] = trim($richiesta[$campo]);
            }
        }
        // Il token impedisce invii del form provenienti da altre pagine.
        if (!isset($richiesta['token']) || !is_string($richiesta['token']) || !hash_equals($sessione['token_registrazione'], $richiesta['token'])) {
            $pagina['esito'] = 'errore';
            $pagina['messaggio'] = 'Il modulo è scaduto o non valido. Controlla i dati e riprova.';
            return $pagina;
        }

        $azione = $richiesta['azione'] ?? 'registra';
        if ($azione === 'modifica') {
            unset($sessione['iscritto_da_confermare']);
            return $pagina;
        }
        if ($azione !== 'registra' && $azione !== 'conferma') {
            $pagina['esito'] = 'errore';
            $pagina['messaggio'] = 'Operazione non valida. Invia di nuovo il modulo.';
            return $pagina;
        }

        $conferma = $azione === 'conferma';
        if ($conferma) {
            $datiDaConfermare = $sessione['iscritto_da_confermare']['dati'] ?? null;
            if ($datiDaConfermare !== $pagina['dati']) {
                unset($sessione['iscritto_da_confermare']);
                $pagina['esito'] = 'errore';
                $pagina['messaggio'] = 'Prima di confermare, verifica nuovamente i dati e il possibile duplicato.';
                return $pagina;
            }
        }

        try {
            // Il Service viene creato solo per un invio, mai per aprire il form.
            if ($this->servizio === null) {
                $this->servizio = new RegistrazioneIscrittoService(new UtenteRepository());
            }
            $risultato = $this->servizio->registra($pagina['dati'], $conferma);
        } catch (Throwable $errore) {
            $pagina['esito'] = 'errore';
            $pagina['messaggio'] = 'Non è stato possibile completare la registrazione. Riprova più tardi o avvisa il responsabile.';
            return $pagina;
        }

        $pagina['esito'] = $risultato['esito'];
        $pagina['messaggio'] = $risultato['messaggio'];
        $pagina['codice_iscritto'] = $risultato['codice_iscritto'];
        if ($risultato['esito'] === 'possibile_duplicato') {
            $sessione['iscritto_da_confermare'] = ['dati' => $pagina['dati'], 'messaggio' => $pagina['messaggio']];
            $pagina['richiede_conferma'] = true;
        } else {
            unset($sessione['iscritto_da_confermare']);
        }
        if ($risultato['esito'] === 'registrato') {
            $pagina['dati'] = $datiVuoti;
            $sessione['token_registrazione'] = bin2hex(random_bytes(32));
            $pagina['token'] = $sessione['token_registrazione'];
        }
        return $pagina;
    }
}
