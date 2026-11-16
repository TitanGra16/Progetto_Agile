<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;

class CertificatoMedicoService
{
    public function __construct(
        private CertificatoMedicoRepository $repository,
        private ArchivioPdfCertificati $archivio
    ) {
    }

    /** Accetta la scadenza odierna, non quella passata. */
    public static function validaScadenza(string $scadenza, ?DateTimeImmutable $oggi = null): void
    {
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $scadenza);

        if ($data === false || $data->format('Y-m-d') !== $scadenza) {
            throw new InvalidArgumentException('Inserisci una data di scadenza valida.');
        }

        $oggi = ($oggi ?? new DateTimeImmutable('today'))->setTime(0, 0);
        if ($data < $oggi) {
            throw new InvalidArgumentException('Il certificato è già scaduto e non può essere registrato come valido.');
        }
    }

    /** @param array<string, mixed> $pdf */
    public function registra(int $utenteId, string $scadenza, array $pdf): void
    {
        self::validaScadenza($scadenza);

        if (!$this->repository->esisteUtente($utenteId)) {
            throw new InvalidArgumentException('Iscritto non trovato.');
        }

        $nuovoFile = $this->archivio->salva($pdf);
        $this->repository->salvaOaggiorna($utenteId, $scadenza, $nuovoFile);
    }
}
