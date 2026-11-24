-- Run once on databases without the optional EPS field.
ALTER TABLE usuario ADD COLUMN eps VARCHAR(150) NULL DEFAULT NULL;
