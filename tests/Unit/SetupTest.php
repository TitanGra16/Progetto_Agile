<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Config\Database;
use PHPUnit\Framework\TestCase;

final class SetupTest extends TestCase
{
    // Sono dati fittizi: questi test non aprono connessioni a MySQL.
    private function creaAmbienteDiProva(): array
    {
        return [
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_NAME' => 'fitslot',
            'DB_USER' => 'utente_prova',
            'DB_PASSWORD' => 'password_fittizia',
        ];
    }

    public function testAutoloadCaricaLaClasseDatabase(): void
    {
        $this->assertTrue(class_exists(Database::class));
    }

    public function testConfigurazioneValida(): void
    {
        $ambiente = $this->creaAmbienteDiProva();
        $configurazione = Database::leggiConfigurazione($ambiente);

        $this->assertSame(3306, $configurazione['porta']);
        $this->assertSame('fitslot', $configurazione['nome_database']);
        $this->assertSame('utente_prova', $configurazione['utente']);
    }
}