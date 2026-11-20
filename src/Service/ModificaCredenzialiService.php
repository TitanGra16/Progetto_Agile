<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\AccountRepository;
use Ladythelemons\Fitslot\Repository\UtenteRepository;

class ModificaCredenzialiService
{
    private AccountRepository $accountRepository;
    private UtenteRepository $utenteRepository;

    public function __construct()
    {
        $this->accountRepository = new AccountRepository();
        $this->utenteRepository = new UtenteRepository();
    }

    private function verificaPasswordAttuale(
        int $idUtente,
        string $passwordAttuale
    ): bool {
        $account = $this->accountRepository->trovaPerId($idUtente);

        if (
            $account === null ||
            $account['stato'] !== 'ACTIVE' ||
            empty($account['password_hash'])
        ) {
            return false;
        }

        return password_verify(
            $passwordAttuale,
            $account['password_hash']
        );
    }

public function modificaEmail(
    int $idUtente,
    string $nuovaEmail,
    string $passwordAttuale
): array {
    $nuovaEmail = trim($nuovaEmail);

    // Verifica che l'email sia valida
    if (
        !filter_var($nuovaEmail, FILTER_VALIDATE_EMAIL) ||
        strlen($nuovaEmail) > 255
    ) {
        return [
            'esito' => 'errore',
            'messaggio' => 'Inserisci un indirizzo email valido.'
        ];
    }

    // Verifica la password attuale
    if (!$this->verificaPasswordAttuale($idUtente, $passwordAttuale)) {
        return [
            'esito' => 'errore',
            'messaggio' => 'Password attuale non corretta.'
        ];
    }

    // Verifica che l'email non sia già utilizzata
    if ($this->utenteRepository->esisteEmail($nuovaEmail)) {
        return [
            'esito' => 'errore',
            'messaggio' => 'Questa email è già registrata.'
        ];
    }

    // Aggiorna l'email nel database
    $this->utenteRepository->aggiornaEmail(
        $idUtente,
        $nuovaEmail
    );

    return [
        'esito' => 'successo',
        'messaggio' => 'Email aggiornata correttamente!'
    ];
}

public function modificaPassword(
    int $idUtente,
    string $passwordAttuale,
    string $nuovaPassword,
    string $confermaPassword
): array {

    // Verifica la password attuale
    if (!$this->verificaPasswordAttuale($idUtente, $passwordAttuale)) {
        return [
            'esito' => 'errore',
            'messaggio' => 'Password attuale non corretta.'
        ];
    }

    // Controlla la lunghezza della nuova password
    if (strlen($nuovaPassword) < 8) {
        return [
            'esito' => 'errore',
            'messaggio' => 'La nuova password deve contenere almeno 8 caratteri.'
        ];
    }

    // Controlla che le due password coincidano
    if ($nuovaPassword !== $confermaPassword) {
        return [
            'esito' => 'errore',
            'messaggio' => 'Le nuove password non coincidono.'
        ];
    }

    // Impedisce di riutilizzare la password attuale
    if ($nuovaPassword === $passwordAttuale) {
        return [
            'esito' => 'errore',
            'messaggio' => 'La nuova password deve essere diversa da quella attuale.'
        ];
    }

    // Genera l'hash della nuova password
    $nuovoHash = password_hash($nuovaPassword, PASSWORD_DEFAULT);

    // Aggiorna la password nel database
    $this->accountRepository->aggiornaPassword(
        $idUtente,
        $nuovoHash
    );

    return [
        'esito' => 'successo',
        'messaggio' => 'Password aggiornata correttamente!'
    ];
}

}
