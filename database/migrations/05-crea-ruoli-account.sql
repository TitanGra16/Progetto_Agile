CREATE TABLE IF NOT EXISTS ruoli (
    id TINYINT UNSIGNED PRIMARY KEY,
    nome VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS account_ruoli (
    account_id BIGINT UNSIGNED NOT NULL,
    ruolo_id TINYINT UNSIGNED NOT NULL,

    PRIMARY KEY (account_id, ruolo_id),

    CONSTRAINT fk_account_ruoli_account
        FOREIGN KEY (account_id)
        REFERENCES account(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_account_ruoli_ruolo
        FOREIGN KEY (ruolo_id)
        REFERENCES ruoli(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO ruoli (id, nome)
VALUES
    (1, 'ISCRITTO'),
    (2, 'RECEPTION'),
    (3, 'ISTRUTTORE'),
    (4, 'GESTORE')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);
