ALTER TABLE blog_articles
	ADD COLUMN published_at DATETIME NULL DEFAULT NULL AFTER updated_at;

UPDATE blog_articles
SET published_at = created_at
WHERE enabled = 1
	AND published_at IS NULL;
