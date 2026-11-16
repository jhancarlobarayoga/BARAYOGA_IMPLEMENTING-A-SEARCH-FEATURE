-- Run this file ONCE on an existing webdev_business_db created by the previous project version.
-- It copies the old timestamps into the required date_added and last_updated columns.
USE webdev_business_db;

ALTER TABLE programmers ADD COLUMN date_added DATETIME NULL AFTER address;
UPDATE programmers SET date_added = COALESCE(created_at, CURRENT_TIMESTAMP) WHERE date_added IS NULL;
ALTER TABLE programmers MODIFY date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE programmers ADD COLUMN last_updated DATETIME NULL AFTER date_added;
UPDATE programmers SET last_updated = COALESCE(last_updated_at, created_at, CURRENT_TIMESTAMP) WHERE last_updated IS NULL;
ALTER TABLE programmers MODIFY last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE clients ADD COLUMN date_added DATETIME NULL AFTER project_name;
UPDATE clients SET date_added = COALESCE(created_at, CURRENT_TIMESTAMP) WHERE date_added IS NULL;
ALTER TABLE clients MODIFY date_added DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE clients ADD COLUMN last_updated DATETIME NULL AFTER date_added;
UPDATE clients SET last_updated = COALESCE(last_updated_at, created_at, CURRENT_TIMESTAMP) WHERE last_updated IS NULL;
ALTER TABLE clients MODIFY last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Existing rows keep their earlier created_at/last_updated_at columns for backup compatibility.
-- New and updated records are handled by the updated PHP pages using date_added/last_updated.
