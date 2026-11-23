CREATE TABLE sale (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    capienza INT UNSIGNED NOT NULL,
    inizio_occupazione TIME NOT NULL,
    fine_occupazione TIME NOT NULL,
    CHECK (capienza > 0),
    UNIQUE KEY nome_unico (nome)
) ENGINE=InnoDB;