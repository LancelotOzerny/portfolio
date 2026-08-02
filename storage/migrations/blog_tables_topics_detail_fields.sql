ALTER TABLE blog_topics
    ADD COLUMN IF NOT EXISTS detail_text TEXT NULL AFTER preview_text,
    ADD COLUMN IF NOT EXISTS detail_image_path VARCHAR(500) NULL AFTER detail_text;
