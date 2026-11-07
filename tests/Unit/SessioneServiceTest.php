<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\SessioneService;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SessioneServiceTest extends TestCase
{
    // TEST 1: Utente non autenticato
    public function testUtenteNonAutenticato(): void
    {
        $sessione = new SessioneService();

        $risultato = $sessione->getIdUtente();

        $this->assertNull($risultato);
    }

    // TEST 2: Avvio della sessione
    public function testAvviaSessione(): void
    {
        $sessione = new SessioneService();

        $sessione->avvia();

        $idIniziale = session_id();

        // Richiamiamo avvia() una seconda volta
        $sessione->avvia();

        $this->assertSame(PHP_SESSION_ACTIVE, session_status());
        $this->assertSame($idIniziale, session_id());
    }

    // TEST 3: Login e registrazione dell'utente
    public function testRegistraAccesso(): void
    {
        $sessione = new SessioneService();

        $sessione->avvia();
        $idPrecedente = session_id();

        $sessione->registraAccesso(5);

        $this->assertSame(5, $_SESSION['utente_id']);

        $this->assertSame(5, $sessione->getIdUtente());

        $this->assertNotSame($idPrecedente, session_id());
    }

    // TEST 4: Logout
    public function testTerminaSessione(): void
    {
        $sessione = new SessioneService();

        // Simuliamo un utente autenticato
        $sessione->registraAccesso(5);

        // Aggiungiamo un altro dato alla sessione
        $_SESSION['prova'] = 'test';

        // Eseguiamo il logout
        $sessione->termina();

        // Verifichiamo che i dati siano stati eliminati
        $this->assertSame([], $_SESSION);

        // Verifichiamo che la sessione non sia più attiva
        $this->assertSame(PHP_SESSION_NONE, session_status());
    }
}
