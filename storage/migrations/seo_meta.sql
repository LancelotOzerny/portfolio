CREATE TABLE IF NOT EXISTS seo_meta (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    target_type VARCHAR(50) NOT NULL,
    target_key VARCHAR(150) NOT NULL,

    title VARCHAR(255) NULL,
    description VARCHAR(320) NULL,
    canonical_url VARCHAR(500) NULL,

    robots_index TINYINT(1) NOT NULL DEFAULT 1,
    robots_follow TINYINT(1) NOT NULL DEFAULT 1,

    og_title VARCHAR(255) NULL,
    og_description VARCHAR(320) NULL,
    og_image VARCHAR(500) NULL,

    created_at DATETIME NULL,
    updated_at DATETIME NULL,

    UNIQUE KEY uniq_target (target_type, target_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
