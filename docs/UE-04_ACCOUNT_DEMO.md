# UE-04 — Account demo e ruoli

Questi account servono per provare localmente le viste e i permessi della UE-04.

| Account | Email | Ruolo | Stato iniziale |
|---|---|---|---|
| Anna Rossi | `anna.rossi@example.test` | Iscritto | `PENDING` |
| Marco Bianchi | `marco.bianchi@example.test` | Reception | `ACTIVE` |
| Giulia Verdi | `giulia.verdi@example.test` | Istruttore + Iscritto | `ACTIVE` |
| Giuseppe Rossi | `giuseppe.rossi@example.test` | Gestore | `ACTIVE` |

## Credenziali locali

Gli account interni hanno una password demo condivisa dal gruppo tramite un canale
privato. La password non viene salvata nella repository pubblica.

## Regole di attivazione

- L'account dell'iscritto viene attivato tramite il link inviato via email.
- Reception e gestore sono account fissi creati dal sistema e non passano dall'attivazione email.
- Un account istruttore viene creato dal gestore e non passa dall'attivazione pubblica.
- Un istruttore può avere anche il ruolo di iscritto, come nell'account demo di Giulia.
- Il gestore può consultare le attività svolte dagli altri ruoli.

Dopo `composer db:setup`, gli account interni sono già attivi. Anna può essere attivata dalla pagina pubblica di richiesta attivazione.
