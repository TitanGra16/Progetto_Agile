**Appunti e dubbi per il prof - Progetto P3 (FitSlot)**

**Accesso al sistema e Ingressi**

1. **Utenti del sistema:** Gli iscritti accedono direttamente loro (tipo da web/app) per prenotarsi e vedere lo stato di abbonamento e certificato, o passa comunque tutto dalla reception?

Entrambi

2. **Conteggio presenze e uscite:** Nella traccia il gestore dice che vorrebbe capire quando il centro è pieno o vuoto, però si parla solo di registrare gli ingressi alla reception. Dobbiamo prevedere anche un modo per registrare quando uno esce (o fare una stima su fascia oraria)?

Si

3. **Come funziona l'abbonamento a 10 ingressi:** Un singolo "ingresso" dà accesso a tutto (sala pesi, piscina, corsi) nella stessa giornata o vale per una singola attività?

Singola attività

4. **Scalare gli ingressi:** A chi ha il pacchetto da 10 ingressi, quando glielo scaliamo? Nel momento in cui prenota il corso, quando subentra dalla lista d'attesa o solo quando passa fisicamente in reception? E se il corso salta perché manca l'istruttore, l'ingresso gli torna indietro in automatico?

Dipende dalla lista, dal tempo limite per cancellarsi ecc
Se il corso salta gli torna indietro in automatico l'ingresso

**Prenotazioni, Liste d'attesa e Penali**

5. **Il caso di Marco (lista d'attesa):** Nell'esempio della traccia, Marco è terzo in lista d'attesa, si libera un solo posto alle 17:00 e subentra lui. Perché salta i primi due? Il sistema controlla in automatico chi ha il certificato scaduto o ha finito gli ingressi e li scarta dalla coda? (Tra l'altro nel testo anche a Marco scade il certificato il giorno dopo, quindi andrebbe bloccato anche lui?). Inoltre, il subentro è diretto o bisogna dare un tempo limite per confermare?

Il fatto che salta i primi due è un errore della traccia
Il certificato scade il giorno dopo, ma il corso
Il primo in lista d'attesa viene notificato se vuole subentrare o meno, e se comunica in tempo che non può subentrare non subisce una penalità

6. **Penale sotto le 2 ore:** La regola dice che chi cancella a meno di due ore dall'inizio perde un ingresso, "anche con l'abbonamento a ingressi". Ma per chi ha il mensile o l'annuale (che non ha ingressi contati) cosa comporta questa penale? Un blocco temporaneo delle prenotazioni o altro?

Enra in gioco la policy sulle priorita, quindi poi non ti puoi registrare la volta dopo, scegliamo noi i vincoli

7. **Capienza strutture:** Il limite massimo di posti e la prenotazione valgono solo per i corsi nelle sale, o dobbiamo gestire una capienza massima anche per gli ingressi liberi in piscina e sala pesi?

Si, è tutto a capienza


**Abbonamenti, Certificati e Corsi (se c'è tempo)**

8. **Certificato medico:** Ci basta salvare la data di scadenza inserita dalla reception o serve caricare il file? E il blocco all'ingresso quando scade è totale o la reception può forzare l'entrata (magari con un avviso)?

Caricare il file, e versione cartacea in reception
Quindi è prevista una scansione da parte della segretaria per confermare che il certificato sia in validità (ovviamente basta una volta)

9. **Congelamento abbonamento (max 30 gg):** I 30 giorni per infortunio sono cumulabili nell'anno (es. 10 giorni ora e 20 tra tre mesi) o vanno usati in un blocco unico? Quando congelo, la data di fine abbonamento slitta in avanti e le eventuali prenotazioni già fatte in quei giorni si cancellano da sole?

Per anno si intende da data di iscrizione a data di scadenza
I 30 giorni sono cumulabili


10. **Ridotto studenti ("solo mattina"):** Cosa intendiamo per mattina (es. fino alle 13:00 o 14:00)? Il blocco vale per l'orario in cui il corso si svolge (non può proprio prenotare i corsi del pomeriggio/sera)?

La fascia orario la decidiamo noi

11. **Avvisi e imprevisti sui corsi:** Quando salta un corso o cambia un istruttore all'ultimo, come vengono avvisati gli iscritti? Dobbiamo prevedere invio di email/notifiche in app, o una lista di persone da contattare per la reception? E per i corsi con "pochi iscritti" (es. i 3 iscritti di cui si lamenta l'istruttore), c'è un annullamento automatico sotto una certa soglia o decide il gestore a mano?

Le notifiche a nostro piacimento
La soglia minima è gestita dal gestore (va calcolata tra iscritti e pagamento dell'istruttore)
Decidiamo una percentuale per cui se il corso non viene svolto (es 70% delle volte annullato) allora il gestore può decidere di cancellare totalmente il corso
Quindi serve uno storico dei corsi

12. **Gestione istruttori e pagamenti:** Per ridurre i buchi d'orario degli istruttori basta dare al gestore una vista chiara delle disponibilità settimanali e delle sale, giusto? Mentre per i pagamenti in reception, ci limitiamo a registrare che l'abbonamento è stato saldato (importo, data e metodo) per calcolare gli incassi?

Per i pagamenti possono essere fatti sia da app sia alla reception, ovviamente per gli incassi ci deve essere una ricevuta di pagamento

**Consegna del 29 ottobre**

13. **File Excel del Backlog:** Per la consegna del 29 ottobre, i criteri di accettazione (Dato/Quando/Allora) li scriviamo direttamente dentro la cella della storia o aggiungiamo una colonna/foglio nel template Excel?

Come vogliamo, basta che sia chiaro


**DOMANDE DI ALTRI GRUPPI**

App desktop della palestra, un'unica interfaccia (possibilità anche web distribuita) dove l'utente accede con le credenziali (che sia iscritto, istruttore, gestore)
Ci sono utenti che possono avere più ruoli (un iscritto può essere anche istruttore)
La prenotazione è gestita su crediti (token)
Se cancelli in tempo una prenotazione, i crediti non li perdi, altrimenti
Ogni corso può avere un principale e dei supplenti, in caso di rinuncia da parte del principale
Un istruttore può insegnare più corsi
Anche la sostituzione di insegnanti è gestita su app
Avvisi della scadenza del certificato: 6 mesi, 1 mese, 1 settimana, 4 giorni, domani
Se un iscritto non ha il certificato in regola, **NON PUO' PROPRIO PRENOTARSI SUL SISTEMA**
I corsi sono FISSI
Ogni utente ha una proprio priorità, per esempio se si iscrive e non si presenta finisce in una "black list"
Il primo in lista d'attesa viene notificato se vuole subentrare o meno, e se comunica in tempo che non può subentrare non subisce una penalità
Se un corso viene cancellato
Ci sono varie fasce di abbonamenti, ovviamente un mensile che prevede 15 ingressi deve costare meno di 15 ingressi pagati a pacchetti, comunque è a nostra discrezione, basta che i prezzi siano coerenti
SOVRAPPOSIZIONE DI ISCRIZIONE DI CORSI VIETATA, in lista d'attesa invece è consentito (se due corsi sono alla stessa ora e sono in entrambi in lista d'attesa, e ne confermo uno, l'altro va automaticamente rifiutato)
Se una persona si iscrive a un corso e per tante volte ripetutamente (es mai riuscito a fare pilates il martedì) non riesce a subentrare