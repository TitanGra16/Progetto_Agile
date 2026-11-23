-- Account demo associati agli iscritti presenti nel seed utenti.
INSERT INTO account (id, password_hash, stato)
SELECT
    utenti.id,
    CASE
        WHEN utenti.email IN (
            'marco.bianchi@example.test',
            'giulia.verdi@example.test',
            'giuseppe.rossi@example.test'
        ) THEN '$2y$12$41T8tBvNXcAGeaecczkaSOTzhhELOS5z2DPFz33y.IdYDKBCqVX32'
        ELSE NULL
    END,
    CASE
        WHEN utenti.email IN (
            'marco.bianchi@example.test',
            'giulia.verdi@example.test',
            'giuseppe.rossi@example.test'
        ) THEN 'ACTIVE'
        ELSE 'PENDING'
    END
FROM utenti
WHERE utenti.email IN (
    'anna.rossi@example.test',
    'marco.bianchi@example.test',
    'giulia.verdi@example.test',
    'giuseppe.rossi@example.test'
)
ON DUPLICATE KEY UPDATE
    password_hash = IF(account.stato = 'ACTIVE', account.password_hash, VALUES(password_hash)),
    stato = IF(account.stato = 'ACTIVE', account.stato, VALUES(stato));
