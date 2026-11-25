<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use mysqli_sql_exception;

class CaricamentoCertificatoService
{
    public function __construct(
        private CertificatoMedicoRepository $repository,
        private ArchivioPdfCertificati $archivio
    ) {
    }

    /**
     * UE-51: carica un certificato medico
     * in attesa di verifica dalla Reception.
     *
     * Restituisce null se il caricamento riesce,
     * altrimenti un messaggio di errore.
     */
    public function carica(int $utenteId, array $file): ?string
    {
        // Verifica che l'utente esista.
        if ($utenteId <= 0 ||
            $this->repository->trovaUtente($utenteId) === null) {
            return 'Utente non trovato.';
        }

        // Un iscritto può avere un solo documento in attesa.
        $certificatoInAttesa =
            $this->repository->trovaInAttesaPerUtente($utenteId);

        if ($certificatoInAttesa !== null) {
            return 'Hai già un certificato in attesa di verifica.';
        }

        // Riutilizziamo la gestione sicura dei PDF della UE-05.
        $nomeFile = $this->archivio->salva($file);

        if ($nomeFile === null) {
            return 'Caricamento non riuscito. '
                . 'Seleziona un PDF valido di massimo 5 MB.';
        }

        // Salviamo il riferimento al nuovo documento.
        try {
            $this->repository->salvaInAttesa(
                $utenteId,
                $nomeFile
            );
        } catch (mysqli_sql_exception $errore) {
            // Se il database non salva il record,
            // eliminiamo il PDF appena caricato.
            $this->archivio->elimina($nomeFile);

            if ((int) $errore->getCode() === 1062) {
                return 'Hai già un certificato in attesa di verifica.';
            }

            return 'Impossibile registrare il certificato. '
                . 'Riprova più tardi.';
        }

        return null;
    }
}
