INSERT INTO utenti (nome, cognome, data_nascita, email, recapito)
VALUES
    ('Anna', 'Rossi', '1995-04-12', 'anna.rossi@example.test', '3331234567'),
    ('Marco', 'Bianchi', '1988-09-25', 'marco.bianchi@example.test', '3337654321'),
    ('Giulia', 'Verdi', '2001-01-30', 'giulia.verdi@example.test', '1234567890')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    cognome = VALUES(cognome),
    data_nascita = VALUES(data_nascita),
    recapito = VALUES(recapito);
