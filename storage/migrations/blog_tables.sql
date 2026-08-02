CREATE TABLE IF NOT EXISTS blog_topics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    image_path VARCHAR(500) NULL,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic_id INT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    title VARCHAR(255) NOT NULL,
    preview_text VARCHAR(500) NULL,
    preview_image_path VARCHAR(500) NULL,
    detail_text TEXT NULL,
    detail_image_path VARCHAR(500) NULL,
    author VARCHAR(255) NULL,

    INDEX idx_blog_articles_topic_id (topic_id),
    CONSTRAINT fk_blog_articles_topic
        FOREIGN KEY (topic_id)
        REFERENCES blog_topics (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_article_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    updated_by_name VARCHAR(255) NULL,
    comment_text TEXT NOT NULL,

    INDEX idx_blog_article_comments_article_id (article_id),
    CONSTRAINT fk_blog_article_comments_article
        FOREIGN KEY (article_id)
        REFERENCES blog_articles (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_article_ratings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    ip_address VARCHAR(45) NULL,
    rating TINYINT UNSIGNED NOT NULL,

    INDEX idx_blog_article_ratings_article_id (article_id),
    CONSTRAINT fk_blog_article_ratings_article
        FOREIGN KEY (article_id)
        REFERENCES blog_articles (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT chk_blog_article_ratings_value
        CHECK (rating BETWEEN 1 AND 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
