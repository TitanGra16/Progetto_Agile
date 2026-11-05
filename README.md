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

### Utenza MySQL locale dedicata

Per il laboratorio si puo usare il proprio account MySQL locale, con o senza password. Configurare DB_USER e DB_PASSWORD di conseguenza nel proprio .env. Se si preferisce creare un'utenza dedicata, le istruzioni SQL seguenti sono da eseguire manualmente da una sessione amministrativa locale aperta con `mysql -u root -p`. Sostituire il segnaposto con la password scelta per quell'account, senza inserirla in file versionati.

```sql
CREATE DATABASE IF NOT EXISTS fitslot_dev
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fitslot_local'@'127.0.0.1'
    IDENTIFIED BY '<PASSWORD_LOCALE_DA_SCEGLIERE>';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES
    ON fitslot_dev.* TO 'fitslot_local'@'127.0.0.1';
```

Sono nomi di esempio coerenti con .env.example: adattarli al database scelto. Usare DB_HOST=127.0.0.1 per l'account dell'esempio. Se l'account esiste gia, verificarlo e gestirlo con l'amministratore invece di ricrearlo. I privilegi sono limitati a questo database e comprendono i DDL necessari al setup locale e all'estensione del registro; nessun privilegio globale o DROP e richiesto dagli SQL attuali. Per un ambiente distribuito separare utenza di migration e utenza applicativa: quest'ultima necessita normalmente soltanto di SELECT, INSERT, UPDATE e DELETE. Eventuali future migration possono richiedere una revisione esplicita dei permessi.

## Setup esplicito del database

```powershell
composer db:setup
```

Questo e un comando che modifica il database configurato: eseguirlo consapevolmente, dopo verifica della configurazione e backup dei dati importanti. Non viene eseguito da installazione, avvio del server o test unitari.

Il runner acquisisce un lock MySQL esclusivo per database, senza attesa: un secondo setup si ferma. Il lock copre anche preparazione del registro e DDL ed e rilasciato chiudendo la connessione, anche in caso di errore. Protegge i processi che usano questo runner sullo stesso server; non blocca importazioni manuali o vecchie copie dello script.

Vengono applicati prima i file di database/migrations, poi quelli di database/seed, ordinati per nome. I file iniziali sono:

- `database/migrations/01-create_utenti.sql`;
- `database/seed/01-ruoli.sql`.

Il registro `schema_migrations` conserva filename e applied_at e viene esteso, senza ricrearlo, con checksum SHA-256 e state. I checksum normalizzano LF/CRLF per rendere coerenti Windows e Linux. I file gia applicati vengono verificati prima di eseguire quelli pendenti: modifiche, rimozioni o rinominazioni bloccano il setup. Aggiungere nuove migration per le modifiche allo schema; non riscrivere quelle gia condivise.

### Compatibilita con il registro precedente

Le righe preesistenti senza checksum non vengono considerate verificate automaticamente. Il setup si ferma dopo aver esteso il registro. Prima della baseline:

1. Confrontare gli SQL con la revisione usata per il setup originario e verificare schema, vincoli e ruoli effettivi con il team.
2. Fare un backup e chiarire eventuali differenze senza cancellare o ricreare tabelle.
3. Solo dopo la verifica, eseguire esplicitamente:

```powershell
composer db:setup -- --registra-impronte
```

La baseline aggiorna soltanto i checksum mancanti nel registro (e, se necessario, ne prepara la struttura); non esegue migration o seed, nemmeno quelli pendenti. Non puo provare retroattivamente quale SQL sia stato eseguito. Dopo la baseline, un successivo `composer db:setup` verifica i file e applica gli eventuali pendenti.

### Errori e recupero manuale

Prima dell'esecuzione SQL viene registrato state=running; soltanto dopo tutti i risultati senza errori o warning lo stato passa ad applied. Errori SQL anche nelle istruzioni successive di un file, warning e checksum discordanti interrompono il comando con codice 1. Non vengono mostrati messaggi SQL contenenti dati o dettagli di connessione. Un file rimasto running blocca anche il successivo avvio del setup.

**Non ritentare alla cieca.** CREATE/ALTER TABLE e altri DDL MySQL causano commit impliciti: un file con piu istruzioni puo lasciare modifiche parziali. Anche DML gia eseguiti possono essere persistiti. Un crash dopo l'SQL ma prima dell'aggiornamento del registro lascia prudenzialmente running. Non cancellare la riga e non impostarla applied senza verificare il risultato. Controllare schema, registro e backup con il team e concordare una riparazione specifica; lo script non effettua rollback o recuperi automatici. Lo stesso controllo vale per vecchi errori avvenuti prima dell'introduzione di state.

Riferimenti: [lock MySQL](https://dev.mysql.com/doc/refman/8.4/en/locking-functions.html), [commit impliciti dei DDL](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html).

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

Usiamo nomi italiani per variabili, metodi e messaggi scritti dal gruppo, per esempio `leggiConfigurazione()`, `connetti()` e `$connessione`. Manteniamo i nomi richiesti da PHP, PHPUnit e MySQLi e quelli del registro SQL gia condiviso. `checksum` indica l'impronta del file; `state` distingue un file completato (`applied`) da uno iniziato ma non completato (`running`). I test sono casi separati e non richiedono MySQL.

L'opzione italiana `--registra-impronte` sostituisce il nome mostrato in precedenza; `--baseline-existing` resta accettata come alias per compatibilita.
