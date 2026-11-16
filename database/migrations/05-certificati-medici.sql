-- UE-05: una copia PDF corrente del certificato per ogni iscritto.
-- La validita si calcola confrontando data_scadenza con la data odierna.
CREATE TABLE IF NOT EXISTS certificati_medici (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utente_id BIGINT UNSIGNED NOT NULL,
    data_scadenza DATE NOT NULL,
    verificato_at DATETIME NOT NULL,
    nome_file VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_certificato_utente (utente_id),
    CONSTRAINT fk_certificato_utente FOREIGN KEY (utente_id)
        REFERENCES utenti(id)
        ON DELETE RESTRICT
);