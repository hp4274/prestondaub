-- Archived reference only. Production auth is Node + Supabase (see server/).
-- To seed the legacy MySQL admins table, run:
--   admin/_archive/setup/migrate-add-role-column.php
-- with LEGACY_MIGRATE_PASSWORD_USER1 and LEGACY_MIGRATE_PASSWORD_USER2 set
-- (see admin/_archive/legacy-php-support/.env.example).

-- Step 1: Add role column (if not already present)
-- ALTER TABLE admins ADD COLUMN role VARCHAR(50) DEFAULT 'admin' AFTER name;

-- Step 2: Insert rows — generate bcrypt hashes locally (e.g. php -r "echo password_hash('your-secret', PASSWORD_BCRYPT);")
-- and substitute <BCRYPT_HASH_1> / <BCRYPT_HASH_2> before running:
--
-- INSERT INTO admins (email, password, name, role) VALUES
-- ('andrew.cisel@gmail.com', '<BCRYPT_HASH_1>', 'Andrew Cisel', 'admin'),
-- ('rumit@keryar.com', '<BCRYPT_HASH_2>', 'Rumit', 'restricted')
-- ON DUPLICATE KEY UPDATE password = VALUES(password), name = VALUES(name), role = VALUES(role);
