# FitSlot — Regole operative consolidate per il Product Backlog

**Progetto P3 · Metodi di sviluppo agile, UniAQ — 27 ottobre 2026**

## Come leggere questo documento

Le regole tratte dalla traccia e dal Q&A fornito dal gruppo restano i vincoli di partenza. I parametri riportati nella sezione 5 di `resocontoFinale.md` sono **già stati approvati dal gruppo** e sono mantenuti. Le precisazioni qui sotto sono **scelte operative proposte su richiesta del gruppo**, necessarie a eliminare le ambiguità prima di aggiornare l'Excel: non sono nuove dichiarazioni del professore.

Per le User Story si descriveranno ruolo, bisogno e beneficio; gli stati, i numeri e i casi limite qui definiti alimenteranno soprattutto i criteri di accettazione.

## R1 — Crediti, prenotazioni e rimborsi

- Un credito dà diritto a **una sola attività**, non all'intera giornata. Per i pacchetti a ingressi, una prenotazione confermata **riserva** un credito (non ancora consumato): quel credito non è disponibile per altre prenotazioni.
- L'inserimento in lista d'attesa **non riserva né consuma** crediti. La riserva avviene soltanto quando l'utente accetta validamente il subentro.
- Quando viene registrata la partecipazione all'attività, il credito riservato diventa **consumato**.
- Se l'iscritto cancella **almeno due ore prima** dell'inizio (`>= 2 h`), il credito torna disponibile senza penalità. Se cancella **meno di due ore** prima (`< 2 h`) perde il credito, convertendo la riserva in consumo. Se non si presenta, perde il credito e si applica anche la regola R2.
- Se il centro annulla l'attività, tutte le prenotazioni vengono cancellate **senza penalità** e i crediti riservati vengono liberati; un credito già contabilizzato come consumato viene riaccreditato.
- Per gli abbonamenti **mensili/annuali illimitati** non si riservano crediti numerici, ma restano obbligatorie le verifiche di abbonamento, prenotazione e presenza. La cancellazione tardiva e il no-show seguono R2.
- Il sistema non deve mai sottrarre lo stesso credito due volte. La disponibilità corrisponde a crediti acquistati meno crediti riservati meno crediti consumati.

## R2 — Penalità e blacklist di cinque giorni

- La **cancellazione tardiva** con pacchetto a ingressi costa un credito; **non aggiunge da sola** una blacklist. La cancellazione tardiva con abbonamento mensile o annuale attiva invece una **blacklist di cinque giorni**. Il **no-show** attiva la blacklist di cinque giorni con qualunque tipo di abbonamento; con pacchetto comporta anche la perdita del credito.
- La blacklist ha **due effetti**: per cinque giorni la priorità dell'utente è **bassa** in tutte le liste d'attesa; inoltre non può effettuare una nuova prenotazione o mettersi in lista per **la successiva occorrenza dello stesso corso ricorrente** che ha generato la penalità, anche se tale lezione cade oltre i cinque giorni.
- Non si annullano retroattivamente altre prenotazioni già confermate. Se l’utente è già in lista d’attesa per la specifica occorrenza bloccata, la sua posizione viene rimossa senza penalità. La restrizione si applica ai **nuovi tentativi** di prenotazione/subentro. Dopo cinque giorni la priorità torna normale, ma il blocco mirato alla successiva occorrenza resta fino al suo svolgimento.
- Eventuali penalità multiple non si sommano in un numero indefinito di giorni: si mantiene come scadenza il giorno più lontano tra i blocchi attivi e si conservano i riferimenti alle singole prossime occorrenze bloccate.
- La blacklist non impedisce accessi consentiti alla struttura per attività già prenotate o diversi servizi inclusi nell'abbonamento.

## R3 — Priorità delle liste d'attesa

- In ogni coda le richieste sono ordinate con queste priorità: **alta**, **normale**, **bassa (blacklist)**. A parità di priorità, viene prima chi è entrato in lista per primo.
- La priorità **alta** viene assegnata dopo **tre edizioni settimanali consecutive dello stesso corso ricorrente** nelle quali l'utente è rimasto in lista senza ottenere un posto. Vale soltanto per **l'edizione successiva** di quel corso e poi scade, anche se l'utente non la utilizza.
- Un'iscrizione in lista rifiutata volontariamente, abbandonata o lasciata scadere **non conta** come tentativo fallito. Se l'utente salta un'edizione settimanale, la sequenza dei tre tentativi riparte da zero. Anche un posto confermato interrompe la sequenza.
- Se un utente in blacklist ha anche un bonus alto, la **priorità bassa prevale** finché dura la blacklist.
- È consentito stare in attesa su più attività sovrapposte. Quando si accetta un subentro, si rimuovono automaticamente le altre richieste di attesa e offerte pendenti sovrapposte; resta comunque impossibile avere due prenotazioni confermate contemporanee.

## R4 — Offerta di subentro

