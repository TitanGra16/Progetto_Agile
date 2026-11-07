$ErrorActionPreference = 'Stop'

# Parte dalla cartella principale del repository, indipendentemente
# da dove viene avviato lo script.
$repo = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repo

foreach ($cmd in @('php', 'composer')) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        Write-Error "Comando '$cmd' non trovato. Configura PHP e Composer nel PATH."
        exit 1
    }
}

if (-not (Test-Path '.env')) {
    if (-not (Test-Path '.env.example')) {
        Write-Error 'Manca sia .env sia .env.example.'
        exit 1
    }
    Copy-Item '.env.example' '.env'
    Write-Warning 'Creato .env da .env.example: controlla i parametri MySQL prima di usare FitSlot.'
}

if (-not (Test-Path 'vendor/autoload.php')) {
    Write-Host 'Installazione dipendenze Composer...'
    composer install
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

# Mailpit e' facoltativo per UE-01/02/03, ma utile per UE-69.
$mailpit = Get-Command 'mailpit' -ErrorAction SilentlyContinue
if ($mailpit) {
    $running = Get-Process -Name 'mailpit' -ErrorAction SilentlyContinue
    if (-not $running) {
        Start-Process -FilePath $mailpit.Source -WorkingDirectory $repo
        Write-Host 'Mailpit avviato: http://127.0.0.1:8025'
    } else {
        Write-Host 'Mailpit risulta gia avviato.'
    }
} else {
    Write-Host 'Mailpit non trovato nel PATH: le email di prova non saranno disponibili.'
}

# Non esegue composer db:setup: quel comando cancella il DB locale!
Write-Host 'Assicurati che MySQL sia gia avviato (es. tramite Laragon).'
Write-Host 'Apertura server PHP in una nuova finestra...'
Start-Process -FilePath 'cmd.exe' -ArgumentList @('/k', 'composer start') -WorkingDirectory $repo
Start-Sleep -Seconds 2
Start-Process 'http://127.0.0.1:8000'
