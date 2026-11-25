# UE-04 — Account demo e ruoli

Questi account servono per provare localmente le viste e i permessi della UE-04.

| Account | Email | Ruolo | Stato iniziale |
|---|---|---|---|
| Anna Rossi | `anna.rossi@example.test` | Iscritto | `PENDING` |
| Marco Bianchi | `marco.bianchi@example.test` | Reception | `ACTIVE` |
| Giulia Verdi | `giulia.verdi@example.test` | Istruttore + Iscritto | `ACTIVE` |
| Giuseppe Rossi | `giuseppe.rossi@example.test` | Gestore | `ACTIVE` |

## Credenziali locali

La password degli account demo attivi (Marco, Giulia e Giuseppe) è `11111111` nel database locale di sviluppo. Anna è in attesa di attivazione e sceglie la propria password tramite il link ricevuto via email.

## Email di attivazione in locale

Avvia Mailpit prima di richiedere il link per `anna.rossi@example.test`.

```sh
mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025
```

Apri il messaggio **Attiva il tuo account FitSlot** nella [casella locale di Mailpit](http://127.0.0.1:8025/) e segui il link per impostare la password.

## Regole di attivazione

- L'account dell'iscritto viene attivato tramite il link inviato via email.
- Reception e gestore sono account fissi creati dal sistema e non passano dall'attivazione email.
- Un account istruttore viene creato dal gestore e non passa dall'attivazione pubblica.
- Un istruttore può avere anche il ruolo di iscritto, come nell'account demo di Giulia.
- Il gestore può consultare le attività svolte dagli altri ruoli.

Dopo `composer db:setup`, gli account interni sono già attivi.
