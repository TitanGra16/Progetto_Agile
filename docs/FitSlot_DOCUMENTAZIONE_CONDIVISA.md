# FitSlot — Documentazione condivisa del progetto

> **Progetto P3 · Metodi di sviluppo agile · Università degli Studi dell'Aquila**  
> **Documento di riferimento per il repository GitHub**  
> **Versione:** 1.0 — 28 ottobre 2026  
> **Ambito:** requisiti, chiarimenti del Product Owner, decisioni del gruppo, motivazioni, casi limite e collegamento al Product Backlog.

Questo documento riunisce e riordina le informazioni contenute in:

1. `resocontoFinale.md` — resoconto della traccia, degli appunti del Q&A del **22 ottobre 2026**, delle proposte iniziali e della prima organizzazione del progetto;
2. `FitSlot_regole_operative_consolidate.md` — regole consolidate del **27 ottobre 2026**, comprese le precisazioni introdotte per rendere il sistema implementabile.

**Scopo pratico:** quando dobbiamo sviluppare una funzionalità, scrivere un test, stimare una User Story o spiegare una scelta al professore, cerchiamo qui **che cosa abbiamo deciso, perché e da dove viene il requisito**.

> **Regola di lettura fondamentale:** alcune proposte iniziali presenti nel vecchio resoconto sono state **modificate successivamente**. Le regole attuali sono quelle presentate nelle sezioni operative di questo documento. Le proposte precedenti rimangono nel [registro delle modifiche](#storico) per capire l'evoluzione, **non** per essere implementate.

---

<a id="indice"></a>
## Indice

1. [Come leggere questo documento e distinguere le fonti](#fonti-e-stati)
2. [Contesto del progetto e ruoli](#contesto)
3. [Chiarimenti emersi dal Product Owner](#chiarimenti-po)
4. [Registro sintetico delle decisioni e delle motivazioni](#registro-decisioni)
5. [R1 — Crediti, prenotazioni e restituzioni](#r1-crediti)
6. [R2 — Penalità e blocco di cinque giorni](#r2-penalita)
7. [R3 — Liste d'attesa, priorità e sovrapposizioni](#r3-priorita)
8. [R4 — Offerte di subentro e scadenze](#r4-subentri)
9. [R5 — Check-in, check-out, presenze e no-show](#r5-presenze)
10. [R6 — Congelamento per infortunio](#r6-congelamento)
11. [R7 — Palinsesto, istruttori e annullamento delle lezioni](#r7-corsi)
12. [R8 — Soglia del 70% e chiusura dei corsi](#r8-settanta)
13. [R9 — Abbonamenti, tariffe, studenti e pagamenti](#r9-abbonamenti)
14. [R10 — Certificati, prenotazioni obbligatorie, ruoli e storico](#r10-trasversali)
15. [Caso completo di Marco e altri scenari di verifica](#scenari)
16. [I 5 Theme e le 20 Epic del gruppo](#theme-epic)
17. [Dalle regole alle User Story, ai test e agli Sprint](#backlog)
18. [Punti da confermare o specificare durante lo sviluppo](#punti-aperti)
19. [Cronologia: come sono cambiate le decisioni](#storico)
20. [Glossario e riferimenti](#glossario)

---

<a id="fonti-e-stati"></a>
## 1. Come leggere questo documento e distinguere le fonti

Usiamo queste etichette per non confondere i **vincoli del progetto** con le **scelte del gruppo**.

| Etichetta | Significato | Come trattarla |
|---|---|---|
| **[TRACCIA / PO]** | Informazione riportata nella traccia P3 o negli appunti della sessione di domande al Product Owner. | È il vincolo di partenza da rispettare; se la fonte precisa un comportamento, non lo si sostituisce con una scelta arbitraria. |
| **[TEAM]** | Scelta concordata dal gruppo su una questione lasciata aperta dal docente; comprende le modifiche successive discusse dal gruppo. | È la regola progettuale attuale, finché il gruppo non la modifica in modo esplicito. |
| **[OPERATIVA]** | Precisazione introdotta nel documento consolidato per evitare casi ambigui e rendere la regola programmabile. | È un riferimento per progettazione e test, **non una citazione del professore**. |
| **[STORICA — SUPERATA]** | Proposta iniziale che è stata sostituita da una decisione successiva. | Non implementarla; è conservata per spiegare perché abbiamo cambiato idea. |
| **[DA CONFERMARE]** | Aspetto sul quale i materiali non documentano una decisione definitiva e non è corretto inventarne una. | Va concordato dal team durante il Refinement, prima di programmare il caso interessato. |

### Gerarchia pratica delle informazioni

1. **Traccia e chiarimenti del Product Owner**: definiscono requisiti e vincoli.
2. **Ultima decisione esplicita del gruppo**: precisa ciò che il Product Owner ha lasciato decidere agli studenti; una revisione successiva sostituisce la proposta precedente.
3. **Regole operative consolidate**: descrivono stati, casi limite e controlli tecnici in modo coerente con i punti 1 e 2.
4. **Product Backlog Excel**: traduce i requisiti in User Story ordinabili, stimabili e implementabili. **Non** è una fonte che cambia silenziosamente una regola di business.

Le formulazioni attribuite al professore provengono dagli **appunti del gruppo** nel resoconto, non da una trascrizione ufficiale della lezione. Questa distinzione è utile quando in presentazione viene chiesto «chi ha deciso questa cosa?».

### Cosa NON fa questo documento

Non sostituisce il Product Backlog, non stabilisce gli Story Point e non assegna le User Story agli Sprint. Le stime si discuteranno con il **Planning Poker** del gruppo (1, 2, 3, 5, 8, 13); una storia troppo grande potrà essere spezzata. Non sono imposte tecnologie, database, librerie o servizi di pagamento specifici.

---

<a id="contesto"></a>
## 2. Contesto del progetto e ruoli

**FitSlot** è il gestionale di un centro sportivo con **sala pesi, piscina e sale per i corsi**. Deve supportare prenotazioni, abbonamenti, accessi fisici, istruttori, notifiche e statistiche del gestore.

### Applicazione unica e utenti multi-ruolo

**[TRACCIA / PO, come riportato nel resoconto]** Tutte le categorie di utenti accedono al sistema: non è un programma riservato alla reception. La visione condivisa prevede una **singola applicazione/interfaccia** utilizzabile con autenticazione; la forma concreta desktop/web sarà determinata durante l'implementazione in conformità alla traccia dell'esame.

**[TRACCIA / PO]** Una persona può avere **più ruoli nello stesso account**: per esempio un istruttore che, nel tempo libero, è anche iscritto e prenota la piscina. Perciò non dobbiamo creare per forza due profili separati per lo stesso individuo.

| Ruolo | Cosa fa | Perché serve |
|---|---|---|
| **Iscritto** | Consulta stato di certificato/abbonamento, acquista, prenota, cancella, entra in lista d'attesa e risponde ai subentri. | Permette di gestire autonomamente le proprie attività. |
| **Addetto alla reception** | Registra iscritti, verifica certificati, registra pagamenti, approva congelamenti, registra entrate e uscite. | Gestisce l'operatività quotidiana e i controlli di accesso. |
| **Istruttore** | Indica disponibilità, vede lezioni e iscritti, registra presenze, segnala assenze e accetta/rifiuta sostituzioni. | Rende attendibile il palinsesto e la partecipazione effettiva. |
| **Gestore** | Definisce listino, corsi e capienze; imposta la soglia minima; controlla affluenza, incassi e storico; decide eventuali chiusure. | Governa l'offerta sportiva e le decisioni economiche. |

**Motivazione della scelta multi-ruolo:** i permessi dipendono dalle funzioni effettivamente attribuite all'account, non dall'idea che ogni persona possa avere soltanto un ruolo. Un istruttore-iscritto potrà consultare i propri corsi *e* prenotare come cliente, ma non avrà automaticamente i permessi del gestore.

---

<a id="chiarimenti-po"></a>
## 3. Chiarimenti emersi dal Product Owner

Questa sezione conserva **il senso degli appunti della lezione del 22 ottobre 2026**, prima delle scelte specifiche del gruppo.

| Argomento | Chiarimento riportato negli appunti | Conseguenza per FitSlot |
|---|---|---|
| Accesso al sistema | Accedono anche iscritti e istruttori; esistono account con più ruoli. | Autenticazione e permessi per ruolo. |
| Entrate e uscite | Vanno registrati **sia check-in sia check-out**. | Non basta registrare l'arrivo per sapere quante persone siano presenti. |
| Valore dell'ingresso | Un ingresso copre **una attività**, non tutta la giornata. | Corso + piscina nello stesso giorno richiedono due crediti su un pacchetto. |
| Riserva/consumo del credito | Il momento dell'addebito non era fissato in modo dettagliato; dipende anche da attese e cancellazioni. | Il gruppo ha scelto un ciclo di **riserva → consumo/restituzione**, descritto in R1. |
| Certificati | Caricamento via applicazione o consegna cartacea; controllo in reception; senza certificato valido o verificato niente prenotazioni/ingresso. | Gli stati del certificato devono essere espliciti. |
| Scadenza imminente | Se il certificato scade domani, **oggi** è ancora valido. | Il controllo guarda la data effettiva dell'attività. |
| Avvisi certificato | Promemoria a 6 mesi, 1 mese, 7 giorni, **4 giorni**, 1 giorno. | Notifiche programmate su date determinate. |
| Capienza | Anche sala pesi e piscina hanno limiti, non soltanto i corsi. | Prenotazioni e controllo delle fasce disponibili. |
| Attività sovrapposte | Due prenotazioni confermate sovrapposte non vanno consentite; si possono invece attendere più corsi allo stesso orario. | Controlli diversi per **prenotato** e **in attesa**. |
| Subentro | Quando si libera un posto, il primo candidato può accettare/rifiutare; un rifiuto tempestivo non va penalizzato. | Offerta sequenziale, non prenotazione automatica. |
| «Marco è terzo» | Il salto dei primi due candidati nella traccia era un **refuso**, secondo il PO. | Si parte dal primo avente diritto secondo l'ordine applicabile. |
| Penalità | Cancellare sotto le 2 ore può costare un ingresso; per gli illimitati serve una politica definita dal gruppo. | Due tipi di conseguenza, spiegati in R2. |
| Priorità ricorrenti | Chi resta escluso ripetutamente dallo stesso corso dovrebbe avere una possibilità maggiore. | Bonus dopo 3 tentativi, definito dal gruppo. |
| Congelamento | Infortunio documentato; massimo **30 giorni cumulabili**, riferiti all'anno di abbonamento e non al calendario solare. | Contatore per abbonamento, non reset al 1° gennaio. |
| Istruttori | Corso ricorrente, titolare e supplenti; indisponibilità e sostituzione via applicazione; avviso agli iscritti. | Le singole lezioni devono poter cambiare istruttore o essere annullate. |
| Numero minimo | Definito dal gestore tenendo conto della convenienza di tenere una lezione. | Controllo della soglia configurabile. |
| Storico/70% | Un corso annullato troppo spesso va segnalato, lasciando al gestore la decisione sul futuro. | Report storico e soglia, non eliminazione automatica. |
| Pagamenti | Possibili da sistema e in reception; **ricevuta in entrambi i casi**. | Storico degli incassi collegato ai pagamenti riusciti. |

**Perché tenere separata questa sezione:** il professore ha descritto diversi *obiettivi* ma non ogni durata, prezzo o comportamento di dettaglio. I valori scelti successivamente dal gruppo non devono essere attribuiti a lui.

---

<a id="registro-decisioni"></a>
## 4. Registro sintetico delle decisioni e delle motivazioni

Questo è l'indice rapido per rispondere alle domande «perché avete fatto così?».

| ID | Decisione attuale | Perché | Origine principale |
|---|---|---|---|
| **D01** | 1 credito = 1 attività, non 1 giorno. | Rispetta il significato di «ingresso» chiarito dal PO. | [TRACCIA / PO] |
| **D02** | Prenotazione confermata **riserva** il credito; partecipazione lo **consuma**; lista d'attesa non riserva nulla. | Evita doppi usi e addebiti per posti non ottenuti. | [OPERATIVA] |
| **D03** | Cancellazione ≥ 2 h libera il credito; cancellazione < 2 h o no-show lo perde. | Distinzione temporale chiara e verificabile. | [TRACCIA / PO] + [TEAM] |
| **D04** | Pacchetti: **solo perdita del credito**; illimitati: **blocco 5 giorni**. | Stessa esigenza di responsabilizzare, senza sovrapporre sanzioni. | [TEAM, revisione successiva] |
| **D05** | Il blocco non abbassa la posizione in coda. | Riduce drasticamente gli stati da programmare e rende la regola spiegabile. | [TEAM, revisione successiva] |
| **D06** | Bonus alto dopo 3 esclusioni settimanali consecutive; durata una edizione. | Evita che pochi iscritti rimangano esclusi indefinitamente. | [TEAM] + [OPERATIVA] |
| **D07** | Offerte sequenziali con risposta 3 h / 1 h / 30 min / 15 min. | Il posto non viene assegnato a chi non può partecipare; i tempi si restringono quando il corso si avvicina. | [TEAM] |
| **D08** | Vietate due **prenotazioni confermate** sovrapposte; consentite due **attese** sovrapposte. | Una possibilità non equivale a un impegno effettivo di partecipazione. | [TRACCIA / PO] |
| **D09** | Check-in/out del centro distinti dalla presenza alla specifica attività. | Sapere che una persona è entrata non dimostra che abbia seguito Yoga. | [TRACCIA / PO] + [OPERATIVA] |
| **D10** | No-show rilevato alla fine dell'attività/fascia. | La penale deriva dall'assenza effettiva, non da un'ipotesi a inizio lezione. | [OPERATIVA] |
| **D11** | Congelamento per mensili/annuali: max 30 giorni cumulabili, scadenza prorogata. | Rispetta il tetto del PO e impedisce di perdere la durata pagata. | [TRACCIA / PO] + [OPERATIVA] |
| **D12** | Sala pesi prenotata per **120 min**, piscina per **60 min**. | Fascia pesi più realistica; le capienze restano controllabili. | [TEAM, revisione successiva] + [OPERATIVA] |
| **D13** | Numero minimo controllato **una volta, 1 h prima** della lezione. | Evita annullamenti continui a ridosso dell'inizio. | [OPERATIVA] |
| **D14** | Supplente: accetta il primo disponibile; se nessuno accetta entro l'inizio, la lezione salta. | Garantisce un comportamento deterministico anche nei casi d'urgenza. | [TRACCIA / PO] + [OPERATIVA] |
| **D15** | Se ≥ 7 delle ultime 10 lezioni sono annullate, **avviso**, non chiusura automatica. | Il gestore resta responsabile della scelta economica. | [TEAM] + [OPERATIVA] |
| **D16** | Studenti: **−20% pacchetti**, attività con inizio dalle 08:00 alle 13:30. | Regola semplice da controllare usando l'orario di inizio. | [TEAM] |
| **D17** | Prezzi 15/85/110/200 €, annuale 2.040 €; pacchetto 10 ingressi **proposto a 100 €**. | Listino diversificato; pacchetto da 10 richiesto dalla traccia. | [TEAM] + [DA CONFERMARE] per i 100 € |
| **D18** | Pacchetti senza scadenza; congelamento riservato agli abbonamenti a tempo. | Evita due meccanismi di sospensione e calcolo scadenza per crediti a consumo. | [OPERATIVA] |
| **D19** | Certificato verificato **per ogni nuovo documento**; blocco se assente/scaduto/non verificato. | Evita il controllo ripetitivo dello stesso file, mantenendo il requisito sanitario. | [TRACCIA / PO] + [OPERATIVA] |
| **D20** | Un solo abbonamento attivo per utente, pagamenti con ricevuta. | Riduce conflitti fra crediti, rinnovi e tipologie; garantisce tracciabilità. | [OPERATIVA] + [TRACCIA / PO] |
| **D21** | Non eliminare lo storico di lezioni, annullamenti, ricevute e penalità. | Servono statistiche verificabili e spiegazioni delle decisioni del sistema. | [TRACCIA / PO] + [OPERATIVA] |

Le sezioni che seguono spiegano ogni decisione con **regola**, **motivazione**, **esempio**, **eccezioni** e, dove serve, **aspetti ancora aperti**.

---

<a id="r1-crediti"></a>
## 5. R1 — Crediti, prenotazioni e restituzioni

### Regola attuale

Per un pacchetto a ingressi, i crediti passano fra tre stati logici:

```text
CREDITO DISPONIBILE
        |
        | Prenotazione confermata / subentro accettato
        v
CREDITO RISERVATO
        |                 |
        | partecipazione | cancellazione in tempo o attività annullata dal centro
        v                 v
CREDITO CONSUMATO     CREDITO DISPONIBILE

Cancellazione tardiva (< 2 h) o no-show: RISERVATO -> CONSUMATO (penale).
```

- **Disponibile:** può essere utilizzato per una nuova prenotazione.
- **Riservato:** garantisce una prenotazione già confermata; non può essere utilizzato per un'altra.
- **Consumato:** è stato utilizzato per un'attività oppure perso come penalità prevista.
- **Lista d'attesa:** non consuma e non riserva crediti. La riserva si effettua soltanto dopo l'accettazione valida di una proposta di subentro.
- **Abbonamento illimitato:** non usa contatori di crediti, pur richiedendo abbonamento valido, certificato e prenotazione.

**Saldo prenotabile** = crediti acquistati − crediti riservati − crediti consumati. Il sistema deve impedire doppi addebiti per la stessa prenotazione.

### Perché questa scelta

Il Q&A aveva chiarito che un ingresso vale una sola attività, ma non aveva fissato in modo completo **quando** scalarlo. Riservarlo quando si ottiene un posto impedisce a un iscritto con un solo credito di confermare due lezioni future. Consumarlo soltanto alla presenza distingue una prenotazione effettivamente utilizzata da una cancellata per tempo. Non bloccare un credito mentre si è in attesa evita di penalizzare chi non ha ancora un posto.

### Casi concreti

| Caso | Prima | Dopo | Perché |
|---|---|---|---|
| Ho 3 crediti e confermo Yoga. | 3 disponibili | 2 disponibili, 1 riservato | Il posto è confermato ma non ho partecipato. |
| Con gli stessi crediti entro nella lista d'attesa di Pilates. | 2 disponibili, 1 riservato | Identico | L'attesa non garantisce un posto. |
| Partecipo a Yoga. | 1 riservato | 1 consumato | È stata frequentata un'attività. |
| Cancello Yoga con 3 ore di anticipo. | 1 riservato | 1 disponibile | La cancellazione è tempestiva. |
| Cancello Yoga con 1 ora di anticipo. | 1 riservato | 1 consumato | Penale prevista per i pacchetti. |
| Il centro annulla Yoga. | 1 riservato | 1 disponibile | L'iscritto non è responsabile. |

**Confine temporale importante:** a **esattamente 2 ore** dalla lezione la cancellazione è tempestiva; a **1 ora e 59 minuti** è tardiva.

**Eccezione per annullamenti registrati in ritardo:** il documento operativo prevede il riaccredito anche qualora un credito risulti già contabilizzato come consumato per un'attività poi annullata dal centro. Questo serve a non addebitare all'iscritto un evento che non si è svolto.

---

<a id="r2-penalita"></a>
## 6. R2 — Penalità e blocco di cinque giorni

### Regola attuale (sostituisce la vecchia proposta)

| Tipo di abbonamento | Cancellazione con almeno 2 ore di anticipo | Cancellazione sotto 2 ore / no-show |
|---|---|---|
| **Pacchetto a ingressi** | Credito liberato, nessuna penalità. | **Perdita di un credito e basta.** Nessuna blacklist. |
| **Mensile o annuale illimitato** | Nessuna penalità. | **Blocco delle nuove prenotazioni per 5 giorni.** Nessun credito numerico da perdere. |

**Durante il blocco di cinque giorni**, il titolare di mensile/annuale:

1. non può confermare **nuove prenotazioni**;
2. non può inserirsi in **nuove liste d'attesa**;
3. non può accettare **offerte di subentro**;
4. mantiene le **prenotazioni già confermate** prima della penalità;
5. viene rimosso dalle **liste d'attesa già aperte** all'avvio del blocco, senza altre penali.

Il blocco termina automaticamente dopo cinque giorni. Se una prenotazione confermata prima del blocco causa un'altra cancellazione tardiva o un altro no-show, la scadenza viene aggiornata a **cinque giorni dopo il nuovo evento**. La blacklist **non modifica la priorità** di chi è in coda: durante il blocco semplicemente non si può utilizzare la coda.

### Perché avete cambiato la proposta iniziale

La prima proposta nel `resocontoFinale.md` combinava **cinque giorni**, divieto della prossima lezione dello stesso tipo e **priorità ridotta** nella coda. Dopo il confronto nel gruppo è stata scelta una soluzione più semplice:

- per chi compra crediti, la perdita del credito è già una conseguenza concreta; aggiungere anche la blacklist sarebbe una doppia penalità;
- per mensili e annuali, non essendoci un contatore di crediti, il blocco temporaneo crea una conseguenza comprensibile;
- eliminando la «priorità bassa» non occorre implementare una classifica complicata che mischi bonus e sanzioni.

Questa modifica è **una decisione del gruppo**, non un cambiamento deciso dal professore.

### Esempi

- **Marco ha un pacchetto da 10:** annulla a 45 minuti da Yoga. Perde 1 credito; il giorno dopo può prenotare un'altra attività se ha crediti e requisiti validi.
- **Sara ha un mensile illimitato:** non si presenta a Pilates. Per cinque giorni non può fare nuove prenotazioni; una prenotazione già confermata per nuoto resta valida.
- **Luca rifiuta un subentro:** il rifiuto, anche per mancata risposta, non comporta penalità; si passa al candidato successivo.
- **Il centro annulla il corso:** non vengono applicati no-show, perdita di credito o blocchi.

---

<a id="r3-priorita"></a>
## 7. R3 — Liste d'attesa, priorità e sovrapposizioni

### Ordinamento della lista

Per ogni lezione piena il sistema conserva una **lista d'attesa**. I livelli sono soltanto:

- **Normale:** chi si iscrive normalmente alla lista.
- **Alta:** chi ha diritto al bonus dopo tre tentativi consecutivi falliti sullo stesso corso ricorrente.

**Ordine:** prima i candidati a priorità alta; a parità di priorità, prima chi si è iscritto alla lista in precedenza. **Non esiste la priorità bassa.**

### Quando si ottiene il bonus alto

Un iscritto deve restare **escluso da tre edizioni settimanali consecutive** dello **stesso corso ricorrente**, avendo effettivamente tentato di ottenere un posto ogni settimana. Nell'edizione successiva ottiene priorità alta; il bonus vale **solo per quella edizione** e poi scade.

Non conta come esclusione valida un'offerta rifiutata o lasciata scadere, né l'abbandono volontario della lista. Se l'utente salta una settimana o ottiene un posto, la sequenza si interrompe e ricomincia da zero.

**Motivazione:** premiare chi non riesce a partecipare senza creare una reputazione generale o un sistema di punteggi. Il bonus è limitato a **quel corso**, non a tutta la palestra.

### Prenotazioni sovrapposte e attese sovrapposte

Sono due situazioni diverse:

| Stato | Posso avere attività sovrapposte? | Perché |
|---|---|---|
| Due prenotazioni **confermate** | **No** | Non posso partecipare a due attività nello stesso intervallo. |
| Due richieste in **lista d'attesa** | **Sì** | Nessun posto è ancora garantito. |
| Un subentro **accettato** e altre attese sovrapposte | **No, dopo l'accettazione** | Si cancellano automaticamente le attese e le offerte pendenti incompatibili. |

Il confronto deve usare **inizio e fine** dell'attività, non solo l'ora di inizio: Yoga 18:00–19:00 e piscina 18:30–19:30 sono sovrapposte anche se iniziano a orari diversi.

**Motivazione:** la regola evita posti confermati inutilizzabili, ma lascia agli iscritti più possibilità quando stanno ancora aspettando un'offerta.

---

<a id="r4-subentri"></a>
## 8. R4 — Offerte di subentro e scadenze

### Procedura

1. Una lezione si libera di un posto (per esempio a causa di una cancellazione).
2. Il sistema sceglie **il primo candidato idoneo** nella lista ordinata secondo R3.
3. Il posto viene offerto **a una sola persona per volta**, con un tempo massimo per rispondere.
4. **Se accetta**, il sistema controlla di nuovo certificato, abbonamento, crediti, fascia oraria, penalità e sovrapposizioni; se tutto è valido, conferma la prenotazione e riserva l'eventuale credito.
5. **Se rifiuta, non risponde o non è più idoneo**, l'offerta decade senza penalità e si passa al successivo.
6. Quando una prenotazione viene confermata, le altre attese/offerte sovrapposte dello stesso utente vengono annullate.

### Quanto tempo ha per rispondere?

La finestra dipende dalle ore che mancano **al momento in cui l'offerta viene inviata**.

| Tempo mancante all'inizio | Finestra assegnata |
|---|---:|
| **Più di 48 ore** | **3 ore** |
| **Più di 24 ore e fino a 48 ore incluse** | **1 ora** |
| **Più di 1 ora e fino a 24 ore incluse** | **30 minuti** |
| **1 ora o meno**, ma prima dell'inizio | **15 minuti** |

La scadenza reale è **la prima tra** «scadenza della finestra assegnata» e «inizio della lezione». Non si inviano offerte dopo l'inizio dell'attività. Quando la finestra è di **3 ore**, se manca ancora una risposta si manda un promemoria **30 minuti prima della scadenza**.

### Perché queste durate

Il gruppo ha voluto lasciare più tempo di risposta quando la lezione è lontana, ma evitare che un posto resti bloccato per ore pochi minuti prima dell'inizio. Le formulazioni iniziali («il giorno prima», «il giorno stesso», «meno di un'ora») potevano sovrapporsi; il documento consolidato le ha convertite in **intervalli esatti non sovrapposti**, per permettere condizioni di controllo univoche.

### Esempi di confine

- Mancano **49 ore**: finestra di **3 ore**.
- Mancano **48 ore esatte**: finestra di **1 ora**.
- Mancano **24 ore esatte**: finestra di **30 minuti**.
- Mancano **60 minuti esatti**: finestra di **15 minuti**.
- Mancano **10 minuti**: si assegna fino a **10 minuti**, non oltre l'inizio.

**Rifiutare e non rispondere non comporta penalità.** Dopo l'accettazione, invece, una successiva cancellazione sotto le 2 ore segue R2.

---

<a id="r5-presenze"></a>
## 9. R5 — Check-in, check-out, presenze e no-show

### Due tipi di presenza

| Evento | Chi lo registra | A cosa serve |
|---|---|---|
| **Entrata fisica nel centro** (*check-in*) | Reception | Sapere chi si trova nella struttura. |
| **Uscita fisica dal centro** (*check-out*) | Reception | Aggiornare il numero di persone presenti. |
| **Presenza al corso** | Istruttore o, in sua assenza, reception | Confermare la partecipazione effettiva e consumare il credito. |
| **Presenza in sala pesi/piscina** | Reception, collegando l'accesso alla fascia prenotata | Verificare che la fascia sia stata utilizzata. |
| **No-show** | Controllo al termine della lezione/fascia | Identificare le prenotazioni confermate senza presenza. |

Un iscritto non deve risultare entrato due volte consecutivamente senza uscita o una correzione esplicita della registrazione precedente.

### Perché separare i due conteggi

**Esempio:** Marco entra alle 17:45, ma non partecipa al corso Yoga prenotato alle 18:00. È **presente nel centro**, ma è **assente al corso**. Se usassimo soltanto il check-in fisico per decidere la partecipazione, non riconosceremmo il no-show e non applicheremmo correttamente la regola sui crediti.

Anche le statistiche devono distinguere:

- **Persone attualmente dentro il centro**: ingressi registrati meno uscite registrate.
- **Posti prenotati in una fascia/lezione**: prenotazioni confermate per quella specifica attività.
- **Partecipanti effettivi**: presenze confermate a una specifica attività.

**Importante per il saldo crediti:** chi ha un solo credito già **riservato** può avere «0 crediti disponibili» ed essere comunque autorizzato a entrare **per quella prenotazione confermata**, se gli altri requisiti sono validi.

**Quando scatta il no-show:** al termine della lezione o della fascia prenotata, se non risulta la presenza. Le lezioni annullate dal centro **non** producono assenze sanzionabili.

---

<a id="r6-congelamento"></a>
## 10. R6 — Congelamento per infortunio

### Quali abbonamenti si possono congelare?

**[TRACCIA / PO]** È previsto un congelamento per infortunio con certificazione e massimo 30 giorni cumulabili riferiti all'anno di validità dell'abbonamento, non all'anno solare.

**[OPERATIVA]** Per ridurre i casi particolari, il congelamento si applica ai soli **abbonamenti mensili e annuali**. I pacchetti a ingressi **non scadono nel tempo**, quindi il credito resta utilizzabile senza sospensione.

### Comportamento dettagliato

1. L'iscritto presenta documentazione medica per infortunio.
2. La reception verifica e approva **un periodo corrente o futuro**, non retroattivo.
3. Il sistema controlla quanti giorni di congelamento sono già stati utilizzati **sullo stesso abbonamento**.
4. Se il nuovo intervallo supera il totale cumulabile di **30 giorni**, la richiesta viene rifiutata.
5. Le prenotazioni che cadono durante la sospensione vengono cancellate **senza penalità**; non si accettano nuove prenotazioni o ingressi coperti dal piano sospeso.
6. La scadenza del mensile/annuale slitta in avanti dei giorni autorizzati.
7. A fine sospensione il piano torna attivo automaticamente.

### Perché è stato definito così

Il PO ha specificato il tetto di giorni, ma **non** il metodo completo di proroga, l'interazione con prenotazioni già confermate o la distinzione fra tipi di piano. Le precisazioni scelte evitano che un iscritto perda giorni pagati e impediscono che una prenotazione rimanga attiva durante l'infortunio.

**Esempio:** 10 giorni di congelamento a novembre + 20 a marzo = **30 giorni usati** sullo stesso annuale. Una richiesta ulteriore di 1 giorno viene rifiutata. La proroga dell'abbonamento **non** fa ripartire un nuovo contatore da 30 giorni.

**Nota sul mensile:** il criterio di 30 giorni viene applicato «per abbonamento», come precisato nel documento operativo; non si deve interpretare come 30 giorni nuovi per ogni anno solare.

---

<a id="r7-corsi"></a>
## 11. R7 — Palinsesto, istruttori e annullamento delle lezioni

### Corso ricorrente e singola lezione non sono la stessa cosa

Un **corso ricorrente** definisce la programmazione abituale: sala, giorno, orario, durata, capienza, soglia minima, istruttore titolare e supplenti. Ogni data concreta corrisponde a **una singola lezione**; questa può essere svolta, sostituita o annullata senza cancellare le altre edizioni.

**Perché:** se l'istruttrice è malata martedì, non dobbiamo eliminare automaticamente tutti i «Pilates del martedì» dal calendario. Lo storico deve inoltre restare consultabile.

### Capienza e soglia minima

Il **gestore** configura la capienza massima e il **numero minimo di iscritti** necessario a tenere la lezione; la soglia minima non può superare la capienza. È utile per evitare lezioni economicamente non sostenibili.

**[OPERATIVA] Una sola verifica definitiva un'ora prima dell'inizio:**

- se le **prenotazioni confermate** sono inferiori alla soglia, quella lezione viene annullata;
- gli iscritti vengono avvisati e recuperano gli eventuali crediti;
- se al momento del controllo la soglia era raggiunta, cancellazioni successive **non** attivano un secondo annullamento automatico per scarsa partecipazione.

**Perché una sola verifica:** rende prevedibile il comportamento del sistema e limita gli annullamenti improvvisi nell'ultima ora.

### Titolare, disponibilità e supplenti

- L'istruttore comunica la sua **disponibilità settimanale** e può segnalare di non poter tenere una specifica lezione.
- Il gestore assegna un **titolare** e uno o più **supplenti** al corso.
- Se il titolare è indisponibile, si inviano le richieste di sostituzione ai supplenti; il **primo che accetta** viene assegnato.
- Gli iscritti vengono avvisati se cambia l'istruttore.
- Se nessun supplente accetta **entro l'inizio della lezione**, questa viene annullata e gli iscritti vengono avvisati.

**Motivazione:** il cambio istruttore va comunicato, ma l'assenza di una persona non deve automaticamente far saltare una lezione quando è disponibile un sostituto.

### Conseguenze di un annullamento deciso dal centro

- I posti non vengono più prenotati né offerti in subentro.
- I crediti riservati vengono restituiti; nessuno riceve blacklist o no-show.
- L'evento viene mantenuto nello storico con stato **annullato**.

---

<a id="r8-settanta"></a>
## 12. R8 — Soglia del 70% e chiusura dei corsi

### Regola attuale

Per un corso ricorrente si considerano **le ultime 10 lezioni programmate già trascorse**. Se **almeno 7** sono state **annullate dal centro**, il sistema mostra un avviso al gestore.

- **Prima di 10 lezioni osservabili**, non si genera una segnalazione automatica.
- Si contano le lezioni annullate per insufficienza di iscritti, mancata sostituzione dell'istruttore o altre ragioni imputate al centro.
- **Non** si contano come lezioni annullate le singole cancellazioni di prenotazione effettuate dagli iscritti.
- **Nessuna chiusura automatica:** il gestore può mantenere, modificare o interrompere il corso.
- Se interrompe il corso, si annullano le **lezioni future** interessate e si conservano **tutte le lezioni passate** nello storico; ai prenotati si restituiscono i crediti senza penale.

### Perché le ultime 10 e non gli ultimi 30 giorni

Nel resoconto iniziale comparivano le alternative «ultimi 30 giorni **o** ultime 10 lezioni». Il documento operativo successivo ha scelto **le ultime 10 lezioni** per avere un denominatore fisso e verificabile, indipendente da quante volte il corso si svolga in un mese.

**Esempio:** 7 lezioni annullate su 10 = **70%**, compare la segnalazione. 6 su 10 = **60%**, nessuna segnalazione. Non si cancella automaticamente il corso: la scelta rimane al gestore, come richiesto negli appunti del Q&A.

### Differenza fra due annullamenti

- **Annullare una singola lezione**: non si svolge solo quella data.
- **Interrompere un corso ricorrente**: si smette di programmare le date future di quel corso, senza cancellarne la storia.

---

<a id="r9-abbonamenti"></a>
## 13. R9 — Abbonamenti, tariffe, studenti e pagamenti

### Listino preso come riferimento

| Tipologia | Prezzo | Stato della decisione | Motivazione |
|---|---:|---|---|
| Ingresso singolo | **15 €** | [TEAM] | Opzione senza pacchetto. |
| Pacchetto **8** ingressi | **85 €** | [TEAM] | Prezzo unitario inferiore al singolo ingresso. |
| Pacchetto **10** ingressi | **100 €** | **[DA CONFERMARE]**: proposta operativa | La traccia menziona espressamente 10 ingressi; prezzo intermedio fra 8 e 12. |
| Pacchetto **12** ingressi | **110 €** | [TEAM] | Convenienza maggiore su un pacchetto più ampio. |
| Mensile illimitato | **200 €** | [TEAM] | Alternativa per chi frequenta molte attività. |
| Annuale illimitato | **2.040 €** | [TEAM] + calcolo operativo | Dodici mensili da 200 € meno **15%**: `200 × 12 × 0,85 = 2.040`. |

**Nota economica:** il docente ha chiesto prezzi coerenti e ha proposto come esempio un mensile da 15 ingressi più conveniente di 15 ingressi comprati singolarmente. Quell'esempio **non impone** di creare esattamente un mensile da 15 crediti, perché il gruppo ha scelto un mensile **illimitato**. Durante la presentazione conviene comunque saper spiegare il valore delle diverse opzioni, senza attribuire al professore questo specifico listino.

### Agevolazione studenti

**[TEAM]** Lo sconto è del **20% sui pacchetti a ingressi**:

| Pacchetto | Prezzo normale | Prezzo studenti |
|---|---:|---:|
| 8 ingressi | 85 € | **68 €** |
| 10 ingressi | 100 € | **80 €** *(dipende dalla conferma del prezzo da 100 €)* |
| 12 ingressi | 110 € | **88 €** |

L'abbonamento ridotto è utilizzabile soltanto per attività che **iniziano fra le 08:00 e le 13:30 comprese**. Non è necessario che l'attività **termini** entro le 13:30.

**Perché questa regola:** basta verificare l'ora d'inizio della prenotazione, evitando di penalizzare arbitrariamente lezioni che iniziano in fascia mattutina ma finiscono più tardi.

**Esempio:** un'attività 13:30–14:30 è consentita a uno studente idoneo; un'attività che inizia alle 14:00 non lo è. La reception deve poter verificare l'idoneità allo sconto oppure si deve usare un dato dichiarato e successivamente validato.

### Servizi inclusi, scadenza e rinnovi

- **[OPERATIVA]** Un credito di un pacchetto copre una attività; i pacchetti non hanno scadenza temporale e non richiedono congelamento.
- **[OPERATIVA]** Per la prima versione si considerano inclusi **corsi, piscina e sala pesi**, con il limite di orario per gli studenti. I servizi esatti di ciascun piano vanno esplicitati nel listino.
- **[OPERATIVA]** Per evitare sovrapposizioni fra piani diversi, un iscritto ha **un solo abbonamento attivo alla volta**.
- **[OPERATIVA]** Il rinnovo di un mensile o annuale acquistato in anticipo può partire **alla scadenza** del precedente.

**[DA CONFERMARE — punto importante]** L'ultima frase del documento consolidato parla genericamente di «rinnovo che parte alla scadenza», ma i pacchetti **non scadono**. Il modo esatto di acquistare un nuovo pacchetto mentre uno vecchio ha ancora crediti **non è definito in modo univoco dai due file**. Una soluzione semplice già discussa durante il lavoro sul backlog è attivare il nuovo pacchetto **solo quando il precedente ha esaurito i crediti**; va però fissata esplicitamente dal gruppo prima di implementare quel caso, per non presentarla come una regola del professore.

### Pagamenti e ricevute

**[TRACCIA / PO]** Il pagamento deve essere possibile **alla reception** e **tramite sistema**. Ogni pagamento andato a buon fine produce una **ricevuta** consultabile e conservata nello storico. Un pagamento fallito non deve apparire come riuscito né produrre una ricevuta che lo attesti.

**[OPERATIVA]** Per la dimostrazione accademica si può rappresentare il pagamento online come **simulazione dichiarata**, se non è stata realizzata l'integrazione con un provider reale. Non bisogna descrivere una simulazione come transazione economica reale.

**Perché:** reception e utente hanno due canali diversi, ma incassi e ricevute devono restare coerenti per i report del gestore.

---

<a id="r10-trasversali"></a>
## 14. R10 — Certificati, prenotazioni obbligatorie, ruoli e storico

### Certificato medico

Il documento può essere **caricato dall'iscritto** o **consegnato su carta** in reception. Per ogni **nuovo certificato** la reception lo controlla, registra la scadenza e lo valida; «una sola verifica» significa **una verifica per documento**, non «una verifica nella vita dell'iscritto».

Stati utili per l'implementazione: **assente**, **in attesa di verifica**, **valido**, **scaduto**. Non si conferma una prenotazione né si autorizza l'accesso se il certificato richiesto è assente, non verificato o scaduto **alla data dell'attività**.

**Esempio:** il certificato scade domani, quindi una lezione oggi è consentita; una lezione fra tre giorni viene invece rifiutata se non sarà coperta da un nuovo certificato valido.

**Notifiche di scadenza:** 6 mesi, 1 mese, 7 giorni, 4 giorni e 1 giorno prima. I **4 giorni** sono importanti perché erano emersi esplicitamente negli appunti e rischiavano di sparire copiando i criteri nell'Excel.

**Protezione dei dati:** un istruttore può aver bisogno di sapere che l'iscritto è idoneo a frequentare una lezione, non necessariamente di leggere il documento medico completo. I dati e le funzioni devono essere accessibili ai soli ruoli autorizzati.

### Prenotazione obbligatoria e fasce

Per la versione operativa di FitSlot **ogni accesso a un'attività deve corrispondere a una prenotazione**, anche per sala pesi e piscina:

| Attività | Durata/finestra | Capienza |
|---|---|---|
| Corso in una delle due sale | Durata configurata nel palinsesto | Numero massimo definito dal gestore e dalla sala. |
| **Sala pesi** | **120 minuti** per fascia | Posti limitati. |
| **Piscina** | **60 minuti** per fascia | Posti limitati. |

La prenotazione obbligatoria è il modo scelto per rispettare e controllare la capienza di tutte le attività, senza introdurre in parallelo un sistema di accessi liberi non prenotati. Il gruppo ha successivamente corretto la fascia della sala pesi da **60 a 120 minuti**, ritenendola più realistica.

### Altri controlli comuni

- Confrontare la sovrapposizione **degli intervalli**, non soltanto l'identità dell'ora iniziale.
- Verificare **abbonamento, fascia oraria, certificato, crediti disponibili o già riservati alla prenotazione, blocchi e capienza** al momento opportuno.
- Non duplicare la spesa di un credito né emettere due volte la stessa ricevuta.
- Conservare lezioni svolte, annullamenti, ricevute e penalità concluse; servono a verificare report e decisioni del sistema.
- Permettere a uno stesso account di possedere più ruoli senza concedere automaticamente quelli non assegnati.

---

<a id="scenari"></a>
## 15. Caso completo di Marco e altri scenari di verifica

Gli scenari non aggiungono requisiti nuovi: mostrano come applicare insieme decisioni già documentate.

### Scenario A — Il caso di Marco, spiegato correttamente

Il resoconto riporta una traccia nella quale **Marco è terzo in lista d'attesa**, il suo certificato scade il giorno dopo, alle 17:00 si libera un posto e alle 18:00 l'istruttrice comunica di essere malata; il corso è alle 18:30.

1. **Ordine della lista:** il PO ha chiarito che il passaggio direttamente al terzo era un refuso. Si contatta prima il candidato avente diritto secondo ordine e priorità applicabili; **Marco non subentra automaticamente perché è terzo**.
2. **Certificato:** il suo certificato scade **domani**, quindi è ancora valido **oggi** per la lezione delle 18:30.
3. **Offerta:** a 90 minuti dall'inizio, un candidato contattato riceve una finestra di **30 minuti** secondo R4; per ottenere il posto deve accettare e risultare ancora idoneo.
4. **Credito:** se Marco è il candidato a cui arriva validamente l'offerta e l'accetta, viene **riservato** un ingresso; non se ne scalano due.
5. **Malattia della titolare:** alle 18:00 si cercano supplenti. Se nessuno accetta entro le **18:30**, la singola lezione è annullata.
6. **Conseguenza:** gli iscritti vengono avvisati, il credito riservato torna disponibile e **non** viene applicata una penalità.

**Perché è un buon caso di test:** collega liste d'attesa, tempistiche, certificato, sostituzioni e restituzione dei crediti. Il caso va raccontato senza ripetere l'errore iniziale «il terzo passa prima degli altri».

### Scenario B — «Zero crediti disponibili», ma ingresso consentito

Un iscritto ha un solo credito, lo riserva per Yoga e visualizza **zero crediti disponibili**. Al momento dell'ingresso non deve essere respinto **solo** per il saldo zero: ha già un credito riservato alla prenotazione. Va comunque verificata la validità del certificato e dell'abbonamento.

### Scenario C — Due attese, una prenotazione

Sara è in lista d'attesa per Yoga 18:00–19:00 e Pilates 18:30–19:30. Può restare in entrambe le liste. Quando accetta validamente Yoga, le attese incompatibili (Pilates) vengono rimosse. Non può confermare anche Pilates nello stesso intervallo.

### Scenario D — Penalità diverse a parità di assenza

- Con **pacchetto a ingressi**, un no-show fa perdere **un credito**, senza blacklist.
- Con **mensile illimitato**, un no-show blocca le **nuove prenotazioni per cinque giorni**; le prenotazioni già confermate restano valide.

### Scenario E — Soglia e annullamento

La lezione ha soglia minima **5** e soltanto **3 prenotati** un'ora prima: viene annullata. Se ne ha **5** al momento del controllo e uno cancella dopo, il sistema non riesegue automaticamente il controllo della stessa soglia.

### Scenario F — Rischio di chiusura di un corso

Nelle ultime 10 lezioni già trascorse di Pilates, 7 sono state annullate dal centro. Il gestore vede una segnalazione. È il **gestore**, non un algoritmo, a decidere se interrompere il corso. Le lezioni passate non vengono cancellate dallo storico.

### Scenario G — Fascia studenti

Uno studente con pacchetto agevolato può prenotare una lezione **13:30–14:30** (inizio incluso nella fascia), ma non una lezione **14:00–15:00**.

### Scenario H — Congelamento in due intervalli

Un annuale ha già **20 giorni** di sospensione per infortunio. Se l'iscritto chiede altri **15 giorni**, la reception deve rifiutare la richiesta: `20 + 15 = 35 > 30`.

---

<a id="theme-epic"></a>
## 16. I 5 Theme e le 20 Epic del gruppo

La sezione 6 del vecchio `resocontoFinale.md` conteneva **una prima bozza incompleta** delle Epic («da discutere»). Durante il lavoro al Product Backlog il gruppo ha definito una struttura più precisa di **5 Theme e 20 Epic**. Questa tabella è quella usata per organizzare le **68 User Story** nei quattro fogli e nel backlog principale.

> I nomi e la suddivisione riportati qui provengono dalla **struttura condivisa dal gruppo nel percorso di lavoro**, non dal testo originario dei due Markdown allegati. Li includiamo per evitare che qualcuno riprenda per errore la vecchia bozza di Epic del resoconto.

| Theme | Epic | Responsabilità |
|---|---|---|
| **T1 · Anagrafica, accesso e conformità sanitaria** | **E1.1 — Registrazione e anagrafica iscritti** | Creare, correggere e cercare gli iscritti. |
| T1 | **E1.2 — Accesso e ruoli** | Login, funzioni per ruolo, utenti multi-ruolo. |
| T1 | **E1.3 — Certificato medico** | Registrazione, caricamento, validazione, stato, blocco prenotazioni, avvisi, privacy. |
| **T2 · Abbonamenti, crediti e pagamenti** | **E2.1 — Tipi di abbonamento** | Listino, prezzi, servizi inclusi, fasce orarie. |
| T2 | **E2.2 — Assegnazione e consultazione abbonamenti** | Vendita in reception, stato dell'abbonamento, rinnovi. |
| T2 | **E2.3 — Crediti (ingressi)** | Riserva, consumo, restituzione e perdita dei crediti. |
| T2 | **E2.4 — Pagamenti e ricevute** | Pagamenti in reception e nel sistema, ricevute. |
| T2 | **E2.5 — Congelamento per infortunio** | Sospensione, limite 30 giorni, proroga, prenotazioni coinvolte. |
| **T3 · Palinsesto, prenotazioni e liste d'attesa** | **E3.1 — Palinsesto e strutture** | Sale, capienze, corsi ricorrenti e soglia minima. |
| T3 | **E3.2 — Prenotazioni** | Prenotare, cancellare, consultare, impedire sovrapposizioni. |
| T3 | **E3.3 — Liste d'attesa e subentri** | Code, offerte con scadenza, accettazione, idoneità. |
| T3 | **E3.4 — Priorità e penalità** | Bonus di priorità, blocco temporaneo delle nuove prenotazioni. |
| **T4 · Operatività giornaliera** | **E4.1 — Controllo accessi** | Ingresso con esito e motivi del rifiuto. |
| T4 | **E4.2 — Uscite e presenze** | Check-out, presenza alle attività, no-show, posti occupati. |
| T4 | **E4.3 — Istruttori** | Disponibilità, titolari, supplenti, lezioni assegnate. |
| T4 | **E4.4 — Sostituzioni e annullamenti** | Sostituzioni, avvisi e annullamenti delle singole lezioni. |
| T4 | **E4.5 — Notifiche** | Avvisi nel sistema e, dove previsto, via e-mail. |
| **T5 · Monitoraggio e statistiche del gestore** | **E5.1 — Affluenza e occupazione** | Ingressi per fascia/giorno e partecipazione ai corsi. |
| T5 | **E5.2 — Incassi e abbonamenti** | Incassi, rinnovi, andamento dei piani. |
| T5 | **E5.3 — Storico corsi e chiusura** | Storico lezioni, soglia del 70%, interruzione dei corsi. |

**Una distinzione utile:** la **perdita del credito** per cancellazione tardiva si descrive principalmente in **E2.3**, perché è una regola del saldo. Il **blocco di cinque giorni** e il **bonus di priorità** stanno invece in **E3.4**. Sono regole collegate, ma non sono la stessa funzionalità.

---

<a id="backlog"></a>
## 17. Dalle regole alle User Story, ai test e agli Sprint

### Come utilizzare questo documento con l'Excel

Il Product Backlog unico comprende **68 User Story** organizzate per priorità, con ID, Theme ed Epic. I quattro fogli originari (Annarella, Gandolfo, Peppe, Tessari) sono stati usati per lavorare in parallelo; il backlog principale è l'elenco di prodotto da pianificare.

Per ogni User Story:

1. Individuare **Theme ed Epic** per capire quale parte del sistema copre.
2. Cercare qui la **regola di business** corrispondente (R1–R10).
3. Leggere il **perché** della decisione e i **casi limite**, non soltanto il titolo.
4. Se la storia sta per entrare in uno Sprint, completare i **criteri di accettazione** in forma «Dato / Quando / Allora».
5. Stimare con **Planning Poker** insieme al gruppo, usando **1, 2, 3, 5, 8, 13**; se è troppo ampia, discutere uno **spezzamento** e riportarlo nel foglio apposito.
6. Implementare e verificare anche i casi di errore/annullamento, non solo il comportamento normale.

### Alcuni collegamenti concreti

| User Story / area del backlog | Regole da consultare | Cosa verificare |
|---|---|---|
| Riservare un credito alla prenotazione | R1, R3 | Nessun doppio uso; nessun credito bloccato per una semplice attesa. |
| Cancellare una prenotazione | R1, R2, R4 | Confine delle 2 ore; liberazione posto; eventuale offerta al successivo. |
| Prenotare due attività | R3, R10 | Intervalli sovrapposti e distinzione tra attesa/conferma. |
| Accettare un subentro | R3, R4, R10 | Scadenza, idoneità attuale, riserva del credito e annullamento altre attese. |
| Autorizzare un ingresso | R1, R5, R10 | Certificato, prenotazione già confermata, credito eventualmente già riservato. |
| Rilevare un no-show | R1, R2, R5 | Verifica a fine attività; mai per un'attività annullata. |
| Congelare un abbonamento | R6, R9 | Certificato d'infortunio, massimo 30 giorni, proroga, prenotazioni future. |
| Creare un corso | R7, R10 | Capienza, soglia minima, durata, titolare e supplenti. |
| Annullare una lezione | R1, R7 | Notifica, restituzione crediti, conservazione dello storico. |
| Segnalare un corso problematico | R8 | 7 annullamenti su 10, nessuna eliminazione automatica. |
| Vendere un pacchetto studenti | R9, R10 | Prezzo, idoneità, inizio attività nella fascia ammessa. |

### Perché i criteri di accettazione non sono tutti compilati subito

Il backlog evolve nel tempo. È ragionevole che le storie più lontane siano inizialmente meno dettagliate; **prima di svilupparle**, il gruppo dovrà fissare abbastanza criteri per sapere quando sono completate. Lasciare un criterio ancora da scrivere **non significa** lasciare una contraddizione o una regola sbagliata.

### Ordine suggerito di implementazione — non è uno Sprint Planning già approvato

1. Account, ruoli, certificati e tipi di abbonamento.
2. Palinsesto, capienze, prenotazioni e riserva crediti.
3. Entrate/uscite, presenze, cancellazioni e restituzioni.
4. Liste d'attesa, offerte, priorità e sovrapposizioni.
5. Penalità, congelamenti, sostituzioni e annullamenti automatici.
6. Storico, report, controllo del 70% e pagamenti via sistema simulati/integrati secondo le possibilità del progetto.

L'ordine effettivo sarà scelto dal gruppo in base a **priorità, dipendenze e Story Point**.

---

<a id="punti-aperti"></a>
## 18. Punti da confermare o specificare durante lo sviluppo

Questi elementi sono lasciati espliciti per **non fingere che i due documenti stabiliscano dettagli che non stabiliscono**. Non impediscono di usare il backlog, ma è meglio chiuderli prima della relativa implementazione.

| Tema | Che cosa risulta dalle fonti | Che cosa resta da decidere |
|---|---|---|
| **Prezzo pacchetto 10 ingressi** | Il pacchetto è richiesto dalla traccia; il documento operativo propone **100 €**. | Confermare che il prezzo proposto sia stato accettato dall'intero gruppo. |
| **Rinnovo di pacchetti senza scadenza** | Un solo abbonamento attivo; il rinnovo «alla scadenza» funziona per mensile/annuale, non per pacchetti senza scadenza. | Formalizzare se il nuovo pacchetto parte all'esaurimento del precedente o come si gestisce un acquisto anticipato. |
| **Ingresso singolo** | Il listino prevede **15 €**, ma il ciclo operativo è descritto soprattutto per i pacchetti. | Precisare se si registra come titolo monouso/credito singolo e quando si attiva. |
| **Idoneità allo sconto studenti** | È necessario applicare lo sconto solo a chi ne ha diritto. | Decidere la modalità concreta di verifica (in reception o dichiarazione poi validata). |
| **Numero minimo dei corsi** | Il gestore sceglie una soglia, sempre non superiore alla capienza. | Definire il valore per **ogni corso effettivamente creato**; non esiste una soglia fissa universale nei materiali. |
| **Liste d'attesa per sala pesi e piscina** | Le capienze e le prenotazioni sono obbligatorie; gli esempi di subentro riguardano soprattutto i corsi. | Confermare se lo stesso sistema di coda viene esteso anche alle fasce pesi/piscina o solo ai corsi. |
| **Canali e-mail/notifiche** | Gli avvisi sono richiesti; nel backlog sono state previste notifiche interne e via e-mail. | Stabilire il comportamento tecnico in caso di mancato invio, senza cambiare le penali o lo stato delle prenotazioni. |
| **Implementazione pagamenti online** | Sono richiesti pagamento nel sistema e ricevuta. | Scegliere se realizzare integrazione vera o simulazione chiaramente dichiarata nel contesto didattico. |

**Cosa fare quando si decide uno di questi punti:** registrare data, decisione, motivazione e User Story interessate; aggiornare questo documento e il backlog, evitando versioni contraddittorie.

---

<a id="storico"></a>
## 19. Cronologia: come sono cambiate le decisioni

### 22 ottobre 2026 — Q&A con il Product Owner

Negli appunti di `resocontoFinale.md` sono registrati i chiarimenti su certificati, credito per attività, capienze, prenotazioni sovrapposte, liste d'attesa, Marco, istruttori, congelamenti, storico e pagamenti. Rimanevano da determinare alcuni **numeri e politiche** del prodotto.

### Prima proposta del gruppo — successivamente approvata e poi in parte modificata

La sezione 5 del vecchio resoconto raccoglieva le proposte su fascia studenti, tempi di risposta, penalità, bonus di priorità, soglia del 70% e listino. Durante il lavoro le scelte sono state dichiarate **approvate dal gruppo**, ma alcuni dettagli sono stati **semplificati o precisati successivamente**.

| Tema | Versione iniziale riportata nel vecchio resoconto | Versione da applicare adesso | Perché è cambiata |
|---|---|---|---|
| **Penalità pacchetti / no-show** | Blacklist anche per alcune assenze; collegamento fra no-show e priorità. | **Pacchetti:** si perde soltanto il credito; **nessuna blacklist**. | Evitare una doppia penalità su crediti già pagati. |
| **Penalità mensili/annuali** | Blacklist 5 giorni, esclusione da lezione successiva dello stesso tipo e priorità abbassata. | **Blocco delle nuove prenotazioni per 5 giorni**, senza priorità bassa. | Regola più semplice, separata dalla coda. |
| **Priorità attese** | Bonus dopo 3 fallimenti; mescolato a penalità/blacklist. | Bonus **alto** dopo 3 esclusioni consecutive, valido per una edizione; nessuna priorità bassa. | Separare il premio per esclusioni dalle sanzioni. |
| **Finestra subentro** | «Oltre due giorni», «giorno prima», «giorno stesso», «meno di un'ora»: intervalli informali. | Fasce **>48 h**, **(24,48] h**, **(1,24] h**, **≤1 h**, con deadline non oltre l'inizio. | Eliminare ambiguità ai confini temporali. |
| **Durata sala pesi** | Fascia operativa precedente da 60 minuti. | **120 minuti**; piscina **60 minuti**. | Durata sala pesi giudicata più realistica dal gruppo. |
| **Soglia annullamenti** | 70% negli ultimi 30 giorni **oppure** nelle ultime 10 lezioni. | **Almeno 7 nelle ultime 10 lezioni**; solo avviso al gestore. | Denominatore fisso e verificabile. |
| **Congelamento** | Il PO specificava massimo 30 giorni, senza tutte le conseguenze operative. | Solo abbonamenti a tempo, giorni cumulabili, proroga scadenza e cancellazione senza penali delle prenotazioni interessate. | Limitare i casi e mantenere corrette scadenze/prenotazioni. |
| **Listino** | 15 €, 8 ingressi a 85 €, 12 a 110 €, mensile 200 €, annuale −15%. | Come prima; 10 ingressi a **100 € come proposta** e annuale calcolato a **2.040 €**. | Includere il pacchetto da 10 della traccia, chiarendo il calcolo dell'annuale. |
| **Temi/Epic** | Prima bozza incompleta in `resocontoFinale.md`, con varie voci «da discutere». | **5 Theme e 20 Epic** definiti nel lavoro successivo. | Organizzare tutte le User Story nel Product Backlog unico. |

### 27 ottobre 2026 — Documento operativo consolidato

`FitSlot_regole_operative_consolidate.md` fissa il funzionamento di crediti, penalità, attese, subentri, presenze, congelamenti, corsi, soglia del 70%, listino e controlli trasversali. **Le precisazioni operative non vanno citate come se fossero nuove richieste del docente.**

### Come aggiornare questo file in futuro

Quando il gruppo decide una modifica, aggiungere qui una nota nel formato:

```text
Data:
Decisione precedente:
Nuova decisione:
Motivazione:
User Story / Epic interessate:
Chi l'ha approvata (gruppo):
```

In questo modo GitHub conserverà anche la cronologia delle versioni tramite commit.

---

<a id="glossario"></a>
## 20. Glossario e riferimenti

| Termine | Significato in FitSlot |
|---|---|
| **Theme** | Macro-area del prodotto, per esempio abbonamenti o prenotazioni. |
| **Epic** | Gruppo di User Story che riguardano un obiettivo funzionale comune. |
| **User Story** | Esigenza descritta come «Come [ruolo], voglio [bisogno], così da [beneficio]». |
| **Criterio di accettazione** | Comportamento verificabile, spesso «Dato / Quando / Allora». |
| **Credito / token / ingresso** | Un'unità utilizzabile per una **singola attività**. |
| **Disponibile / riservato / consumato** | Stati del credito per pacchetti a ingressi. |
| **No-show** | Prenotazione confermata senza presenza alla fine dell'attività. |
| **Blacklist / blocco** | Per mensili e annuali, impossibilità di effettuare nuove prenotazioni per cinque giorni; **non** è una posizione bassa in lista. |
| **Lista d'attesa** | Richiesta di posto non ancora confermata. |
| **Subentro** | Accettazione di un posto che si è liberato dopo essere stati in attesa. |
| **Corso ricorrente** | Programmazione ripetuta (es. Yoga ogni martedì). |
| **Lezione** | Una specifica occorrenza datata di un corso. |
| **Check-in/out fisico** | Entrata/uscita dal **centro**. |
| **Presenza all'attività** | Partecipazione verificata a una **singola lezione/fascia**. |
| **Refinement** | Approfondimento progressivo di una User Story prima dello sviluppo. |
| **Planning Poker** | Stima collegiale delle User Story con carte, distinguendo valore e difficoltà. |

### Documenti di origine

- **`resocontoFinale.md`** — contesto, appunti del PO del 22 ottobre, proposte iniziali, caso Marco, prima bozza dei temi.
- **`FitSlot_regole_operative_consolidate.md`** — regole R1–R10, revisioni e dettagli implementativi del 27 ottobre.
- **Product Backlog `.xlsx` del gruppo** — 68 User Story ordinate, con Theme, Epic, valore di business, criteri dove presenti e future stime/spezzamenti.

### Date di progetto riportate nel resoconto

- **29 ottobre 2026:** consegna Product Backlog `GruppoXX.xlsx`.
- **12 novembre 2026:** Sprint 1.
- **26 novembre 2026:** Sprint 2.
- **10 dicembre 2026:** Sprint 3.
- **Gennaio 2027:** Sprint 4 (nel resoconto non è specificato il giorno).

> **In sintesi:** per capire *cosa* implementare si parte dal Product Backlog; per capire *perché* una regola esiste si consulta il [registro decisioni](#registro-decisioni) e la sezione R1–R10; per sapere *come è cambiata* rispetto alle idee iniziali si consulta lo [storico](#storico). In caso di dubbio, non usare una vecchia proposta come se fosse ancora valida.
