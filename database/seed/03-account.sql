-- Account demo associati agli iscritti presenti nel seed utenti.
INSERT INTO account (id, password_hash, stato)
SELECT
    utenti.id,
    NULL,
    'PENDING'
FROM utenti
WHERE utenti.email IN (
    'anna.rossi@example.test',
    'marco.bianchi@example.test',
    'giulia.verdi@example.test',
    'giuseppe.rossi@example.test'
)
ON DUPLICATE KEY UPDATE
    stato = VALUES(stato);