CREATE TABLE IF NOT EXISTS blog_article_topic_relations (
    article_id INT UNSIGNED NOT NULL,
    topic_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (article_id, topic_id),
    INDEX idx_blog_article_topic_relations_topic_id (topic_id),
    CONSTRAINT fk_blog_article_topic_relations_article
        FOREIGN KEY (article_id)
        REFERENCES blog_articles (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_blog_article_topic_relations_topic
        FOREIGN KEY (topic_id)
        REFERENCES blog_topics (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO blog_article_topic_relations (article_id, topic_id)
SELECT id, topic_id
FROM blog_articles;
