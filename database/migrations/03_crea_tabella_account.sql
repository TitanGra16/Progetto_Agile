CREATE TABLE IF NOT EXISTS account (
    id BIGINT UNSIGNED PRIMARY KEY,

    password_hash VARCHAR(255) NOT NULL,

    CONSTRAINT fk_account_utente
        FOREIGN KEY (id)
        REFERENCES utenti(id)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
