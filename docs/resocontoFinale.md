# Resoconto Completo Progetto P3 · FitSlot
**Corso:** Metodi di sviluppo agile (DT-0540) – A.A. 2026-2027  
**Data lezione Q&A con il Product Owner (Prof. Di Pompeo):** Giovedì 22 Ottobre 2026  
**Scadenza consegna Product Backlog:** 29 Ottobre 2026 (file `GruppoXX.xlsx`)  
**Scadenze Sprint:** S1 (12 Nov) · S2 (26 Nov) · S3 (10 Dic) · S4 (Gennaio)

---

## 1. Scopo di questo documento
Questo documento unisce le specifiche iniziali della traccia **P3 · FitSlot** con tutti i chiarimenti, i vincoli aggiuntivi e i casi limite emersi durante la sessione di domande col Product Owner (inclusi i chiarimenti raccolti dagli altri gruppi). Serve come punto di riferimento unico per il nostro team per:
1. Fissare i parametri lasciati a nostra discrezione dal docente.
2. Definire e approvare **Temi, Epiche e User Stories** (con relativi criteri di accettazione e stime).
3. Verificare che ogni storia rispetti la checklist **INVEST** prima di compilare il template Excel.

---

## 2. Il Sistema e gli Attori: Traccia vs Chiarimenti del PO

### Come cambia la visione generale
* **Applicazione Unica:** Il sistema è un'applicazione desktop/web con **un'unica interfaccia** a cui tutti gli utenti accedono tramite login con le proprie credenziali. Non è un sistema usato solo dalla reception: anche gli iscritti e gli istruttori accedono autonomamente.
* **Utenti Multi-Ruolo (Importante per il modello dati):** Un singolo utente registrato può ricoprire **più ruoli contemporaneamente** (esempio esplicito del prof: *un istruttore della palestra può essere a sua volta un iscritto che prenota corsi o usa la piscina*).

### Riepilogo dei 4 Ruoli
1. **Iscritto:** Carica il certificato medico, acquista/paga abbonamenti, prenota e cancella corsi o ingressi in sala pesi/piscina, si mette in lista d'attesa, conferma/rifiuta i subentri, consulta lo stato di abbonamento, crediti e scadenze.
2. **Reception:** Registra gli utenti, valida i certificati medici (controllo visivo/scansione una tantum), registra pagamenti in sede, congela abbonamenti per infortunio, registra **sia le entrate (check-in) sia le uscite (check-out)** fisiche dal centro.
3. **Istruttore:** Insegna uno o più corsi (come titolare o come supplente), comunica disponibilità, vede gli iscritti ai propri corsi, segnala assenze/rinunce tramite app e accetta eventuali supplenze.
4. **Gestore:** Configura il palinsesto fisso dei corsi (con titolari, supplenti, capienza massima e soglia minima di iscritti), definisce i listini degli abbonamenti, monitora affluenza (centro pieno/vuoto), incassi e storico dei corsi annullati.

---

## 3. Regole di Dominio Consolidate (Traccia + Risposte del Prof)

Qui sotto trovate tutte le regole di business definitive, divise per area funzionale.

### A. Certificato Medico e Notifiche di Scadenza
* **Caricamento e Validazione:** L'iscritto può caricare il file del certificato sull'app oppure consegnare il cartaceo in reception. È prevista una verifica/scansione da parte della segreteria (basta **una sola volta** al momento della presentazione) per confermare che il certificato sia valido e registrare la data di scadenza.
* **Blocco Totale in Prenotazione:** Se un iscritto non ha il certificato medico in regola (scaduto o non ancora validato), **NON può proprio prenotarsi sul sistema** (oltre a non poter entrare fisicamente al centro).
* **Validità sul singolo corso:** Se il certificato è valido oggi ma scade domani, l'iscritto può regolarmente prenotare e frequentare un corso che si svolge oggi.
* **Catena di Avvisi di Scadenza:** Il sistema deve inviare avvisi automatici di scadenza del certificato a scaglioni fissi:
  * $6\text{ mesi}$ prima
  * $1\text{ mese}$ prima
  * $1\text{ settimana}$ ($7\text{ giorni}$) prima
  * $4\text{ giorni}$ prima
  * $1\text{ giorno}$ prima ("domani")

