-- Mt Zion website feature upgrade. Back up mt_zion_school before running.
ALTER TABLE gallery ADD COLUMN category VARCHAR(30) NOT NULL DEFAULT 'students';
ALTER TABLE contact_messages ADD COLUMN tracking_code VARCHAR(20) NULL;
ALTER TABLE contact_messages ADD COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Received';
ALTER TABLE contact_messages ADD COLUMN reply TEXT NULL;
ALTER TABLE contact_messages ADD COLUMN replied_at DATETIME NULL;
CREATE UNIQUE INDEX uq_contact_tracking_code ON contact_messages(tracking_code);
-- New settings are inserted on demand by the admin content editor.
