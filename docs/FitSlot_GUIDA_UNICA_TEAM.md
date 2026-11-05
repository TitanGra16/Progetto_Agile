# FitSlot — Guida unica del team

> **Progetto universitario Agile · Team LadyTheLemons! · Anno accademico 2026–2027**  
> **Versione:** 1.0 · 5 novembre 2026  
> **Destinatari:** tutti e quattro i componenti del gruppo  
> **Scopo:** installare l'ambiente, avviare il progetto, tenere allineati i database locali e collaborare con Git/GitHub in modo uniforme.
>
> **Questa guida NON contiene l'implementazione delle User Story.** Descrive il funzionamento tecnico del progetto, il flusso di lavoro e le regole del gruppo. I requisiti e i criteri di accettazione sono nel Product Backlog e nella documentazione funzionale.

## Indice

1. [Regole essenziali per tutti](#1-regole-essenziali-per-tutti)
2. [Che cos'è FitSlot e come è organizzato](#2-che-cosè-fitslot-e-come-è-organizzato)
3. [Software da installare](#3-software-da-installare)
4. [Prima configurazione: dalla repository al sito funzionante](#4-prima-configurazione-dalla-repository-al-sito-funzionante)
5. [Database locale, senza password e migration](#5-database-locale-senza-password-e-migration)
6. [Comandi per lavorare ogni giorno](#6-comandi-per-lavorare-ogni-giorno)
7. [Organizzazione del codice e delle cartelle](#7-organizzazione-del-codice-e-delle-cartelle)
8. [Git e GitHub: branch, commit, Pull Request](#8-git-e-github-branch-commit-pull-request)
9. [Test, qualità e GitHub Actions](#9-test-qualità-e-github-actions)
10. [Metodo di lavoro Agile e collaborazione tra quattro persone](#10-metodo-di-lavoro-agile-e-collaborazione-tra-quattro-persone)
11. [Sicurezza, file condivisi e cose da non fare](#11-sicurezza-file-condivisi-e-cose-da-non-fare)
12. [Problemi frequenti](#12-problemi-frequenti)
13. [Checklist per il primo giorno e per ogni Pull Request](#13-checklist-per-il-primo-giorno-e-per-ogni-pull-request)
14. [Decisioni e verifiche aperte sul setup](#14-decisioni-e-verifiche-aperte-sul-setup)

---

## 1. Regole essenziali per tutti

1. **Il repository GitHub esiste già.** Non eseguire `git init` e non creare un secondo repository.
2. **Il branch principale si chiama `Main` (M maiuscola).** Git distingue `Main` da `main`. Non rinominarlo senza un accordo del gruppo.
3. **Non sviluppare direttamente su `Main`.** Ogni User Story ha un branch dedicato e viene integrata tramite Pull Request (PR).
4. **Un branch per una User Story**, non un branch per ogni livello (`Controller`, `Service`, ecc.). Le attività tecniche usano branch `chore/...`.
5. **Prima di iniziare una nuova storia, partire da `Main` aggiornato.** Prima di integrare una PR, passare test e code review.
6. **Il database è locale per ciascuno.** GitHub condivide codice e script SQL, non i contenuti del database personale.
7. **`composer install` per chi scarica il progetto; `composer require` soltanto quando si decide di aggiungere una dipendenza.** Il file `composer.lock` va mantenuto e condiviso.
8. **`composer db:setup` e `composer start` sono separati.** Il primo può modificare lo schema del database; il secondo deve solo avviare il sito.
9. **Mai pubblicare `.env`, `vendor/`, password, dati personali veri, certificati medici, dump locali o cache.**
10. **Nessuna PR senza una verifica di almeno un altro membro del gruppo**, secondo le regole di collaborazione in `CONTRIBUTING.md`.

> **Nota sullo stato:** questa guida riporta l'architettura e il flusso verificati durante il setup del 5 novembre 2026. Il branch di setup è `chore/setup-iniziale`. Prima di considerarlo definitivo, verificare i punti aperti al §14 e integrare la relativa PR in `Main`.

## 2. Che cos'è FitSlot e come è organizzato

FitSlot è un'applicazione web per la gestione di un centro sportivo. I ruoli applicativi previsti sono **iscritto, receptionist, istruttore e responsabile/gestore**. Le funzionalità sono definite dalle User Story del Product Backlog; non vengono dettagliate né implementate qui.

### Tecnologie concordate

| Area | Tecnologia | A cosa serve |
|---|---|---|
| Backend | **PHP 8.4** | Logica dell'applicazione e pagine/server |
| Database | **MySQL 8.4**, InnoDB | Conserva i dati locali |
| Connessione al DB | **MySQLi** | Esegue query e prepared statement verso MySQL |
| Frontend | **HTML, CSS, JavaScript** | Interfaccia nel browser |
| Dipendenze e comandi | **Composer 2** | Installa librerie, PSR-4, script di progetto |
| Test | **PHPUnit 13** | Test unitari e, in seguito, d'integrazione |
| Variabili locali | **vlucas/phpdotenv** | Carica `.env`, non condiviso |
| Editor consigliato | **VS Code** | Modifica codice, terminale, Git |
| Versionamento | **Git + GitHub** | Branch, commit, PR, issue |
| CI | **GitHub Actions** | Esegue test automatici sulle PR |
| Ambiente Windows | **Laragon** | Gestisce PHP, MySQL e, se necessario, Apache |

PHPUnit 13 richiede PHP 8.4 o superiore: non usare PHP 8.3 per questo setup.

### Come passa una richiesta attraverso l'app

```text
Browser (HTML/CSS/JavaScript)
           |
           v
public/  ->  Controller  ->  Service  ->  Repository  ->  MySQLi  ->  MySQL
                           |                |
                         DataModel       query SQL
```

- **Controller:** riceve una richiesta e gestisce la risposta HTTP.
- **Service:** contiene le regole applicative e coordina le operazioni.
- **Repository:** esegue l'accesso ai dati con MySQLi, usando query parametrizzate.
- **DataModel:** rappresenta i dati e gli oggetti del dominio.
- **Config:** inizializzazione e configurazione comune, compreso l'accesso al database.

Questa separazione è uno standard tecnico del gruppo; non anticipa l'implementazione delle storie.

## 3. Software da installare

Queste istruzioni sono per **Windows 10/11**, come l'ambiente usato per il setup. Chi usa macOS/Linux può mantenere PHP, Composer, PHPUnit, Git e MySQL nelle stesse versioni, ma deve adattare i comandi di installazione e di terminale.

| Software | Download / installazione | Verifica nel terminale VS Code |
|---|---|---|
| **VS Code** | https://code.visualstudio.com/ | Aprire l'editor |
| **Git per Windows** | https://git-scm.com/install/windows | `git --version` |
| **Laragon** | https://laragon.org/download/ | Aprire Laragon; verificare PHP 8.4 e MySQL 8.4 |
| **Composer 2** | https://getcomposer.org/download/ | `composer --version` |
| **PHPUnit 13** | **Non installarlo globalmente**: arriva tramite `composer install` | `composer test:unit` |
| **PHP MySQLi** | Estensione di PHP 8.4; abilitare se necessario | `php -m` (deve elencare `mysqli`) |
| **Codex VS Code** | Facoltativo; estensione ufficiale OpenAI | Non necessario per eseguire FitSlot |

### 3.1 Preparare Laragon

1. Installare Laragon e aprirlo.
2. Selezionare **PHP 8.4** dal menu delle versioni PHP. Se non è disponibile, aggiungerlo da **Menu → Tools → Quick add**.
3. Selezionare **MySQL 8.4**. Nel progetto iniziale è stata verificata la versione 8.4.3.
4. Avviare **MySQL** da Laragon; il pulsante **Avvia tutto** va bene. Apache è facoltativo se si usa il server PHP integrato.
5. Usare **Menu → Tools → Path → Add Laragon to Path**, se disponibile; chiudere e riaprire VS Code.
6. Nel terminale di VS Code controllare:

```powershell
php -v
php -m
```

L'output di `php -v` deve indicare **PHP 8.4.x**. In `php -m` deve apparire `mysqli` e devono essere disponibili anche le estensioni richieste da Composer/PHPUnit.

### 3.2 Installare Composer correttamente

1. Scaricare **Composer-Setup.exe** dal sito ufficiale di Composer.
2. Quando l'installer chiede il percorso PHP, indicare il file `php.exe` della **versione 8.4 dentro Laragon**. Esempio indicativo: `C:\laragon\bin\php\php-8.4...\php.exe`.
3. Completare l'installazione e **riaprire VS Code**.
4. Verificare:

```powershell
composer --version
php -v
```

Non creare un nuovo `composer.json`: il progetto ne ha già uno condiviso.

### 3.3 Verificare Git

```powershell
git --version
```

Configurare l'identità Git se non è già stata impostata (usare i propri dati, non quelli di altri membri):

```powershell
git config --global user.name "Nome Cognome"
git config --global user.email "email-associata-al-proprio-account-GitHub"
```

## 4. Prima configurazione: dalla repository al sito funzionante

**Questa procedura vale per gli altri tre componenti dopo che il setup è stato integrato in `Main`.** Se sei l'autore del setup e sei ancora su `chore/setup-iniziale`, non cambiare branch né ricreare il progetto mentre hai modifiche non salvate.

### 4.1 Scaricare il repository esistente

Farsi aggiungere al repository GitHub del gruppo, aprire VS Code e scegliere **Terminal → New Terminal**. Posizionarsi nella cartella in cui si vuole tenere il progetto:

```powershell
git clone URL_DEL_REPOSITORY_GITHUB FitSlot
cd FitSlot
git switch Main
git pull --ff-only origin Main
```

Sostituire `URL_DEL_REPOSITORY_GITHUB` con l'URL **Copy/Clone** del repository reale. Il nome della cartella locale può essere diverso (`Progetto_Agile` è altrettanto valido). **Non usare `git init`.**

Se il progetto è già sul PC, basta aprire la cartella corretta, poi:

```powershell
git switch Main
git pull --ff-only origin Main
```

### 4.2 Installare le dipendenze

Dalla cartella in cui si trovano `composer.json` e `composer.lock`:

```powershell
composer install
composer validate --strict --no-check-publish
composer check-platform-reqs
composer test:unit
```

`composer install` usa le versioni registrate nel **`composer.lock`**, compreso PHPUnit. Non fare `composer init`, non installare PHPUnit manualmente, non eseguire `composer update` a ogni avvio.

### 4.3 Creare il file locale `.env`

In **PowerShell**:

```powershell
Copy-Item .env.example .env
```

Aprire `.env` in VS Code e adattare le credenziali al proprio MySQL. **Mai aggiungere `.env` a Git.**

Per il setup locale senza password, dopo aver adattato il codice come spiegato al §5, si usa:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=fitslot
DB_USER=root
DB_PASSWORD=
```

DB_PASSWORD puo essere vuota oppure contenere la password del proprio MySQL locale. Se la chiave viene omessa, il codice usa una stringa vuota. Il codice non modifica gli account MySQL e non richiede variabili aggiuntive per distinguere gli ambienti. Leggere il paragrafo 5.1.

### 4.4 Preparare il database e avviare FitSlot

Avviare MySQL in Laragon e poi, nel terminale del progetto:

```powershell
composer db:setup
composer test:unit
composer start
```

Aprire **http://127.0.0.1:8000** nel browser. Per interrompere il server, tornare nel terminale e premere `Ctrl + C`.

**Importante:** `composer start` avvia soltanto PHP e non elimina dati. MySQL va avviato separatamente in Laragon. `composer db:setup` e invece un reset distruttivo automatico: elimina tutte le tabelle e tutti i dati di fitslot senza richiedere conferma. Non eseguirlo per avviare semplicemente il sito.

## 5. Database locale, senza password e migration

### 5.1 Si può lavorare senza password?

**Sì, per il solo sviluppo sul proprio PC.** Il team può decidere di usare temporaneamente l'utente MySQL locale `root` senza password, se Laragon è configurato in questo modo.

È una **semplificazione accettata per il laboratorio**, non la configurazione da usare per un server accessibile via rete o per la produzione.

**Condizioni indispensabili:**

- Il database deve essere accessibile **soltanto da localhost** (`127.0.0.1`); non esporre la porta 3306 sulla rete e non attivare accesso remoto per `root`.
- Il sito locale deve essere avviato su `127.0.0.1`, non pubblicato su un host esterno.
- Ogni sviluppatore usa **il proprio database locale**.
- `.env` deve restare escluso da Git.
- DB_HOST, DB_PORT, DB_NAME e DB_USER restano obbligatorie e non vuote. DB_PASSWORD puo essere vuota, omessa o valorizzata.
- Per una demo condivisa, hosting o dati reali: creare un account MySQL dedicato con password e privilegi minimi.

**Regola applicata dal codice:** `Database::leggiConfigurazione()` richiede DB_HOST, DB_PORT, DB_NAME e DB_USER presenti e non vuote. DB_PASSWORD puo contenere una stringa vuota oppure la propria password; se omessa viene usata una stringa vuota. Non serve una variabile per distinguere gli ambienti e non esiste un fallback su root.

La stessa validazione e usata da `Database::connetti()` e da `scripts/db-setup.php`: sono ammessi soltanto gli host 127.0.0.1 e localhost e il nome esatto fitslot. I test unitari verificano la configurazione senza collegarsi a MySQL. `.env.example` contiene DB_NAME=fitslot, DB_USER=root e DB_PASSWORD vuota. Il file .env personale va adattato manualmente e non viene modificato dal codice.

### 5.2 MySQL non è GitHub

- Le **tabelle e i relativi aggiornamenti** sono condivisi come file in `database/migrations/`.
- I **dati di esempio condivisibili** si creano con file in `database/seed/`.
- I **dati che un compagno inserisce nel proprio MySQL** non si sincronizzano con `git push` e `git pull`.
- Avviare e arrestare Laragon **non deve cancellare il database**: MySQL salva i dati su disco.

### 5.3 Cosa fa composer db:setup

**ATTENZIONE: il database locale fitslot e tutti i suoi dati saranno eliminati.** Il comando e un reset completo, non un aggiornamento che conserva i dati.

Ogni sviluppatore usa il proprio database locale chiamato esattamente `fitslot`. Prima del reset verificare nel proprio .env DB_NAME=fitslot, DB_HOST=127.0.0.1 oppure localhost e le proprie credenziali. Non cambiare automaticamente il file personale copiandoci sopra il modello.

1. Lo script legge `Database::leggiConfigurazione()` e rifiuta host non locali e nomi diversi da fitslot.
2. Mostra l'avviso e procede automaticamente senza richiedere input.
3. Si collega a MySQL, elimina fitslot se presente e lo ricrea con charset utf8mb4.
5. Esegue prima tutti gli SQL di `database/migrations/`, poi quelli di `database/seed/`, in ordine alfabetico all'interno di ogni cartella.
6. Stampa il numero di file eseguiti e termina. Le tabelle e i dati di esempio vengono ricostruiti soltanto dagli SQL versionati.

Il setup iniziale non contiene SQL delle User Story. Questi file saranno aggiunti nei rispettivi branch, insieme alla funzionalita. Se mancano le cartelle o non ci sono SQL, il reset funziona comunque e lascia fitslot completamente vuoto.

Non vengono usati schema_migrations, checksum, storico delle esecuzioni o rollback. Anche un'eventuale vecchia tabella tecnica presente in fitslot viene eliminata insieme al database.

### 5.4 Regole per il reset e i file SQL

- Non eseguire il reset se ci sono dati da conservare: tutti i dati vengono cancellati definitivamente.
- Ogni reset esegue tutti i file da un database vuoto; non applica solo quelli nuovi.
- Le migration e i seed delle User Story appartengono ai rispettivi branch e devono ricostruire lo schema e gli esempi necessari in ordine corretto.
- Non eseguire due reset contemporaneamente.
- Se un SQL fallisce, il comando si ferma. Il vecchio database e gia stato eliminato e quello nuovo puo essere parziale: non esiste rollback.
- Dopo un git pull leggere le istruzioni della PR e decidere consapevolmente se ricostruire fitslot. Git non trasferisce i dati personali di MySQL.
- `composer start` avvia soltanto il server PHP e non elimina alcun dato.

### 5.5 Controlli manuali in sola lettura

Aprire il client MySQL dal terminale Laragon. Usare il proprio account; aggiungere `-p` se ha una password e inserirla al prompt.

```sql
SHOW DATABASES;
USE fitslot;
SHOW TABLES;
```

Il database locale deve chiamarsi fitslot. Senza SQL applicativi e normale non trovare tabelle dopo il reset. I controlli qui sopra leggono soltanto i metadati; non eseguono il reset.

### 5.6 Se serve `mysql` nel terminale VS Code

Trovare `mysql.exe` (per l'installazione Laragon standard):

```powershell
Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter mysql.exe -File
```

Aggiungere alle variabili utente Windows → `Path` **la cartella `bin` che contiene il file**, non il percorso completo del file. Chiudere e riaprire VS Code. Per una prova temporanea:

```powershell
$mysqlExe = Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter mysql.exe -File | Select-Object -First 1 -ExpandProperty FullName
$env:Path = "$(Split-Path $mysqlExe);$env:Path"
mysql --version
```

Se Laragon è installato altrove, sostituire `C:\laragon` con la posizione corretta.

## 6. Comandi per lavorare ogni giorno

### 6.1 All'inizio della giornata

1. Aprire il repository in VS Code.
2. Avviare MySQL in Laragon.
3. Verificare il branch attivo:

```powershell
git branch --show-current
git status
```

4. Se si sta per **iniziare una nuova User Story**, seguire §8.2 per partire da `Main` aggiornato.
5. Se si sta **continuando un branch già attivo**, non cambiare su `Main` indiscriminatamente: aggiornare il proprio branch quando necessario come al §8.5.
6. Se da `Main` sono arrivati nuovi SQL, leggere le istruzioni della PR. Installare le dipendenze se necessario. Eseguire il reset soltanto se si accetta di perdere tutti i dati locali: il comando successivo cancella automaticamente il database.

```powershell
composer install
composer db:setup
```

7. Avviare il sito:

```powershell
composer start
```

### 6.2 Comandi di riferimento

| Comando | Quando usarlo |
|---|---|
| `composer install` | Prima installazione e dopo aggiornamenti a `composer.lock` |
| `composer validate --strict --no-check-publish` | Dopo modifiche a Composer e prima della PR |
| `composer check-platform-reqs` | Controllare PHP e le estensioni richieste |
| `composer db:setup` | Solo per ricostruire fitslot da zero: elimina automaticamente tutti i dati |
| `composer start` | Avvia il server PHP locale senza modificare il DB |
| `composer test:unit` | Prima di commit e PR; test rapidi senza dipendenza dal DB |
| `git status` | Prima e dopo modifiche Git |
| `git pull --ff-only origin Main` | Aggiornare il branch `Main` locale |
| `git push -u origin nome-branch` | Pubblicare un branch la prima volta |

## 7. Organizzazione del codice e delle cartelle

```text
FitSlot/ (sul PC può chiamarsi Progetto_Agile)
├── .github/
│   └── workflows/         # GitHub Actions / CI
├── database/
│   ├── migrations/        # Evoluzioni dello schema, versionate
│   └── seed/              # Dati iniziali riproducibili
├── docs/                  # Documentazione tecnica e di progetto
├── public/
│   ├── index.php          # Punto di ingresso web
│   ├── css/
│   └── js/
├── scripts/
│   └── db-setup.php       # Setup / avanzamento del DB locale
├── sprints/
│   └── sprint_0/          # Artefatti e note Agile
├── src/
│   ├── Config/
│   ├── Controller/
│   ├── DataModel/
│   ├── Repository/
│   └── Service/
├── tests/
│   ├── Unit/
│   └── Integration/
├── bootstrap.php         # Autoload Composer + ambiente locale
├── composer.json          # Dipendenze, autoload, comandi
├── composer.lock          # Versioni esatte delle dipendenze
├── phpunit.xml            # Suite e impostazioni test
├── .env.example           # Modello pubblico senza segreti
├── .env                   # Configurazione personale: NON su Git
└── vendor/                # Librerie generate da Composer: NON su Git
```

**Namespace PSR-4** concordato: `Ladythelemons\Fitslot\` associato a `src/`.

Le cartelle vuote possono contenere un `.gitkeep`: Git non conserva le directory vuote da sole.

**Cosa NON va messo in questo setup:** implementazioni complete delle User Story, dati personali reali, enormi dump SQL o modifiche funzionali non approvate. Lo scopo del setup è creare un ambiente comune, non realizzare in anticipo tutte le funzionalità.

### Documentazione di riferimento

- **Questa guida:** onboarding tecnico e norme di collaborazione.
- **`CONTRIBUTING.md`:** regole ufficiali di contributo/review del repository, da rispettare se più restrittive.
- **Product Backlog condiviso:** ordine, descrizione, priorità, stime e criteri di accettazione delle User Story.
- **Documenti Scrum in `sprints/` e `docs/`:** pianificazione, avanzamento, review e retrospettive.
- **Vecchie guide tecniche:** se ancora presenti, potrebbero contenere esempi superati con **PDO** o con il branch `main`; per il setup attuale hanno priorità i file eseguibili del repository e questa guida aggiornata.

## 8. Git e GitHub: branch, commit, Pull Request

### 8.1 Concetti di base

- **Repository:** insieme dei file versionati del progetto.
- **`Main`:** versione ufficiale e condivisa.
- **Branch:** linea di lavoro isolata per una storia o attività.
- **Commit:** modifica salvata nella cronologia Git, con un messaggio descrittivo.
- **Push:** pubblica i commit del proprio branch su GitHub.
- **Pull Request (PR):** propone di integrare il proprio branch in `Main`, rendendo possibili review e test automatici.
- **Merge:** integrazione della PR, dopo approvazione e test.

### 8.2 Iniziare una User Story (da `Main` aggiornato)

Esempio generico: sostituire `UE-XX` e `descrizione-breve` con **l'identificativo e un titolo reale del backlog**.

```powershell
# Prima assicurarsi che le modifiche precedenti siano salvate
# o che il working tree sia pulito.
git status

git switch Main
git pull --ff-only origin Main
git switch -c feature/UE-XX-descrizione-breve
```

**Un branch contiene tutta la User Story**, anche se tocca Controller, Service, Repository, SQL, frontend e test. Non assegnare un branch per cartella o per strato architetturale.

Per un'attività comune di configurazione si usa un nome come `chore/setup-iniziale`; per un bug `fix/descrizione-breve`; per sola documentazione `docs/argomento`.

### 8.3 Fare modifiche e commit

Lavorare per cambiamenti piccoli, coerenti, che si possano spiegare e verificare.

```powershell
git status
git diff
composer test:unit

git add percorso/del/file1.php percorso/del/file2.php
git diff --cached --check
git diff --cached --stat

git commit -m "feat(UE-XX): breve descrizione della modifica"
```

**Preferire `git add` con file espliciti.** `git add .` è accettabile solo dopo aver controllato attentamente `git status` e `.gitignore`.

#### Convenzione dei messaggi di commit

Usare preferibilmente il formato `tipo(ambito): messaggio breve`, al presente e con un significato concreto.

| Tipo | Significato | Esempio senza dettaglio implementativo |
|---|---|---|
| `feat` | Avanzamento di una User Story | `feat(UE-XX): aggiunge flusso iniziale` |
| `fix` | Correzione di errore | `fix(UE-XX): corregge controllo di validazione` |
| `test` | Nuovi test o miglioramento test | `test(UE-XX): aggiunge casi limite` |
| `refactor` | Pulizia interna senza cambiare comportamento | `refactor(UE-XX): semplifica responsabilita` |
| `docs` | Documentazione | `docs: aggiorna guida team` |
| `chore` | Dipendenze, setup, CI o manutenzione | `chore: configura workflow CI` |

**Da evitare:** `update`, `fix`, `prova`, `ultimo`, `finito`, `commit finale`, commit enormi con attività non correlate. Non serve fare un commit per ogni riga; serve rendere la cronologia comprensibile.

### 8.4 Fare push e aprire la Pull Request

Prima pubblicazione del branch:

```powershell
git push -u origin feature/UE-XX-descrizione-breve
```

Dopo la prima pubblicazione, di solito basta `git push`.

Su GitHub: **Pull requests → New pull request** e impostare:

- **Base:** `Main` (destinazione).
- **Compare:** `feature/UE-XX-descrizione-breve` (il lavoro proposto).
- **Titolo:** `UE-XX — Titolo sintetico della storia`.
- **Descrizione:** collegamento all'Issue/User Story, obiettivo, modifiche ad alto livello, test eseguiti, eventuali migration, screenshot soltanto se utili, limiti noti.
- **Reviewer:** almeno **un altro membro** del gruppo.

Checklist sintetica per la descrizione PR:

```markdown
## Riferimento
- User Story / Issue: UE-XX / #numero

## Cosa cambia
- [Descrizione sintetica, senza incollare interi file]

## Verifiche
- [ ] composer test:unit
- [ ] Test manuale dei criteri di accettazione applicabili
- [ ] Nuove migration documentate (se presenti)
- [ ] Nessun segreto o file locale nel diff

## Revisione
- [ ] Almeno un reviewer del gruppo
- [ ] CI GitHub Actions superata
```

**Non fare merge da soli senza la revisione richiesta.** Se la CI fallisce, correggere nel proprio branch e fare un nuovo push; la PR si aggiornerà.

### 8.5 Aggiornare una User Story aperta quando cambia `Main`

Se la PR è ancora aperta e altri hanno integrato codice in `Main`:

```powershell
# Sul proprio branch di storia, con modifiche locali salvate:
git fetch origin
git merge origin/Main

# Risolvere eventuali conflitti in VS Code,
# eseguire i test, fare il commit del merge se richiesto:
composer test:unit
git push
```

Un conflitto non è un errore grave: significa che due rami hanno modificato parti incompatibili. Discutere la risoluzione con chi ha toccato gli stessi file. **Non usare `git push --force` senza accordo del team.**

### 8.6 Dopo il merge della PR

```powershell
git switch Main
git pull --ff-only origin Main
```

Se necessario, dopo aver verificato che il lavoro è integrato, cancellare il branch locale ormai concluso:

```powershell
git branch -d feature/UE-XX-descrizione-breve
```

La cancellazione del branch remoto tramite l'interfaccia GitHub è facoltativa e va fatta solo dopo il merge.

### 8.7 Regole speciali del branch di setup

Finché si lavora sul **setup iniziale**, usare il branch `chore/setup-iniziale` già esistente; **non aprire nuove User Story in quel branch**.

Prima di pubblicarlo:

```powershell
git branch --show-current
git status --short
composer validate --strict --no-check-publish
composer check-platform-reqs
composer test:unit
git diff --check
```

Controllare i file destinati al commit e poi:

```powershell
git add .
git diff --cached --check
git diff --cached --stat
git commit -m "chore: completa setup iniziale FitSlot"
git push -u origin chore/setup-iniziale
```

Aprire la PR con **base `Main`** e **compare `chore/setup-iniziale`**. Prima del merge aspettare esito della CI e review. Il codice supporta ora la password vuota nelle condizioni del paragrafo 5.1; la connessione locale e il runner restano da provare nel proprio ambiente.

## 9. Test, qualità e GitHub Actions

### 9.1 Test locali

```powershell
composer test:unit
```

I test unitari devono essere rapidi e, in generale, non dipendere da un MySQL avviato. I test d'integrazione, quando saranno aggiunti, avranno un database di prova separato da quello di sviluppo.

Il controllo del 5 novembre 2026 riportato da Codex ha verificato **10 test e 12 asserzioni superati** nel branch di setup. Non è una garanzia permanente: ogni PR deve rieseguire i test.

### 9.2 GitHub Actions

I workflow sono nella cartella **`.github/workflows/`** e devono terminare in `.yml` o `.yaml`.

La CI deve intercettare push e Pull Request diretti a **`Main`**, preparare **PHP 8.4**, installare le dipendenze con `composer install`, validare Composer e avviare i test unitari.

**Controllo aperto:** nell'ultima revisione Codex risultavano **due file YAML** (`php-tests.yml` e `php-test.yml`), uno dei quali descritto come workflow manuale storico. Il team deve verificare se entrambi sono necessari e, in caso contrario, rimuovere quello obsoleto tramite una PR. Il solo parsing YAML **non dimostra** che la CI funzioni: verificarne l'esecuzione reale su GitHub.

### 9.3 Quando una User Story è completata?

La **Definition of Done** minima è:

- Criteri di accettazione della User Story verificati.
- Codice compilabile/eseguibile, con separazione Controller/Service/Repository coerente.
- Test adeguati, eseguiti localmente, e CI verde.
- Nessuna regressione nota non documentata.
- Eventuali SQL/migration compresi nella PR e non distruttivi per sorpresa.
- Documentazione aggiornata dove cambia l'uso del progetto.
- PR revisionata da almeno un compagno e integrata in `Main`.

Una storia **non è Done** soltanto perché è stata codificata o perché il suo branch è stato pubblicato.

## 10. Metodo di lavoro Agile e collaborazione tra quattro persone

### 10.1 Fonti di verità

- **Product Backlog:** cosa va realizzato, priorità, criteri di accettazione e stime.
- **Sprint Backlog:** quali storie il team si impegna a completare nello Sprint corrente.
- **Issue GitHub:** traccia operativa delle attività e collegamento alle PR.
- **`Main`:** stato tecnico integrato del progetto.
- **Documenti del corso:** Scrum, presentazioni, note e consegne.

Gli Story Point non sono ore. Non impegnarsi in più storie di quante il gruppo possa realmente concludere e testare nello Sprint.

### 10.2 Flusso per ogni User Story

1. Il gruppo verifica che la storia sia sufficientemente chiara e stimata.
2. La storia viene selezionata per lo Sprint e associata a un responsabile operativo.
3. Si apre/collega un'Issue GitHub.
4. Si crea il branch `feature/UE-XX-...` a partire da `Main` aggiornato.
5. Si sviluppa **una storia completa**, comprese verifiche e test; collaborazione su uno stesso branch solo quando concordata.
6. Si fanno commit piccoli e si pubblica il branch.
7. Si apre PR con collegamento alla Issue e risultati dei test.
8. Un altro componente revisiona. Il codice viene migliorato fino a CI verde.
9. Si integra la PR in `Main`, si aggiorna la board e si chiude la storia solo quando rispetta la Definition of Done.

### 10.3 Riunioni e coordinamento

- **Sprint Planning:** decidere obiettivo e storie fattibili, chiarire dipendenze.
- **Daily breve:** cosa è cambiato, cosa si farà, impedimenti e necessità di collaborazione; non una riunione lunga.
- **Backlog Refinement:** chiarire criteri di accettazione, priorità, stime e dipendenze delle storie future.
- **Sprint Review:** mostrare funzionalità integrate, non branch personali incompleti.
- **Retrospettiva:** identificare miglioramenti concreti del modo di lavorare.

Consigli pratici per quattro persone: evitare che due componenti modifichino contemporaneamente gli stessi file senza coordinarsi; segnalare subito migrazioni SQL, modifiche a `composer.json` e cambiamenti all'interfaccia tra livelli; richiedere review presto, non soltanto l'ultimo giorno dello Sprint.

### 10.4 Chi gestisce cosa

I **ruoli applicativi** (iscritto, reception, istruttore, responsabile) non coincidono con i **ruoli Scrum** e non assegnano automaticamente compiti di programmazione. Le responsabilità operative del gruppo si concordano durante la pianificazione. Le PR vengono revisionate da qualcuno diverso dall'autore.

## 11. Sicurezza, file condivisi e cose da non fare

### File da pubblicare

- Codice sorgente in `src/`, `public/`, `scripts/` e test.
- `composer.json` **e** `composer.lock`.
- File SQL di `database/migrations/` e `database/seed/` senza dati sensibili.
- `.env.example` con soli valori di esempio e, per il solo laboratorio locale, password vuota.
- `phpunit.xml`, `bootstrap.php`, documentazione e workflow CI.

### File da NON pubblicare

- `.env` e qualsiasi credenziale reale.
- `vendor/` (si rigenera con `composer install`).
- `.phpunit.cache/`, `.phpunit.result.cache`, output di coverage.
- File IDE personali come `.vscode/settings.json`, salvo decisione esplicita del gruppo su impostazioni condivise e innocue.
- File di log, dump MySQL, backup, certificati medici o altri dati personali.

### Regole tecniche minime

- Usare **prepared statement MySQLi** per dati inseriti dagli utenti; evitare SQL costruito concatenando input.
- Gestire credenziali tramite `.env` e tenere il server web in locale finché non sarà progettata una configurazione adatta alla rete.
- Non inserire regole di business nei Controller o direttamente nel frontend.
- Per operazioni future che coinvolgono più scritture dipendenti, valutare transazioni e controlli di concorrenza.
- Non fare `DROP DATABASE`, `DROP TABLE`, `TRUNCATE` o import di dump senza coordinamento e backup.
- Non cambiare versione di PHP, PHPUnit o librerie individualmente senza aggiornare le istruzioni del team.

## 12. Problemi frequenti

| Problema | Causa probabile | Cosa fare |
|---|---|---|
| `php` non riconosciuto da VS Code | PHP di Laragon non nel PATH | Laragon → Tools → Path → Add Laragon to Path; riaprire VS Code |
| `php -v` mostra 8.3 | Versione sbagliata nel PATH | Selezionare PHP 8.4 in Laragon; controllare il percorso usato dal terminale |
| `composer` non riconosciuto | Composer non installato nel PATH | Installare Composer-Setup.exe usando `php.exe` 8.4; riaprire VS Code |
| `mysql` non riconosciuto da VS Code | `mysql.exe` non nel PATH | Usare il Terminale Laragon oppure aggiungere `...\mysql\...\bin` al PATH |
| `mysqli` assente | Estensione PHP non abilitata | Verificare `php -m` e la configurazione della versione PHP usata |
| `XML declaration allowed only at the start` | Spazi/BOM prima di `<?xml` | Salvare `phpunit.xml` UTF-8 senza BOM, con `<?xml` come primi caratteri |
| `composer db:setup` dice che manca `.env` | Non creato il file locale | `Copy-Item .env.example .env` e configurarlo |
| Accesso MySQL rifiutato | Account o password non corrispondenti alla configurazione MySQL locale | Verificare DB_USER e DB_PASSWORD nel proprio .env; il codice accetta la password vuota ma l'account MySQL deve consentirla |
| Database collegato ma tabella mancante | Migration non applicata sul DB locale | Controllare quali SQL sono effettivamente presenti; il setup iniziale non contiene ancora tabelle delle User Story |
| Nuova tabella del compagno non appare | `git pull` non copia il database | Scaricare la migration con Git e applicarla sul proprio MySQL |
| `git switch Main` non funziona | Branch `Main` locale non ancora creato o nome diverso | `git branch -a`; verificare `origin/Main` e creare il tracking branch se necessario |
| `git pull` dà conflitti | Modifiche locali o rami divergenti | Non cancellare nulla; salvare lavoro e risolvere con il compagno coinvolto |
| GitHub Actions non parte | Workflow fuori da `.github/workflows`, estensione errata o filtro `main` anziché `Main` | Verificare `.yml`, `branches: [Main]` e la pagina Actions/PR |
| `composer test:unit` passa ma il sito non si apre | Server non avviato oppure DB non configurato | Avviare MySQL e `composer start`; guardare errori del terminale |

## 13. Checklist per il primo giorno e per ogni Pull Request

### Nuovo componente del gruppo

- [ ] Ho accesso al repository GitHub e conosco il branch principale `Main`.
- [ ] Ho installato Git, VS Code, Laragon (PHP 8.4, MySQL 8.4) e Composer 2.
- [ ] `php -v`, `composer --version`, `git --version` funzionano e `php -m` include MySQLi.
- [ ] Ho clonato il repository **esistente** e sono su `Main` aggiornato.
- [ ] Ho eseguito `composer install` e `composer test:unit` senza errori.
- [ ] Ho creato `.env` da `.env.example` senza pubblicarlo.
- [ ] Ho avviato MySQL locale e preparato il database con il runner, dopo le verifiche necessarie.
- [ ] `composer start` apre il sito su `127.0.0.1:8000`.
- [ ] So creare un branch per una User Story e aprire una PR verso `Main`.

### Prima di ogni PR

- [ ] La PR contiene una sola storia/attività coerente.
- [ ] Branch e destinazione corretti (`feature/...` → `Main`, o `chore/...` → `Main`).
- [ ] Nessuna password, `.env`, cartella `vendor`, cache, dump o dato personale.
- [ ] Diff controllato (`git diff`, `git diff --cached --check`).
- [ ] Test unitari passati e criteri di accettazione applicabili verificati.
- [ ] Nuove migration documentate, non applicate di nascosto all'avvio del server.
- [ ] Descrizione PR con riferimento Issue/User Story.
- [ ] Revisione richiesta a un altro compagno.
- [ ] CI GitHub Actions verde prima del merge.

## 14. Decisioni e verifiche aperte sul setup

Alla data di stesura del documento, la revisione statica di Codex ha mostrato un setup molto vicino al completamento. **Non trasformare questi punti aperti in affermazioni già verificate:**

1. **Password vuota:** supportata insieme alle password valorizzate, senza variabili aggiuntive per gli ambienti. Ogni compagno configura il proprio MySQL locale. La connessione effettiva e il runner restano da provare nel proprio ambiente; i test unitari controllano la validazione senza usare MySQL.
2. **Reset completo:** composer db:setup elimina e ricrea solo fitslot su 127.0.0.1 o localhost, senza conferma. Esegue tutti gli SQL del repository senza storico o rollback. Non e un comando da avviare automaticamente.
3. **Workflow:** controllare se entrambi i file `.github/workflows/php-tests.yml` e `.github/workflows/php-test.yml` siano davvero necessari. Rimuovere il workflow obsoleto solo dopo revisione.
4. **Test reale della CI:** il file YAML era stato analizzato staticamente, ma **l'esecuzione effettiva su GitHub** andrà verificata sulla PR del setup.
5. **Prova del runner su ambiente pulito:** oltre ai test unitari, verificare quando possibile `composer db:setup` sul proprio fitslot locale solo dopo aver salvato gli eventuali dati importanti. Non usare il comando per una semplice verifica di connessione.
6. **Documentazione storica:** una guida precedente conteneva esempi PDO e comandi `main` minuscolo; non usarla per il setup attuale senza allinearla. Questa guida usa **MySQLi** e **`Main`**.

### Come verificare che il setup sia pronto da pubblicare

Sul branch `chore/setup-iniziale`:

```powershell
git branch --show-current
composer validate --strict --no-check-publish
composer check-platform-reqs
composer test:unit
git status --short
git diff --check
```

Verificare inoltre manualmente che il sito locale si colleghi a MySQL, che le protezioni del reset siano coperte dai test; provare il reset reale solo quando si accetta la perdita dei dati locali e che i segreti non entrino nel commit. Dopo il push, confermare la CI verde e ottenere la review richiesta prima del merge.

---

**In una frase:** FitSlot si sviluppa con **PHP 8.4 + MySQLi + Composer + PHPUnit**; ciascuno lavora sul proprio MySQL locale; **migration e Git** tengono allineata la struttura; **un branch per User Story, una PR con test e review** tiene allineato il codice.

**Riferimenti ufficiali:** [Laragon](https://laragon.org/docs) · [Composer](https://getcomposer.org/download/) · [PHPUnit](https://phpunit.de/supported-versions.html) · [Git](https://git-scm.com/doc) · [GitHub Pull Requests](https://docs.github.com/en/pull-requests) · [GitHub Actions](https://docs.github.com/en/actions)
