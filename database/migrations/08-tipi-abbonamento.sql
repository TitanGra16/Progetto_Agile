CREATE TABLE IF NOT EXISTS tipi_abbonamento (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    categoria ENUM('SINGOLO', 'PACCHETTO', 'TEMPO') NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    ingressi_totali SMALLINT UNSIGNED NULL,
    durata_mesi SMALLINT UNSIGNED NULL,
    servizi_inclusi JSON NOT NULL,
    sconto_studenti_percentuale TINYINT UNSIGNED NOT NULL DEFAULT 0,
    studenti_dalle TIME NULL,
    studenti_alle TIME NULL,
    attivo BOOLEAN NOT NULL DEFAULT TRUE
);
