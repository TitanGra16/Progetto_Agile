<?php

declare(strict_types=1);
namespace Ladythelemons\Fitslot\Service;
use Ladythelemons\Fitslot\Repository\AccountRepository;

class AutenticazioneService{
    private AccountRepository $repository;
    private ?string $errore = null;

    public function __construct(?AccountRepository $repository = null)
    {
        $this->repository = $repository ?? new AccountRepository();
    }

    public function getErrore(): ?string
    {
        return $this->errore;
    }

    public function verificaPassword(
        string $passwordInserita,
        string $hashMemorizzato
    ): bool {
        return password_verify($passwordInserita, $hashMemorizzato);
    }

    public function verificaCredenziali(
        string $email,
        string $password
    ): ?int {
        $this->errore = null;

        $account = $this->repository->trovaPerEmail($email);

        // Se l'account non esiste, il login fallisce e se la password non corrisponde, il login fallisce
        if ($account === null || !$this->verificaPassword($password, $account['password_hash'])) {
            return null;
        }

        // Le credenziali sono corrette
        return (int) $account['id'];
    }
}
