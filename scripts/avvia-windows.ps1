$ErrorActionPreference = 'Stop'

# Mettere questo file in scripts/avvia-windows.ps1.
# Il menu lavora sempre dalla cartella principale del repository FitSlot.
$repo = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path -LiteralPath (Join-Path $repo 'composer.json'))) {
    Write-Host 'ERRORE: composer.json non trovato. Metti lo script nella cartella scripts/ di FitSlot.' -ForegroundColor Red
    exit 1
}
Set-Location -LiteralPath $repo

function Pausa-Menu {
    [void](Read-Host 'Premi INVIO per tornare al menu')
}

function Verifica-Comando([string]$nome) {
    if (-not (Get-Command $nome -ErrorAction SilentlyContinue)) {
        throw "Comando '$nome' non trovato nel PATH. Controlla Laragon/PHP/Composer."
    }
}

function Prepara-Env {
    if (-not (Test-Path -LiteralPath (Join-Path $repo '.env'))) {
        $esempio = Join-Path $repo '.env.example'
        if (-not (Test-Path -LiteralPath $esempio)) {
            throw 'Mancano .env e .env.example.'
        }
        Copy-Item -LiteralPath $esempio -Destination (Join-Path $repo '.env')
        Write-Host 'Creato .env da .env.example. Controlla host, porta e credenziali MySQL.' -ForegroundColor Yellow
    }
}

function Prepara-Dipendenze {
    Verifica-Comando 'php'
    Verifica-Comando 'composer'
    Prepara-Env
    if (-not (Test-Path -LiteralPath (Join-Path $repo 'vendor/autoload.php'))) {
        Write-Host 'Dipendenze non presenti: eseguo composer install...'
        & composer install
        if ($LASTEXITCODE -ne 0) { throw "composer install fallito (codice $LASTEXITCODE)." }
    }
}

function Leggi-ConfigDb {
    Prepara-Env
    $config = @{
        DB_HOST = '127.0.0.1'
        DB_PORT = '3306'
        DB_NAME = 'fitslot'
        DB_USER = 'root'
        DB_PASSWORD = ''
    }
    foreach ($riga in Get-Content -LiteralPath (Join-Path $repo '.env')) {
        if ($riga -match '^\s*(DB_HOST|DB_PORT|DB_NAME|DB_USER|DB_PASSWORD)\s*=\s*(.*)$') {
            $chiave = $Matches[1]
            $valore = $Matches[2].Trim()
            if ($valore.Length -ge 2 -and
                (($valore.StartsWith('"') -and $valore.EndsWith('"')) -or
                 ($valore.StartsWith("'") -and $valore.EndsWith("'")))) {
                $valore = $valore.Substring(1, $valore.Length - 2)
            }
            $config[$chiave] = $valore
        }
    }
    return $config
}

