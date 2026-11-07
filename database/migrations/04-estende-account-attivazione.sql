ALTER TABLE account
    MODIFY password_hash VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS activation_token_hash VARCHAR(64) NULL,
    ADD COLUMN IF NOT EXISTS activation_expires_at DATETIME NULL,
    ADD COLUMN IF NOT EXISTS activated_at DATETIME NULL;
