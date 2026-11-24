-- Run once on an existing installation before creating the two named accounts.
ALTER TABLE admins ADD COLUMN role VARCHAR(30) NOT NULL DEFAULT 'administrator';
