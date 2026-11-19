<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Controller;

use Ladythelemons\Fitslot\Repository\CertificatoMedicoRepository;
use Ladythelemons\Fitslot\Service\ArchivioPdfCertificati;
use Ladythelemons\Fitslot\Service\CertificatoMedicoService;

class CertificatoMedicoController
{
    private CertificatoMedicoService $servizio;

    public function __construct()
    {
        $this->servizio = new CertificatoMedicoService(
            new CertificatoMedicoRepository(),
            new ArchivioPdfCertificati()
        );
    }

    public function gestisciRichiesta(string $metodo, array $parametri, array $file = []): array
    {
        $idRichiesto = $parametri['utente_id'] ?? null;
        $utenteId = self::leggiId($idRichiesto);
        $pagina = ['utente_id' => $utenteId, 'dati' => null, 'errore' => null, 'salvato' => false];

        if ($utenteId === null) {
            $pagina['errore'] = $metodo === 'GET' && $idRichiesto === null
                ? null
                : 'Inserisci un identificativo iscritto valido.';
            return $pagina;
        }

        if ($metodo === 'POST') {
            $scadenza = $parametri['scadenza'] ?? '';
            $pagina['errore'] = $this->servizio->registra(
                $utenteId,
                is_string($scadenza) ? $scadenza : '',
                $file,
                ($parametri['verificato'] ?? null) === '1'
            );

            if ($pagina['errore'] === null) {
                $pagina['salvato'] = true;
                return $pagina;
            }
        }

        $pagina['dati'] = $this->servizio->caricaIscritto($utenteId);
        if ($pagina['dati'] === null) {
            $pagina['errore'] ??= 'Iscritto non trovato.';
        }

        return $pagina;
    }

    public function percorsoDownload(mixed $id): ?string
    {
        $certificatoId = self::leggiId($id);

        return $certificatoId === null ? null : $this->servizio->percorsoDownload($certificatoId);
    }

    private static function leggiId(mixed $valore): ?int
    {
        $id = filter_var($valore, FILTER_VALIDATE_INT);

        return $id > 0 ? $id : null;
    }
}
