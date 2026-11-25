INSERT INTO sale (nome, capienza, inizio_occupazione, fine_occupazione)
VALUES
    ('Sala A', 50, '09:00:00', '17:00:00'),
    ('Sala B', 30, '10:00:00', '15:00:00'),
    ('Sala C', 20, '08:30:00', '12:30:00'),
    ('Sala D', 40, '14:00:00', '18:00:00'),
    ('Sala E', 25, '11:00:00', '16:00:00'),
    ('Sala F', 35, '07:00:00', '13:00:00'),
    ('Sala G', 45, '12:00:00', '20:00:00'),
    ('Sala H', 15, '09:30:00', '14:30:00'),
    ('Sala I', 60, '10:30:00', '19:30:00'),
    ('Sala J', 55, '08:00:00', '18:00:00'),
    ('Sala Pesi', 200, '05:00:00', '23:59:59'),
    ('Piscina', 100, '08:00:00', '21:00:00'),
    ('Sala Spinning', 20, '09:00:00', '19:00:00')
ON DUPLICATE KEY UPDATE
    capienza = VALUES(capienza),
    inizio_occupazione = VALUES(inizio_occupazione),
    fine_occupazione = VALUES(fine_occupazione);