- Si contatta **un candidato alla volta**, partendo dal primo idoneo secondo R3. Finché l'offerta è valida, il posto è temporaneamente riservato a quel candidato e non è assegnabile a un secondo.
- **Finestra di risposta**, calcolata rispetto al tempo che manca all'inizio dell'attività quando si invia l'offerta:
  - oltre **48 ore**: **3 ore**;
  - da oltre **24 ore** fino a **48 ore** incluse: **1 ora**;
  - da oltre **1 ora** fino a **24 ore** incluse: **30 minuti**;
  - fino a **1 ora** inclusa: **15 minuti**.
- La scadenza effettiva dell'offerta è il **minore** tra l'orario previsto dalla finestra e **l'inizio dell'attività**. Nessuna offerta viene inviata dopo l'inizio.
- Solo per le offerte con finestra di tre ore, se il candidato non ha ancora risposto, arriva un promemoria **30 minuti prima** della scadenza.
- Rifiuto e mancata risposta **non provocano penalità**: si passa al successivo idoneo. All'accettazione il sistema ricontrolla certificato, abbonamento e crediti disponibili, fasce orarie, eventuale blocco e sovrapposizioni. Se i requisiti non ci sono più, l'offerta decade senza penalità.
- Una volta accettato il subentro, valgono le normali regole della prenotazione, compresa la cancellazione a meno di due ore.

## R5 — Presenze, ingressi, uscite e no-show

- La reception registra **entrata e uscita fisica dal centro**, così il gestore può vedere quante persone sono presenti. Un secondo ingresso non va registrato finché non è stata chiusa o corretta l'uscita precedente.
- La **presenza alla singola attività** è distinta dalla presenza nel centro. Per i corsi la conferma viene registrata dall'istruttore (o, in sua assenza, dalla reception); per sala pesi e piscina la reception associa il check-in alla prenotazione della fascia oraria.
- La presenza alla singola attività converte il credito riservato in consumato. Il solo ingresso nel centro non basta a segnare un corso come frequentato.
- Alla **fine della fascia oraria**, le prenotazioni confermate senza presenza registrata sono marcate come **no-show** e trattate secondo R1 e R2. Una lezione annullata dal centro non genera no-show.
- Il gestore può consultare due dati differenti: **presenze reali correnti** tramite check-in/out e **posti prenotati** per attività/fascia. Non vanno confusi.

## R6 — Congelamento per infortunio

- Per semplicità il congelamento si applica solo agli abbonamenti **a tempo (mensile e annuale)**. I pacchetti a ingressi **non hanno una scadenza temporale**: conservano i crediti finché non utilizzati, perciò non necessitano di congelamento.
- Il richiedente presenta documentazione medica per infortunio; la reception registra e approva il periodo. La sospensione decorre da una data corrente o futura, non retroattiva.
- Il limite è di **30 giorni complessivi**, anche frazionati, **per ciascun abbonamento e il suo originario periodo di validità**. Il prolungamento non crea un nuovo diritto ad altri 30 giorni.
- Durante la sospensione non si effettuano nuove prenotazioni né ingressi coperti dall'abbonamento congelato. Le prenotazioni che cadono nel periodo sono cancellate automaticamente **senza penali** e gli eventuali crediti/residui prenotati vengono liberati.
- La data di scadenza dell'abbonamento **slitta in avanti** di tanti giorni quanti sono quelli di sospensione autorizzati. Alla data di fine congelamento l'abbonamento torna attivo in automatico.

## R7 — Corsi ricorrenti, minimo di iscritti e sostituzioni

- Ogni corso ha un **modello ricorrente** (sala, giorno, orario, durata, capienza, istruttore titolare e supplenti, numero minimo di iscritti) e **singole lezioni datate**. Si possono annullare o modificare una singola lezione senza cancellare l'intero corso e senza eliminare lo storico.
- Il gestore imposta il numero minimo di partecipanti, coerente con i costi dell'istruttore, purché non superi la capienza.
- **Un'ora prima** dell'inizio si effettua **un solo controllo definitivo** del numero minimo: se le prenotazioni confermate sono sotto la soglia, la singola lezione viene annullata automaticamente. Gli iscritti sono avvisati e i crediti restituiti secondo R1. Se la soglia era raggiunta in quel controllo, eventuali cancellazioni successive non fanno scattare un secondo annullamento automatico per pochi iscritti: questa scelta evita instabilità nell'ultima ora.
- Se il titolare segnala indisponibilità, il sistema propone la sostituzione ai supplenti; il primo che accetta viene assegnato. Se nessun supplente ha accettato **entro l'orario di inizio**, la lezione viene annullata. Il cambio istruttore e l'annullamento vengono notificati agli iscritti.
- La cancellazione da parte del centro non crea no-show o blacklist e libera le prenotazioni. Dopo l'annullamento non si accettano altre prenotazioni o subentri per quella lezione.

