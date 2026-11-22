<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\AccountRepository;

class AutorizzazioneService
{
    private AccountRepository $accountRepository;
    private SessioneService $sessione;

    public function __construct(
        ?AccountRepository $accountRepository = null,
        ?SessioneService $sessione = null
    ) {
        $this->accountRepository = $accountRepository ?? new AccountRepository();
        $this->sessione = $sessione ?? new SessioneService();
    }

    /**
     * Restituisce i ruoli dell'utente autenticato.
     *
     * @return string[] Array vuoto se non c'è un utente autenticato.
     */
    public function getRuoliUtente(): array
    {
        $idUtente = $this->sessione->getIdUtente();

        if ($idUtente === null) {
            return [];
        }

        return $this->accountRepository->trovaRuoli($idUtente);
    }

    public function haRuolo(string $ruolo): bool
    {
        return in_array($ruolo, $this->getRuoliUtente(), true);
    }

    /**
     * Verifica se l'utente può accedere a una vista.
     * Il gestore può accedere a tutte le viste del sistema.
     *
     * @param string[] $ruoliRichiesti Ruoli abilitati alla vista.
     */
    public function haAccessoA(array $ruoliRichiesti): bool
    {
        $ruoliUtente = $this->getRuoliUtente();

        if (in_array('GESTORE', $ruoliUtente, true)) {
            return true;
        }

        return count(array_intersect($ruoliUtente, $ruoliRichiesti)) > 0;
    }
}