### B. Abbonamenti, Crediti (Token) e Pagamenti
* **Logica a Crediti (Token):** La prenotazione è gestita tramite crediti/ingressi. Nell'abbonamento a $10\text{ ingressi}$, un singolo ingresso vale per **una singola attività** (es. un singolo corso, oppure un accesso in sala pesi, oppure uno in piscina), **non** per l'intera giornata.
* **Fasce e Prezzi Coerenti:** Esistono abbonamenti mensili, annuali, a pacchetto ingressi (es. $10\text{ ingressi}$) e ridotti per studenti ("solo mattina"). I prezzi e le soglie li decidiamo noi, ma devono essere **economicamente coerenti** (es. un mensile che include $15\text{ ingressi}$ deve costare meno rispetto a comprare $15\text{ ingressi}$ a pacchetto).
* **Pagamenti e Ricevute:** I pagamenti possono essere effettuati **sia da app sia alla reception**. In entrambi i casi, il sistema deve obbligatoriamente generare una **ricevuta di pagamento** (necessaria anche per il calcolo degli incassi del gestore).
* **Congelamento per Infortunio (Max $30\text{ giorni}$):**
  * Si attiva presentando un certificato medico di infortunio.
  * Per "anno" si intende **l'anno di validità dell'abbonamento** (dalla data di iscrizione alla data di scadenza), non l'anno solare.
  * I $30\text{ giorni}$ massimi sono **cumulabili/frazionabili** (es. $10\text{ giorni}$ a novembre e $20\text{ giorni}$ a marzo).

### C. Prenotazioni, Capienza Globale e Divieto di Sovrapposizione
* **Tutto è a Capienza:** Il limite massimo di posti e la necessità di prenotare non valgono solo per i corsi nelle due sale, ma **anche per la sala pesi e per la piscina**.
* **Sovrapposizione Vietata (con eccezione per le code):**
  * Un iscritto **non può** essere prenotato a due corsi/attività che si svolgono nella stessa fascia oraria.
  * È invece **consentito mettersi in lista d'attesa per più corsi contemporanei**. Se si libera il posto in uno dei corsi e l'utente **conferma** il subentro, il sistema deve **rifiutare/cancellare automaticamente** la sua presenza nelle altre liste d'attesa per quello stesso orario.

### D. Liste d'Attesa, Subentri, Penali ($< 2\text{ ore}$) e Priorità (Blacklist)
* **Come funziona il Subentro:** Quando un iscritto cancella la propria prenotazione, il posto non viene assegnato "a sorpresa" scalando subito l'ingresso: il **primo in lista d'attesa viene notificato** per sapere se vuole subentrare o meno. Se comunica entro il tempo limite che rifiuta il subentro, **non subisce alcuna penalità** e si passa al successivo in coda.
* **Cancellazione Tardiva ($< 2\text{ ore}$) o Assenza (No-Show):**
  * Se cancelli **in tempo** ($\ge 2\text{ ore}$ prima), non perdi i crediti e non hai penali.
  * Se cancelli a **meno di $2\text{ ore}$ dall'inizio** (o non ti presenti), con l'abbonamento a ingressi **perdi il credito/ingresso**.
* **Sistema di Priorità e Blacklist:** Ogni utente ha un proprio livello di **priorità** nel sistema:
  * **Penalità (Blacklist):** Chi ha un abbonamento temporale (mensile/annuale) o chi prenota e non si presenta finisce in una "blacklist" / subisce un abbassamento di priorità (es. non può registrarsi alla lezione successiva o per un tot di giorni — *vincolo specifico da fissare tra noi*).
  * **Premio per chi non riesce mai a entrare:** Se un iscritto prova ripetutamente a iscriversi a un corso (es. *Pilates del martedì*) rimanendo sempre bloccato in lista d'attesa senza mai riuscire a subentrare, la sua priorità per quel corso aumenta nelle settimane successive.

