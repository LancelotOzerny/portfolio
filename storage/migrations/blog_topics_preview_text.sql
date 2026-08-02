ALTER TABLE blog_topics
    ADD COLUMN preview_text VARCHAR(500) NULL AFTER title;

UPDATE blog_topics
SET preview_text = description
WHERE preview_text IS NULL
  AND description IS NOT NULL;
