<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;

class ServizioAttivazioneAccount
{
    private AccountRepository $repository;
    private GeneratoreTokenAttivazione $generatore;
    private int $oreValidita;
    private ?UtenteRepository $utenti;
    private ?InvioEmailAttivazione $invioEmail;

    public function __construct(
        AccountRepository $repository,
        GeneratoreTokenAttivazione $generatore,
        int $oreValidita = 24,
        ?UtenteRepository $utenti = null,
        ?InvioEmailAttivazione $invioEmail = null
    ) {
        $this->repository = $repository;
        $this->generatore = $generatore;
        $this->oreValidita = $oreValidita;
        $this->utenti = $utenti;
        $this->invioEmail = $invioEmail;
    }

    /**
     * Crea una richiesta di attivazione per un iscritto.
     *
     * @return array{token: string, scadenza: string}
     */
    public function creaRichiesta(
        int $idUtente,
        ?DateTimeImmutable $adesso = null
    ): array {
        $adesso = $adesso ?? new DateTimeImmutable();
        $token = $this->generatore->genera();
        $tokenHash = $this->generatore->creaHash($token);
        $scadenza = $adesso->modify('+' . $this->oreValidita . ' hours');
        $scadenzaTesto = $scadenza->format('Y-m-d H:i:s');

        $this->repository->creaInAttesa($idUtente, $tokenHash, $scadenzaTesto);

        return [
            'token' => $token,
            'scadenza' => $scadenzaTesto,
        ];
    }

    /**
     * Restituisce l'account associato al token se il token e ancora valido.
     */
    public function verificaToken(
        string $token,
        ?DateTimeImmutable $adesso = null
    ): ?array {
        if (trim($token) === '') {
            return null;
        }

        $adesso = $adesso ?? new DateTimeImmutable();
        $tokenHash = $this->generatore->creaHash($token);
        $account = $this->repository->trovaPerToken($tokenHash);

        if ($account === null || $account['stato'] !== 'PENDING') {
            return null;
        }

        $scadenza = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $account['activation_expires_at']
        );

        if ($scadenza === false || $scadenza <= $adesso) {
            return null;
        }

        return $account;
    }

    public function richiediPerEmail(string $email, ?DateTimeImmutable $adesso = null): array
    {
        if ($this->utenti === null || $this->invioEmail === null) {
            throw new InvalidArgumentException('Servizio non configurato per la richiesta email.');
        }

        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['esito' => 'dati_non_validi', 'messaggio' => 'Inserisci un indirizzo email valido.'];
        }

        $utente = $this->utenti->trovaPerEmail($email);
        if ($utente === null) {
            return ['esito' => 'non_trovato', 'messaggio' => 'Email non associata a un iscritto.'];
        }

        $account = $this->repository->trovaPerEmail($email);
        if ($account !== null && $account['stato'] === 'ACTIVE') {
            return ['esito' => 'gia_attivo', 'messaggio' => 'L’account è già attivo.'];
        }

        $richiesta = $this->creaRichiesta((int) $utente['id'], $adesso);
        $this->invioEmail->invia($email, $richiesta['token']);

        return ['esito' => 'inviata', 'messaggio' => 'Email di attivazione inviata.'];
    }

    public function attiva(string $token, string $password, ?DateTimeImmutable $adesso = null): array
    {
        if (strlen($password) < 8) {
            return ['esito' => 'dati_non_validi', 'messaggio' => 'La password deve contenere almeno 8 caratteri.'];
        }

        $account = $this->verificaToken($token, $adesso);
        if ($account === null) {
            return ['esito' => 'token_non_valido', 'messaggio' => 'Il link non è valido o è scaduto.'];
        }

        $this->repository->attiva((int) $account['id'], password_hash($password, PASSWORD_DEFAULT));
        return ['esito' => 'attivato', 'messaggio' => 'Account attivato correttamente.'];
    }
}
