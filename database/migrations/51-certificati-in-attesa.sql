-- UE-51: certificati caricati dagli iscritti
-- in attesa di verifica da parte della Reception.

CREATE TABLE IF NOT EXISTS certificati_medici_in_attesa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    utente_id BIGINT UNSIGNED NOT NULL,
    nome_file VARCHAR(80) NOT NULL,
    caricato_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_certificato_in_attesa_utente (utente_id),

    CONSTRAINT fk_certificato_in_attesa_utente
        FOREIGN KEY (utente_id)
        REFERENCES utenti(id)
        ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
