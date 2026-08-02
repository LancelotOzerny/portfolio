ALTER TABLE blog_topics
    ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER updated_at;

ALTER TABLE blog_articles
    ADD COLUMN enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER updated_at;