function Trova-MySql {
    foreach ($nome in @('mysql.exe', 'mariadb.exe')) {
        $comando = Get-Command $nome -ErrorAction SilentlyContinue
        if ($comando) { return $comando.Source }
    }

    # Laragon spesso non aggiunge mysql.exe al PATH di Windows.
    $radici = @(
        (Join-Path $env:SystemDrive 'laragon'),
        (Join-Path $env:USERPROFILE 'laragon')
    )
    foreach ($radice in $radici) {
        foreach ($cartella in @('mysql', 'mariadb')) {
            $base = Join-Path $radice ("bin\" + $cartella)
            if (-not (Test-Path -LiteralPath $base)) { continue }
            $versioni = Get-ChildItem -LiteralPath $base -Directory -ErrorAction SilentlyContinue |
                Sort-Object -Property Name -Descending
            foreach ($versione in $versioni) {
                foreach ($nome in @('mysql.exe', 'mariadb.exe')) {
                    $exe = Join-Path $versione.FullName ("bin\" + $nome)
                    if (Test-Path -LiteralPath $exe) { return $exe }
                }
            }
        }
    }
    return $null
}

function Porta-InUso([int]$porta) {
    # Get-NetTCPConnection e' disponibile su Windows moderni.
    $comando = Get-Command 'Get-NetTCPConnection' -ErrorAction SilentlyContinue
    if (-not $comando) { return $false }
    return [bool](Get-NetTCPConnection -LocalPort $porta -State Listen -ErrorAction SilentlyContinue)
}

function Avvia-Mailpit-Facoltativo {
    if (Porta-InUso 8025) {
        Write-Host 'Porta 8025 gia in uso: controlla Mailpit su http://127.0.0.1:8025'
        return
    }

    $comando = Get-Command 'mailpit.exe' -ErrorAction SilentlyContinue
    $eseguibile = $null
    if ($comando) {
        $eseguibile = $comando.Source
    } else {
        foreach ($candidato in @(
            (Join-Path $env:USERPROFILE 'Desktop\mailpit-windows-amd64\mailpit.exe'),
            (Join-Path $repo 'tools\mailpit.exe')
        )) {
            if (Test-Path -LiteralPath $candidato) {
                $eseguibile = $candidato
                break
            }
        }
    }

    if (-not $eseguibile) {
        Write-Host 'Mailpit non trovato: se vuoi provare le email UE-69 avvialo manualmente.' -ForegroundColor Yellow
        return
    }
    if (Porta-InUso 1025) {
        Write-Host 'La porta SMTP 1025 e gia occupata: non avvio un secondo Mailpit.' -ForegroundColor Yellow
        return
    }
    Start-Process -FilePath $eseguibile -ArgumentList @('--listen', '127.0.0.1:8025', '--smtp', '127.0.0.1:1025') -WorkingDirectory $repo
    Write-Host 'Avvio Mailpit richiesto. Interfaccia: http://127.0.0.1:8025'
}

function Esegui-Test {
    Prepara-Dipendenze
    Write-Host "`n=== TEST UNITARI PHPUNIT ===" -ForegroundColor Cyan
    & composer 'test:unit'
    if ($LASTEXITCODE -ne 0) { throw "Test non superati (codice $LASTEXITCODE)." }
    Write-Host 'Test unitari completati.' -ForegroundColor Green
}

function Ricrea-Database {
    Prepara-Dipendenze
    $cfg = Leggi-ConfigDb
    if ($cfg.DB_NAME -ne 'fitslot') {
        throw "Il file .env indica DB_NAME=$($cfg.DB_NAME), ma db:setup e' progettato per fitslot. Operazione annullata."
    }

    Write-Host "`n!!! ATTENZIONE: OPERAZIONE DISTRUTTIVA !!!" -ForegroundColor Red
    Write-Host 'composer db:setup ELIMINA COMPLETAMENTE il database locale fitslot.' -ForegroundColor Yellow
    Write-Host 'Poi ricrea fitslot ed esegue database/migrations/*.sql e database/seed/*.sql.'
    Write-Host 'TUTTI i dati attuali in fitslot andranno persi (non i file SQL su disco).'
    $conferma = Read-Host 'Per continuare digita esattamente RESET FITSLOT'
    if ($conferma -cne 'RESET FITSLOT') {
        Write-Host 'Operazione annullata. Nessun reset eseguito.' -ForegroundColor Yellow
        return
    }

    & composer 'db:setup'
    if ($LASTEXITCODE -ne 0) { throw "db:setup fallito (codice $LASTEXITCODE). Il database potrebbe essere incompleto." }
    Write-Host 'Database ricreato e SQL eseguiti.' -ForegroundColor Green
}

function Apri-MySql {
    $mysql = Trova-MySql
    if (-not $mysql) {
        throw 'mysql.exe non trovato. Apri il Terminale di Laragon, oppure aggiungi la cartella bin di MySQL al PATH.'
    }
    $cfg = Leggi-ConfigDb
    Write-Host "Apro MySQL: $($cfg.DB_USER)@$($cfg.DB_HOST):$($cfg.DB_PORT) / $($cfg.DB_NAME)" -ForegroundColor Cyan
    Write-Host 'Esci dalla console MySQL con il comando exit;'
    $argomenti = @(
        "--host=$($cfg.DB_HOST)",
        "--port=$($cfg.DB_PORT)",
        "--user=$($cfg.DB_USER)",
        '--default-character-set=utf8mb4'
    )
    if ($cfg.DB_PASSWORD -ne '') {
        # Il client chiede la password senza mostrarla nella riga di comando.
        $argomenti += '-p'
    }
    $argomenti += $cfg.DB_NAME
    & $mysql @argomenti
    if ($LASTEXITCODE -ne 0) { throw "Console MySQL terminata con errore (codice $LASTEXITCODE)." }
}

function Avvia-Progetto {
    Prepara-Dipendenze
    Write-Host 'Assicurati che MySQL sia acceso (per esempio in Laragon).'
    Avvia-Mailpit-Facoltativo

    if (Porta-InUso 8000) {
        Write-Host 'La porta 8000 e gia occupata: non avvio un secondo server PHP.' -ForegroundColor Yellow
    } else {
        Write-Host 'Apro il server in una nuova finestra. Per fermarlo usa Ctrl+C in quella finestra.'
        Start-Process -FilePath 'cmd.exe' -ArgumentList @('/k', 'composer start') -WorkingDirectory $repo
        Start-Sleep -Seconds 2
    }
    Start-Process 'http://127.0.0.1:8000'
}

while ($true) {
    Write-Host ''
    Write-Host '=============================================' -ForegroundColor Cyan
    Write-Host '            FITSLOT - MENU LOCALE' -ForegroundColor Cyan
    Write-Host '=============================================' -ForegroundColor Cyan
    Write-Host '  1) Esegui test unitari (PHPUnit)'
    Write-Host '  2) Pulisci e ricrea il database dagli SQL'
    Write-Host '  3) Apri la console MySQL nel terminale'
    Write-Host '  4) Avvia FitSlot in locale (+ Mailpit se presente)'
    Write-Host '  0) Esci'
    Write-Host '============================================='
    $scelta = Read-Host 'Scegli un numero'

    if ($scelta -eq '0') { break }
    try {
        switch ($scelta) {
            '1' { Esegui-Test; break }
            '2' { Ricrea-Database; break }
            '3' { Apri-MySql; break }
            '4' { Avvia-Progetto; break }
            default { Write-Host 'Scelta non valida.' -ForegroundColor Yellow }
        }
    } catch {
        Write-Host ("ERRORE: " + $_.Exception.Message) -ForegroundColor Red
    }
    Pausa-Menu
}
Write-Host 'Uscita dal menu FitSlot.'
