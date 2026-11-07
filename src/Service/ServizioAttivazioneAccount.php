<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\AccountRepository;

class ServizioAttivazioneAccount
{
    private AccountRepository $repository;
    private GeneratoreTokenAttivazione $generatore;
    private int $oreValidita;

    public function __construct(
        AccountRepository $repository,
        GeneratoreTokenAttivazione $generatore,
        int $oreValidita = 24
    ) {
        $this->repository = $repository;
        $this->generatore = $generatore;
        $this->oreValidita = $oreValidita;
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

        if ($account === null || $account['activated_at'] !== null) {
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
}
