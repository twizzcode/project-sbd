-- Add username and password fields to owner table for portal access
-- Run this migration to enable owner self-registration

ALTER TABLE owner 
ADD COLUMN username VARCHAR(50) UNIQUE AFTER owner_id,
ADD COLUMN password VARCHAR(255) AFTER username,
ADD COLUMN registered_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER tanggal_registrasi,
ADD COLUMN last_login DATETIME AFTER registered_at;

-- Add index for username lookup
CREATE INDEX idx_owner_username ON owner(username);
