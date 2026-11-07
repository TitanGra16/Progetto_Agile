ALTER TABLE account
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN stato ENUM('PENDING', 'ACTIVE') NOT NULL DEFAULT 'PENDING',
    ADD COLUMN activation_token_hash VARCHAR(64) NULL,
    ADD COLUMN activation_expires_at DATETIME NULL,
    ADD COLUMN activated_at DATETIME NULL;

UPDATE account
SET stato = 'ACTIVE'
WHERE password_hash IS NOT NULL;
