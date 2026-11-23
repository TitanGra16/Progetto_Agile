-- Ruoli degli account demo usati per verificare la UE-04.
INSERT IGNORE INTO account_ruoli (account_id, ruolo_id)
SELECT utenti.id, ruoli.id
FROM utenti
JOIN ruoli ON ruoli.nome = 'ISCRITTO'
WHERE utenti.email IN (
    'anna.rossi@example.test',
    'giulia.verdi@example.test'
)
UNION ALL
SELECT utenti.id, ruoli.id
FROM utenti
JOIN ruoli ON ruoli.nome = 'RECEPTION'
WHERE utenti.email = 'marco.bianchi@example.test'
UNION ALL
SELECT utenti.id, ruoli.id
FROM utenti
JOIN ruoli ON ruoli.nome = 'ISTRUTTORE'
WHERE utenti.email = 'giulia.verdi@example.test'
UNION ALL
SELECT utenti.id, ruoli.id
FROM utenti
JOIN ruoli ON ruoli.nome = 'GESTORE'
WHERE utenti.email = 'giuseppe.rossi@example.test';
