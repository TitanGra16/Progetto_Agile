# Progetto Agile

Repository del progetto per l'esame di Agile.

## Obiettivo

Questo repository raccoglie codice, documentazione e artefatti del progetto del gruppo. La gestione del lavoro segue un flusso iterativo: backlog ordinato, sprint, review, retrospettiva e integrazione continua delle modifiche.

## Gruppo

| Ruolo | Membro |
| --- | --- |
| Product Owner | `Daniele Di Pompeo` |
| Scrum Master | `Matteo Tessari` |
| Developer | `Giuseppe Maglione` |
| Developer | `Anna Maddaloni` |
| Developer | `Marco Marinanza` |

## FitSlot: ambiente locale Windows e Laragon

Il setup iniziale usa PHP 8.4 (almeno 8.4.1 per le dipendenze bloccate), MySQL 8.4, MySQLi, Composer 2 e PHPUnit 13. Le funzionalita applicative sono ancora da sviluppare. Branch stabile: `Main`; branch del setup: `chore/setup-iniziale`.

1. In Laragon selezionare PHP 8.4 e MySQL 8.4, quindi avviare MySQL. Verificare che sia MySQL, non MariaDB.
2. Aprire il terminale Laragon, oppure configurare il PATH di Windows e riaprire il terminale di VS Code.
3. Dalla radice del repository controllare:

```powershell
where.exe php
php -v
php --ini
php -m
composer --version
mysql --version
```

Abilitare nel php.ini della CLI le estensioni richieste da Composer: in particolare mysqli, dom, xml, xmlwriter, tokenizer e le altre indicate da `composer check-platform-reqs`; mbstring e consigliata (il lock include anche il relativo polyfill). La versione del client MySQL non certifica quella del server: controllare anche `SELECT VERSION();` dal client locale.

## Installazione e configurazione

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
```

Copiare il modello solo se `.env` non esiste: non sovrascrivere la configurazione locale. DB_HOST, DB_PORT, DB_NAME e DB_USER devono essere presenti e non vuote. Anche la chiave DB_PASSWORD deve essere presente, ma puo contenere una stringa vuota (`DB_PASSWORD=`) oppure la password del proprio account MySQL. Ogni compagno usa le credenziali del proprio MySQL locale in Laragon. Non servono variabili aggiuntive per distinguere gli ambienti. Il codice non cambia la password MySQL e non sceglie un account automaticamente.

Usare `composer install`, non `composer update`, per installare le versioni del lock condiviso. `bootstrap.php` carica `vendor/autoload.php` e phpdotenv. Il namespace PSR-4 e `Ladythelemons\Fitslot\`, associato a `src/`.


## Setup esplicito del database

```powershell
composer db:setup
```

**A ogni esecuzione, `composer db:setup` elimina il database locale `fitslot` con tutti i suoi dati e lo ricrea.** Verifica la configurazione e fai un backup dei dati importanti prima di eseguire il comando. Installazione, avvio del server e test unitari non eseguono il reset.

Lo script usa la configurazione dell'applicazione. Esegue prima i file SQL di `database/migrations/` e poi quelli di `database/seed/`, in ordine di nome all'interno di ciascuna cartella. Esegui un solo setup alla volta.

### Errori SQL

Un errore interrompe il comando con codice 1 e indica l'operazione in corso. I messaggi non mostrano credenziali o contenuto delle query. I DDL MySQL possono lasciare modifiche parziali: non ritentare alla cieca dopo un errore e non presumere un rollback automatico. Verificare lo schema prima di proseguire.

L'eventuale vecchia tabella `schema_migrations` non viene consultata, aggiornata o eliminata. Le precedenti opzioni per registrare le impronte non sono piu disponibili: usare soltanto `composer db:setup`, senza opzioni.

## Avvio e test

```powershell
composer start
```

Avvia soltanto il server PHP su http://127.0.0.1:8000 con document root public. Arrestare con Ctrl+C. La pagina iniziale verifica il collegamento al DB; il server integrato e destinato allo sviluppo locale.

```powershell
composer validate --strict --no-check-publish
composer check-platform-reqs
composer test:unit
composer test
```

I test unitari controllano autoload e validazione della configurazione usando valori fittizi: non richiedono MySQL e non leggono le credenziali per collegarsi. `composer test` esegue tutte le suite; Integration contiene per ora solo un segnaposto. In futuro i test d'integrazione dovranno usare un database di test separato.

## Git, CI e Pull Request

La CI automatica e `.github/workflows/php-tests.yml`: PHP 8.4, Composer validate, installazione dal lock e test unitari su push e PR verso Main, senza setup DB. Il precedente php-test.yml e conservato come workflow solo manuale per evitare doppie esecuzioni automatiche; potra essere rimosso in una pulizia autorizzata.

Versionare composer.json, composer.lock, .env.example, bootstrap, sorgenti, test, configurazione PHPUnit, SQL e workflow. Non versionare .env, vendor, cache PHPUnit o credenziali. Le impostazioni VS Code restano locali: settings.json contiene attualmente solo un'opzione del terminale Python, non necessaria a FitSlot. Per condividere impostazioni utili al team concordare un insieme minimo senza percorsi personali e modificare esplicitamente la regola di esclusione.

Prima della PR verificare i comandi sopra, git status e il diff da proporre. La destinazione e Main (maiuscola). Collegare la issue e ottenere la revisione di un altro membro secondo CONTRIBUTING.md. Il setup non completa le User Story applicative.

### Convenzioni di leggibilita

Usiamo nomi italiani per variabili, metodi e messaggi scritti dal gruppo, per esempio `leggiConfigurazione()`, `connetti()` e `$connessione`. Manteniamo i nomi richiesti da PHP, PHPUnit e MySQLi. I test sono casi separati e non richiedono MySQL.

Il setup resta un comando esplicito, separato dall'avvio del sito.

### Account demo e ruoli

Le regole degli account demo della UE-04, compresi ruoli, password locale e attivazione, sono raccolte in [`docs/UE-04_ACCOUNT_DEMO.md`](docs/UE-04_ACCOUNT_DEMO.md).
