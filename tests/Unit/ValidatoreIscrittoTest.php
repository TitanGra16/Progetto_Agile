<?php
declare(strict_types=1);

use Ladythelemons\Fitslot\Service\ValidatoreIscritto;
use PHPUnit\Framework\TestCase;

final class ValidatoreIscrittoTest extends TestCase
{
    public function testAccettaDatiValidiERifiutaDataScorretta(): void
    {
        $validatore = new ValidatoreIscritto();
        $dati = [
            'nome' => 'Anna',
            'cognome' => 'Rossi',
            'data_nascita' => '2000-02-29',
            'email' => 'anna@example.test',
            'recapito' => '3331234567',
        ];

        self::assertTrue($validatore->validaIscritto($dati));
        $dati['data_nascita'] = '2000-02-30';
        self::assertFalse($validatore->validaIscritto($dati));
    }
}
