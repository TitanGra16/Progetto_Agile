<?php

declare(strict_types=1);

namespace Ladythelemons\Fitslot\Service;

class SessioneService{
    // Avvia o recupera la sessione
    public function avvia(): void{
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Memorizza l'utente dopo il login
    public function registraAccesso(int $idUtente): void{
        $this->avvia();

        session_regenerate_id(true);

        $_SESSION['utente_id'] = $idUtente;
    }

    // Recupera l'ID dell'utente autenticato
    public function getIdUtente(): ?int{
        $this->avvia();

        if (!isset($_SESSION['utente_id'])) {
            return null;
        }

        return (int) $_SESSION['utente_id'];
    }

    // Termina la sessione durante il logout
    public function termina(): void{
        $this->avvia();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametri = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $parametri['path'],
                'domain' => $parametri['domain'],
                'secure' => $parametri['secure'],
                'httponly' => $parametri['httponly'],
                'samesite' => $parametri['samesite'] ?? 'Lax'
            ]);
        }

        session_destroy();
    }
}
