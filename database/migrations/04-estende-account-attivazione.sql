ALTER TABLE account
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN activation_token_hash VARCHAR(64) NULL,
    ADD COLUMN activation_expires_at DATETIME NULL,
    ADD COLUMN activated_at DATETIME NULL;
