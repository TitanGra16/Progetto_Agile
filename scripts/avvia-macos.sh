#!/usr/bin/env bash
set -euo pipefail

# Si posiziona nella cartella principale del repository.
REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO"

for cmd in php composer; do
  if ! command -v "$cmd" >/dev/null 2>&1; then
    echo "Errore: comando '$cmd' non trovato. Installa/configura PHP e Composer." >&2
    exit 1
  fi
done

if [[ ! -f .env ]]; then
  if [[ ! -f .env.example ]]; then
    echo 'Errore: manca sia .env sia .env.example.' >&2
    exit 1
  fi
  cp .env.example .env
  echo 'Creato .env da .env.example: controlla i parametri MySQL.'
fi

if [[ ! -f vendor/autoload.php ]]; then
  echo 'Installazione dipendenze Composer...'
  composer install
fi

# Mailpit e' facoltativo, utile per la UE-69.
MAILPIT_PID=''
if command -v mailpit >/dev/null 2>&1; then
  if ! curl -fsS --max-time 1 'http://127.0.0.1:8025' >/dev/null 2>&1; then
    mailpit >/dev/null 2>&1 &
    MAILPIT_PID="$!"
    echo 'Mailpit avviato: http://127.0.0.1:8025'
  else
    echo 'Mailpit risulta gia avviato.'
  fi
else
  echo 'Mailpit non installato: le email di prova non saranno disponibili.'
fi

cleanup() {
  if [[ -n "$MAILPIT_PID" ]]; then
    kill "$MAILPIT_PID" 2>/dev/null || true
  fi
}
trap cleanup EXIT

# NON esegue composer db:setup, che eliminerebbe il DB locale.
echo 'Assicurati che MySQL sia gia avviato.'
echo 'FitSlot: http://127.0.0.1:8000 (Ctrl+C per fermare il server)'
(sleep 2; open 'http://127.0.0.1:8000' >/dev/null 2>&1 || true) &
composer start
