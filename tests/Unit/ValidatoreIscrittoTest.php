<?php

declare(strict_types=1);

use Ladythelemons\Fitslot\Service\ValidatoreIscritto;
use PHPUnit\Framework\TestCase;

final class ValidatoreIscrittoTest extends TestCase
{
    private function creaIscrittoDiProva(): array
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

    public function testDatiValidiVengonoAccettati(): void
    {
        $validatore = new ValidatoreIscritto();
        $this->assertTrue($validatore->validaIscritto($this->creaIscrittoDiProva()));
    }

    public function testDatiAssentiVengonoRifiutati(): void
    {
        $validatore = new ValidatoreIscritto();
        $this->assertFalse($validatore->validaIscritto([]));
    }

    public function testOgniCampoObbligatorioDeveEsserePresente(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (array_keys($this->creaIscrittoDiProva()) as $campo) {
            $iscritto = $this->creaIscrittoDiProva();
            unset($iscritto[$campo]);
            $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
        }
    }

    public function testCampiVuotiOSoloSpaziVengonoRifiutati(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (array_keys($this->creaIscrittoDiProva()) as $campo) {
            foreach (['', '   '] as $valore) {
                $iscritto = $this->creaIscrittoDiProva();
                $iscritto[$campo] = $valore;
                $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
            }
        }
    }

    public function testEmailNonValidaVieneRifiutata(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->creaIscrittoDiProva();
        $iscritto['email'] = 'email-senza-dominio';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testDataInesistenteVieneRifiutata(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->creaIscrittoDiProva();
        $iscritto['data_nascita'] = '2023-02-29';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testDataInFormatoDiversoVieneRifiutata(): void
    {
        $validatore = new ValidatoreIscritto();
        foreach (['29/02/2000', '2000-2-29', '2000-aa-29'] as $data) {
            $iscritto = $this->creaIscrittoDiProva();
            $iscritto['data_nascita'] = $data;
            $this->assertFalse($validatore->validaIscritto($iscritto));
        }
    }

    public function testDataFuturaVieneRifiutata(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->creaIscrittoDiProva();
        $iscritto['data_nascita'] = '2999-01-01';
        $this->assertFalse($validatore->validaIscritto($iscritto));
    }

    public function testCampiTroppoLunghiVengonoRifiutati(): void
    {
        $validatore = new ValidatoreIscritto();
        $lunghezzeNonValide = ['nome' => 101, 'cognome' => 101, 'recapito' => 191, 'email' => 256];
        foreach ($lunghezzeNonValide as $campo => $lunghezza) {
            $iscritto = $this->creaIscrittoDiProva();
            $iscritto[$campo] = str_repeat('a', $lunghezza);
            $this->assertFalse($validatore->validaIscritto($iscritto), $campo);
        }
    }

    public function testLunghezzeMassimeDiNomeCognomeERecapitoVengonoAccettate(): void
    {
        $validatore = new ValidatoreIscritto();
        $iscritto = $this->creaIscrittoDiProva();
        $iscritto['nome'] = str_repeat('a', 100);
        $iscritto['cognome'] = str_repeat('b', 100);
        $iscritto['recapito'] = str_repeat('c', 190);
        $this->assertTrue($validatore->validaIscritto($iscritto));
    }
}
