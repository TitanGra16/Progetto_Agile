#!/usr/bin/env bash
set -uo pipefail

# Mettere questo file in scripts/avvia-macos.sh.
# Il menu lavora sempre dalla cartella principale del repository FitSlot.
REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)" || exit 1
if [[ ! -f "$REPO/composer.json" ]]; then
  echo 'ERRORE: composer.json non trovato. Metti lo script nella cartella scripts/ di FitSlot.' >&2
  exit 1
fi
cd "$REPO" || exit 1

MAILPIT_PID=''
cleanup() {
  if [[ -n "$MAILPIT_PID" ]]; then
    kill "$MAILPIT_PID" 2>/dev/null || true
  fi
}
trap cleanup EXIT

pausa_menu() {
  read -r -p 'Premi INVIO per tornare al menu ' _ || true
}

verifica_comando() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "ERRORE: comando '$1' non trovato nel PATH." >&2
    return 1
  fi
}

prepara_env() {
  if [[ ! -f .env ]]; then
    if [[ ! -f .env.example ]]; then
      echo 'ERRORE: mancano .env e .env.example.' >&2
      return 1
    fi
    cp .env.example .env || return 1
    echo 'Creato .env da .env.example. Controlla le credenziali MySQL.'
  fi
}

prepara_dipendenze() {
  verifica_comando php || return 1
  verifica_comando composer || return 1
  prepara_env || return 1
  if [[ ! -f vendor/autoload.php ]]; then
    echo 'Dipendenze non presenti: eseguo composer install...'
    composer install || return 1
  fi
}

