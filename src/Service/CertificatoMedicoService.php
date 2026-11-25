<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

use DateTimeImmutable;
use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use mysqli_sql_exception;

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

        if (self::stato($scadenza, $oggi) === 'SCADUTO') {
            return 'Il certificato è già scaduto.';
        }

        return null;
    }

    public static function stato(?string $scadenza, ?DateTimeImmutable $oggi = null): string
    {
        if ($scadenza === null) {
            return 'ASSENTE';
        }

        $dataOdierna = $oggi?->format('Y-m-d') ?? date('Y-m-d');

        return $scadenza < $dataOdierna ? 'SCADUTO' : 'VALIDO';
    }

    public function caricaIscritto(int $utenteId): ?array
    {
        $utente = $this->repository->trovaUtente($utenteId);
        if ($utente === null) {
            return null;
        }

        $certificato = $this->repository->trovaPerUtente($utenteId);
        if ($certificato !== null) {
            $certificato['pdf_disponibile'] = $this->archivio->percorso($certificato['nome_file']) !== null;
        }

        return [
            'utente' => $utente,
            'certificato' => $certificato,
            'stato' => self::stato($certificato['data_scadenza'] ?? null),
        ];
    }

    public function percorsoDownload(int $certificatoId): ?string
    {
        return $this->archivio->percorso($this->repository->trovaNomeFile($certificatoId));
    }

    /** Restituisce il messaggio di errore o null dopo il salvataggio. */
    public function registra(int $utenteId, string $scadenza, array $pdf, bool $verificato = false): ?string
    {
        $errore = self::validaScadenza($scadenza);
        if ($errore !== null) {
            return $errore;
        }

        if (!$verificato) {
            return 'Conferma di aver verificato il certificato cartaceo.';
        }

        if ($this->repository->trovaUtente($utenteId) === null) {
            return 'Iscritto non trovato.';
        }

        $nomeFile = $this->archivio->salva($pdf);
        if ($nomeFile === null) {
            return 'PDF non salvato. Verifica che il file sia un PDF di massimo 5 MB.';
        }

        try {
            $precedente = $this->repository->salvaOaggiorna($utenteId, $scadenza, $nomeFile);
        } catch (mysqli_sql_exception) {
            $this->archivio->elimina($nomeFile);
            return 'Non è stato possibile salvare il certificato.';
        }

        if ($precedente !== null && $precedente !== $nomeFile) {
            $this->archivio->elimina($precedente);
        }

        return null;
    }
}