### E. Corsi Fissi, Supplenze Istruttori e Taglio Corsi ($70\%$)
* **Palinsesto Fisso:** I corsi sono fissi. Un istruttore può insegnare più corsi.
* **Titolare e Supplenti:** Ogni corso ha un **istruttore principale (titolare)** e uno o più **istrimuttori supplenti**.
* **Gestione Sostituzioni via App:** Se l'istruttore principale rinuncia/è malato, la sostituzione viene gestita tramite l'app interpellando i supplenti. Se cambia l'istruttore, gli iscritti prenotati vengono avvisati.
* **Annullamento Corso e Rimborso Automatico:** Se nessun supplente è disponibile o se non si raggiunge il numero minimo di iscritti, il corso salta. Gli iscritti vengono notificati e **l'ingresso/credito viene restituito automaticamente**.
* **Soglia Minima e Storico del $70\%$:** Il gestore definisce una soglia minima di iscritti per ogni corso (calcolata bilanciando gli iscritti e il compenso dell'istruttore). Il sistema mantiene uno **storico dei corsi**: se un corso non viene svolto / viene annullato per una certa percentuale di volte (es. $\ge 70\%$ delle volte), il sistema lo segnala al gestore che può decidere di **cancellare definitivamente il corso** dal palinsesto.

### F. Registrazione Entrate e Uscite (Affluenza Centro)
* Per permettere al gestore di sapere quando il centro sportivo è pieno e quando è vuoto, il sistema non registra solo l'entrata, ma **deve registrare anche le uscite** degli iscritti.

---

## 4. Chiarimento sul "Caso di Marco" (dalla Traccia)
Nella traccia originale c'era scritto: *"Marco è terzo in lista d'attesa, il giorno dopo gli scade il certificato. Alle 17:00 un'iscritta cancella: Marco subentra. Alle 18:00 l'istruttrice avvisa di essere malata."*
* **Perché Marco saltava i primi due?** Il prof ha confermato che **era un refuso della traccia** (nella realtà si notifica il primo in coda, poi il secondo, ecc.).
* **Il certificato in scadenza:** Scadendo il *giorno dopo*, per il corso delle 18:30 di *oggi* Marco è perfettamente in regola.
* **L'istruttrice malata alle 18:00:** Scatta il meccanismo dei supplenti su app; se nessun supplente subentra entro le 18:30, il corso viene annullato, Marco riceve la notifica e il suo ingresso (ne aveva 2 rimasti) gli torna indietro in automatico.

---

## 5. Decisioni che dobbiamo prendere noi (Proposte da approvare insieme)

Il professore ha lasciato a nostra discrezione alcuni numeri/vincoli, purché siano coerenti e chiari nei Criteri di Accettazione. Ecco una proposta pronta da discutere:

| Aspetto da decidere | Indicazione del Prof | Nostra Proposta (da confermare in team) |
| :--- | :--- | :--- |
| **1. Orario abbonamento "Ridotto Studenti (solo mattina)"** | *"La fascia oraria la decidiamo noi"* | Accesso e corsi con inizio tra le **08:00 e le 13:30**. |
| **2. Tempo massimo per confermare il subentro dalla lista d'attesa** | *"Se comunica in tempo che non può subentrare non ha penalità"* | **3 ore** dalla ricezione della notifica se mancano più di **2 giorni** al corso, **1 ora** se è il giorno prima, **30 minuti** se è il giorno stesso e **15 minuti** se manca meno di **1 ora** al corso. Nel caso della finestra di 3 ore, viene inviata una seconda notifica quando mancano **30 minuti** alla scadenza per confermare. |
| **3. Regola di Penalità / Blacklist per mensili/annuali e No-Show** | *"Entra in gioco la policy sulle priorità, non ti puoi registrare la volta dopo, scegliamo noi"* | Chi cancella a $< 2\text{ ore}$ con mensile/annuale o fa *no-show* finisce in **Blacklist per 5 giorni** (non può prenotare quella tipologia di corso nel turno successivo e scende in fondo alle liste d'attesa). |
| **4. Bonus Priorità per chi resta sempre fuori in lista d'attesa** | *"Se per tante volte ripetutamente non riesce a subentrare..."* | Dopo **3 tentativi consecutivi** sullo stesso corso settimanale conclusi in lista d'attesa senza subentro, l'utente ottiene **Priorità Alta** (passa davanti in coda) per la settimana successiva. |
| **5. Soglia storica per cancellazione definitiva di un corso** | *"Decidiamo una percentuale, es. 70% delle volte annullato"* | Confermiamo il **$70\%$ di lezioni annullate** nell'arco degli ultimi **30 giorni** (o ultime 10 lezioni previste). |
| **6. Listino prezzi e crediti coerente** | *"Un mensile da 15 ingressi deve costare meno di 15 ingressi a pacchetto"* | Ingresso singolo = **15 €**, pacchetto da 8 ingressi = **85 €**, pacchetto da 12 ingressi = **110 €**. Gli studenti ricevono una riduzione del **20%** sugli abbonamenti a ingressi, vincolati ai corsi in orario mattutino. L'abbonamento **Illimitato** mensile costa **200 €**, mentre l'abbonamento annuale prevede uno sconto del **15%**. |

---

## 6. Traccia per il Product Backlog: Temi che ho pensato (PEPPE)

Questa è la base strutturata da rivedere insieme per poi copiarla nel file `.xlsx` (ordinata per priorità di valore e dipendenze logiche).

### TEMA 1: Anagrafica Multi-Ruolo, Accesso e Conformità Sanitaria

* **Epic 1.1: Autenticazione e Gestione Ruoli**
* **Epic 1.2: Certificato Medico e Scadenze**
* **NON SAPREI CHE ALTRO COME EPICA, NE DOBBIAMO DISCUTERE**

### TEMA 2: Abbonamenti, Crediti (Token) e Pagamenti
* **Epic 2.1: Acquisto Abbonamenti, Crediti e Ricevute**
* **Epic 2.2: Congelamento Abbonamenti per Infortunio**
* **NON SAPREI CHE ALTRO COME EPICA, NE DOBBIAMO DISCUTERE**

### TEMA 3: Palinsesto, Prenotazioni a Capienza e Liste d'Attesa
* **Epic 3.1: Gestione Palinsesto Fisso e Capienze**
* **Epic 3.2: Prenotazione Attività e Cancellazioni**
* **Epic 3.3: Liste d'Attesa, Subentri e Sistema di Priorità (Blacklist)**
* **NON SAPREI CHE ALTRO COME EPICA, NE DOBBIAMO DISCUTERE**

### TEMA 4: Operatività Giornaliera: Check-in/out e Supplenze Istruttori
* **Epic 4.1: Registrazione Ingressi e Uscite in Reception**
* **NON SAPREI CHE ALTRO COME EPICA, NE DOBBIAMO DISCUTERE**

### TEMA 5: Monitoraggio, Storico Corsi e Statistiche Gestore
* **Epic 5.1: Analisi Affluenza, Incassi e Taglio Corsi ($70\%$)**
* **NON SAPREI CHE ALTRO COME EPICA, NE DOBBIAMO DISCUTERE**
---

## 7. Scaletta per la nostra riunione di gruppo
1. **Conferma dei parametri:** Leggiamo la Tabella al punto 5 e fissiamo i numeri definitivi.
2. **Revisione Storie e INVEST:** Controlliamo i temi qui sopra e stiliamo le US per vedere poi cosa unire o spezzare.
3. **:) NON SO CHE ALTRO POTEMO FA :)**