# Legge solo le chiavi richieste, senza eseguire il contenuto di .env.
leggi_env() {
  local chiave="$1" predefinito="$2" riga valore
  valore="$predefinito"
  if [[ -f .env ]]; then
    while IFS= read -r riga || [[ -n "$riga" ]]; do
      riga="${riga%$'\r'}"
      if [[ "$riga" =~ ^[[:space:]]*${chiave}[[:space:]]*=(.*)$ ]]; then
        valore="${BASH_REMATCH[1]}"
        valore="${valore#"${valore%%[![:space:]]*}"}"
        valore="${valore%"${valore##*[![:space:]]}"}"
        if [[ ${#valore} -ge 2 ]]; then
          if [[ "$valore" == \"*\" || "$valore" == \'*\' ]]; then
            valore="${valore:1:${#valore}-2}"
          fi
        fi
      fi
    done < .env
  fi
  printf '%s' "$valore"
}

esegui_test() {
  prepara_dipendenze || return 1
  echo ''
  echo '=== TEST UNITARI PHPUNIT ==='
  if composer test:unit; then
    echo 'Test unitari completati.'
  else
    echo 'ERRORE: uno o piu test non sono stati superati.' >&2
    return 1
  fi
}

ricrea_database() {
  prepara_dipendenze || return 1
  local db_nome conferma
  db_nome="$(leggi_env DB_NAME fitslot)"
  if [[ "$db_nome" != 'fitslot' ]]; then
    echo "ERRORE: .env indica DB_NAME=$db_nome, ma db:setup e' progettato per fitslot." >&2
    return 1
  fi

  echo ''
  echo '!!! ATTENZIONE: OPERAZIONE DISTRUTTIVA !!!'
  echo 'composer db:setup ELIMINA COMPLETAMENTE il database locale fitslot.'
  echo 'Poi lo ricrea eseguendo database/migrations/*.sql e database/seed/*.sql.'
  echo 'TUTTI i dati attuali in fitslot andranno persi (non i file SQL su disco).'
  read -r -p 'Per continuare digita esattamente RESET FITSLOT: ' conferma || return 1
  if [[ "$conferma" != 'RESET FITSLOT' ]]; then
    echo 'Operazione annullata. Nessun reset eseguito.'
    return 0
  fi

  if composer db:setup; then
    echo 'Database ricreato e SQL eseguiti.'
  else
    echo 'ERRORE: db:setup fallito. Il database potrebbe essere incompleto.' >&2
    return 1
  fi
}

apri_mysql() {
  local mysql_cli='' host porta utente database password
  if command -v mysql >/dev/null 2>&1; then
    mysql_cli="$(command -v mysql)"
  elif command -v mariadb >/dev/null 2>&1; then
    mysql_cli="$(command -v mariadb)"
  elif [[ -x /opt/homebrew/opt/mysql-client/bin/mysql ]]; then
    mysql_cli='/opt/homebrew/opt/mysql-client/bin/mysql'
  elif [[ -x /usr/local/opt/mysql-client/bin/mysql ]]; then
    mysql_cli='/usr/local/opt/mysql-client/bin/mysql'
  else
    echo 'ERRORE: client mysql non trovato. Installa mysql-client o aggiungilo al PATH.' >&2
    return 1
  fi

  prepara_env || return 1
  host="$(leggi_env DB_HOST 127.0.0.1)"
  porta="$(leggi_env DB_PORT 3306)"
  utente="$(leggi_env DB_USER root)"
  database="$(leggi_env DB_NAME fitslot)"
  password="$(leggi_env DB_PASSWORD '')"
  echo "Apro MySQL: $utente@$host:$porta / $database"
  echo 'Per uscire dalla console MySQL scrivi exit;'
  local -a args=("--host=$host" "--port=$porta" "--user=$utente" '--default-character-set=utf8mb4')
  if [[ -n "$password" ]]; then
    args+=('-p') # Il client chiede la password senza mostrarla nella riga di comando.
  fi
  args+=("$database")
  "$mysql_cli" "${args[@]}"
}

porta_in_uso() {
  local porta="$1"
  if command -v lsof >/dev/null 2>&1; then
    lsof -nP -iTCP:"$porta" -sTCP:LISTEN 2>/dev/null | grep -q LISTEN
  else
    return 1
  fi
}

avvia_mailpit_facoltativo() {
  if porta_in_uso 8025; then
    echo 'La porta 8025 e gia occupata. Controlla http://127.0.0.1:8025'
    return 0
  fi
  if ! command -v mailpit >/dev/null 2>&1; then
    echo 'Mailpit non trovato nel PATH: puoi avviarlo manualmente per la UE-69.'
    return 0
  fi
  if porta_in_uso 1025; then
    echo 'Porta SMTP 1025 gia occupata: non avvio un secondo Mailpit.'
    return 0
  fi
  mailpit --listen '127.0.0.1:8025' --smtp '127.0.0.1:1025' >/dev/null 2>&1 &
  MAILPIT_PID="$!"
  echo 'Avvio Mailpit richiesto: http://127.0.0.1:8025'
}

avvia_progetto() {
  prepara_dipendenze || return 1
  echo 'Assicurati che MySQL sia acceso.'
  avvia_mailpit_facoltativo
  if porta_in_uso 8000; then
    echo 'La porta 8000 e gia occupata: non avvio un secondo server PHP.'
    open 'http://127.0.0.1:8000' >/dev/null 2>&1 || true
    return 0
  fi
  echo 'FitSlot: http://127.0.0.1:8000'
  echo 'Il server resta attivo in questo terminale: Ctrl+C per fermarlo.'
  (sleep 2; open 'http://127.0.0.1:8000' >/dev/null 2>&1 || true) &
  composer start
}

while true; do
  echo ''
  echo '============================================='
  echo '            FITSLOT - MENU LOCALE'
  echo '============================================='
  echo '  1) Esegui test unitari (PHPUnit)'
  echo '  2) Pulisci e ricrea il database dagli SQL'
  echo '  3) Apri la console MySQL nel terminale'
  echo '  4) Avvia FitSlot in locale (+ Mailpit se presente)'
  echo '  0) Esci'
  echo '============================================='
  read -r -p 'Scegli un numero: ' scelta || break
  case "$scelta" in
    0) break ;;
    1) esegui_test || true ;;
    2) ricrea_database || true ;;
    3) apri_mysql || true ;;
    4) avvia_progetto || true ;;
    *) echo 'Scelta non valida.' ;;
  esac
  pausa_menu
done

echo 'Uscita dal menu FitSlot.'
