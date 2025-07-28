-- Add user preference columns to the users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS preferences JSON NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL;
