-- Aggiunge le voci mancanti del listino iniziale.
INSERT IGNORE INTO tipi_abbonamento (
    codice, nome, categoria, prezzo,
    ingressi_totali, durata_mesi, servizi_inclusi,
    sconto_studenti_percentuale, studenti_dalle, studenti_alle
) VALUES
    ('SINGOLO', 'Ingresso singolo', 'SINGOLO', 15.00, 1, NULL,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), DEFAULT, NULL, NULL),

    ('PACK_8', 'Pacchetto 8 ingressi', 'PACCHETTO', 85.00, 8, NULL,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), 20, '08:00:00', '13:30:00'),
    ('PACK_10', 'Pacchetto 10 ingressi', 'PACCHETTO', 100.00, 10, NULL,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), 20, '08:00:00', '13:30:00'),
    ('PACK_12', 'Pacchetto 12 ingressi', 'PACCHETTO', 110.00, 12, NULL,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), 20, '08:00:00', '13:30:00'),

    ('MENSILE', 'Mensile illimitato', 'TEMPO', 200.00, NULL, 1,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), DEFAULT, NULL, NULL),
    ('ANNUALE', 'Annuale illimitato', 'TEMPO', 2040.00, NULL, 12,
     JSON_ARRAY('SALA_PESI', 'PISCINA', 'CORSI'), DEFAULT, NULL, NULL);
