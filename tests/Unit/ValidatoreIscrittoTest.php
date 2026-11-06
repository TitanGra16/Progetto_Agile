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
            'recapito' => '+39 333 1234567',
            'email' => 'iscritto@example.test',
            'password' => 'password_di_prova',
        ];
    }

    public function testValido(): void
    {
        $validatore = new ValidatoreIscritto();
        $this->assertTrue($validatore->validaIscritto($this->dati()));
    }

    public function testVuoto(): void
    {
        $validatore = new ValidatoreIscritto();
        $this->assertFalse($validatore->validaIscritto([]));
    }

    public function testCampiObbligatori(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (array_keys($this->dati()) as $campo) {
            $iscritto = $this->dati();
            unset($iscritto[$campo]);
            $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
        }
    }

    public function testCampiVuoti(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (array_keys($this->dati()) as $campo) {
            foreach (['', '   '] as $valore) {
                $iscritto = $this->dati();
                $iscritto[$campo] = $valore;
                $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
            }
        }
    }

    public function testEmail(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->dati();
        $iscritto['email'] = 'email-senza-dominio';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testDataInesistente(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->dati();
        $iscritto['data_nascita'] = '2023-02-29';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testFormatoData(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (['29/02/2000', '2000-2-29', '2000-aa-29'] as $data) {
            $iscritto = $this->dati();
            $iscritto['data_nascita'] = $data;
            $this->assertFalse($validatore->validaIscritto($iscritto));
        }
    }

    public function testDataFutura(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->dati();
        $iscritto['data_nascita'] = '2999-01-01';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testCampiLunghi(): void
    {
        $validatore = new ValidatoreIscritto();
        $lunghezzeNonValide = ['nome' => 101, 'cognome' => 101, 'recapito' => 191, 'email' => 256];
        foreach ($lunghezzeNonValide as $campo => $lunghezza) {
            $iscritto = $this->dati();
            $iscritto[$campo] = str_repeat('a', $lunghezza);
            $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
        }
    }

    public function testLunghezzeMassime(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->dati();
        $iscritto['nome'] = str_repeat('a', 100);
        $iscritto['cognome'] = str_repeat('b', 100);
        $iscritto['recapito'] = str_repeat('c', 190);
        $this->assertTrue($validatore->validaIscritto($iscritto));
    }
}