## R8 — Soglia del 70% per i corsi da rivedere

- Per ogni corso ricorrente si considerano le **ultime dieci lezioni programmate la cui data di inizio è già passata**; lezioni ancora future non contano.
- Se **almeno sette su dieci (70%)** risultano annullate dal centro, compare una **segnalazione al gestore**. Prima di avere dieci lezioni osservabili, nessuna segnalazione automatica viene mostrata.
- Contano gli annullamenti per insufficienza di iscritti, assenza di istruttore senza sostituto e altre cancellazioni decise dal centro. Non conta la cancellazione della prenotazione di un singolo iscritto.
- Il sistema **non elimina automaticamente** il corso dal palinsesto: il gestore decide se mantenerlo, modificarlo o interrompere le edizioni future. Se lo interrompe, le lezioni passate restano nello storico; le prenotazioni delle lezioni future eliminate sono rimborsate senza penali.

## R9 — Tipi di abbonamento e prezzi

- Il listino mantiene le decisioni già concordate dal gruppo: **ingresso singolo 15 €; 8 ingressi 85 €; 12 ingressi 110 €; mensile illimitato 200 €; annuale con sconto del 15% rispetto a dodici mensili**, cioè **2.040 €**.
- Poiché la traccia richiede esplicitamente anche il pacchetto da **10 ingressi**, si introduce il prezzo intermedio di **100 €**: è coerente con gli 8 ingressi a 85 € e i 12 a 110 €. Questo prezzo è una **nuova proposta operativa**, non un importo già approvato nella sezione 5.
- **Studenti:** sconto del **20% sui pacchetti a ingressi** (8: 68 €; 10: 80 €; 12: 88 €). La prenotazione è consentita soltanto per attività che **iniziano fra le 08:00 e le 13:30 incluse**, anche se finiscono dopo le 13:30. L'idoneità allo sconto va verificata dalla reception o mediante dato dichiarato e validato.
- I pacchetti a ingressi coprono **una singola attività per credito** e non scadono nel tempo. Gli abbonamenti mensili/annuali illimitati coprono le attività incluse dal listino senza detrarre crediti numerici. I servizi inclusi devono essere elencati esplicitamente per ogni tipologia: **per la prima versione** si considerano inclusi sala pesi, piscina e corsi, salvo il vincolo mattutino degli studenti.
- Per ridurre ambiguità nel conteggio, un iscritto ha **un solo abbonamento attivo alla volta**; può acquistarne un altro come rinnovo, che entra in vigore alla scadenza del precedente. I pagamenti avvengono sia da app sia in reception e **generano sempre una ricevuta**; per la dimostrazione accademica un eventuale pagamento online non integrato va indicato chiaramente come simulazione.

## R10 — Regole trasversali da applicare in ogni storia

1. **Capienza e prenotazione:** corsi, sala pesi e piscina hanno limiti di posti; per coerenza con il resoconto Markdown si richiede prenotazione per tutte e tre le tipologie. Sala pesi e piscina sono suddivise in **fasce prenotabili da 60 minuti**; i corsi usano la loro durata effettiva. Se un posto è temporaneamente offerto a un candidato in lista, resta riservato durante la risposta.
2. **Sovrapposizioni:** non si consentono due prenotazioni confermate il cui intervallo orario si sovrappone; l'attesa su più attività sovrapposte è consentita e viene risolta quando se ne conferma una.
3. **Certificati medici:** il certificato viene caricato dall'utente o presentato alla reception ed è validato dalla reception **una volta per ogni nuovo documento**. Prenotazioni e check-in sono rifiutati se il certificato è assente, non validato o scaduto **alla data dell'attività**. Gli avvisi sono inviati a 6 mesi, 1 mese, 7 giorni, 4 giorni e 1 giorno dalla scadenza.
4. **Ruoli:** uno stesso account può avere più ruoli (es. iscritto e istruttore); ogni funzione e dato sono accessibili soltanto ai ruoli autorizzati.
5. **Pagamenti:** ogni pagamento riuscito, online o alla reception, genera una ricevuta persistente consultabile; non si crea una ricevuta di pagamento riuscito per una transazione fallita.
6. **Storico:** non si cancellano fisicamente le lezioni effettuate, annullate, le ricevute o le penalità concluse; si conserva lo storico per spiegare report e comportamenti del sistema.

## Ordine consigliato di implementazione

1. Account/ruoli, anagrafica, certificato valido e tipi di abbonamento.
2. Palinsesto e lezioni datate, capienze, prenotazione semplice e credito riservato.
3. Check-in/out, presenza alle attività, cancellazione e rimborso.
4. Liste d'attesa, offerte di subentro e gestione delle sovrapposizioni.
5. Blacklist, bonus di priorità, congelamenti e sostituzioni.
6. Report, storico, soglia del 70%, pagamento da app simulato o integrato secondo lo sviluppo disponibile.
