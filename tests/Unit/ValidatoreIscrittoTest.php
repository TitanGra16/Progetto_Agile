<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\ValidatoreIscritto;
use PHPUnit\Framework\TestCase;

final class ValidatoreIscrittoTest extends TestCase
{
    private function dati(): array
    {
        return [
            'nome' => 'Anna Maria',
            'cognome' => "D'Angelo",
            'data_nascita' => '2000-02-29',
            'email' => 'anna@example.test',
            'recapito' => '+39 333 1234567',
        ];
    }

    public function testAccettaDatiValidi(): void
    {
        $validatore = new ValidatoreIscritto();
        $this->assertTrue($validatore->validaIscritto($this->dati()));

        $dati = $this->dati();
        $dati['recapito'] = '';
        $this->assertTrue($validatore->validaIscritto($dati));
    }

    public function testRifiutaCampiMancantiOSbagliati(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (['nome', 'cognome', 'data_nascita', 'email'] as $campo) {
            $dati = $this->dati();
            unset($dati[$campo]);
            $this->assertFalse($validatore->validaIscritto($dati));
        }

        foreach (['email-non-valida', 123, ['email@example.test'], ''] as $email) {
            $dati = $this->dati();
            $dati['email'] = $email;
            $this->assertFalse($validatore->validaIscritto($dati));
        }
    }

    public function testRifiutaDateELunghezzeNonValide(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (['2023-02-29', '29/02/2000', '2999-01-01'] as $data) {
            $dati = $this->dati();
            $dati['data_nascita'] = $data;
            $this->assertFalse($validatore->validaIscritto($dati));
        }

        $dati = $this->dati();
        $dati['recapito'] = str_repeat('a', 191);
        $this->assertFalse($validatore->validaIscritto($dati));

        foreach (['nome' => 101, 'cognome' => 101, 'recapito' => 191] as $campo => $lunghezza) {
            $dati = $this->dati();
            $dati[$campo] = str_repeat('a', $lunghezza);
            $this->assertFalse($validatore->validaIscritto($dati));
        }
    }
}
