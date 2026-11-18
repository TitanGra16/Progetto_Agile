<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;

class CertificatoMedicoService
{
    public function __construct(
        private CertificatoMedicoRepository $repository,
        private ArchivioPdfCertificati $archivio
    ) {
    }

    /** Restituisce il messaggio di errore o null se la scadenza è valida. */
    public static function validaScadenza(string $scadenza, ?DateTimeImmutable $oggi = null): ?string
    {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})\z/', $scadenza, $parti)
            || !checkdate((int) $parti[2], (int) $parti[3], (int) $parti[1])) {
            return 'Inserisci una data di scadenza valida.';
        }

        $dataOdierna = $oggi?->format('Y-m-d') ?? date('Y-m-d');
        if ($scadenza < $dataOdierna) {
            return 'Il certificato è già scaduto.';
        }

        return null;
    }

    /** Restituisce il messaggio di errore o null dopo il salvataggio. */
    public function registra(int $utenteId, string $scadenza, array $pdf): ?string
    {
        $errore = self::validaScadenza($scadenza);
        if ($errore !== null) {
            return $errore;
        }

        if (!$this->repository->esisteUtente($utenteId)) {
            return 'Iscritto non trovato.';
        }

        $nomeFile = $this->archivio->salva($pdf);
        if ($nomeFile === null) {
            return 'PDF non salvato. Verifica che il file sia un PDF di massimo 3 MiB.';
        }

        $this->repository->salvaOaggiorna($utenteId, $scadenza, $nomeFile);

        return null;
    }
}
