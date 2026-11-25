# FitSlot

Applicazione per la gestione di un centro sportivo, sviluppata dal team LadyTheLemons! per il progetto universitario di Agile.

## Avvio locale

Servono PHP 8.4 (da 8.4.1) con MySQLi, Composer 2 e MySQL 8.4.

1. Avvia MySQL ed esegui `composer install` dalla radice del progetto.
2. Se `.env` manca, crealo copiando `.env.example`. Imposta le credenziali del tuo MySQL locale e usa `DB_NAME=fitslot`.
3. Per preparare il database di sviluppo, esegui `composer db:setup`. **Il comando elimina tutti i dati di `fitslot` e ricrea il database.**
4. Esegui `composer start` e apri <http://127.0.0.1:8000>.

## Test

- `composer test:unit` esegue i test unitari senza MySQL.
- `composer test` esegue anche i test di integrazione. Usa un'istanza MySQL di test con `fitslot` già inizializzato e imposta `FITSLOT_RUN_DB_TESTS=1` per includere i test del listino.

[Account demo e ruoli](docs/UE-04_ACCOUNT_DEMO.md) · [Come contribuire](CONTRIBUTING.md)
