-- API-токены пользователей
-- Выполнить на сервере через Разработка → SQL запросы.

CREATE TABLE IF NOT EXISTS `api_tokens` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`created_at` timestamp NOT NULL DEFAULT current_timestamp(),
	`changed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`user_id` int(10) unsigned NOT NULL,
	`token_hash` char(64) NOT NULL,
	`token_encrypted` text NOT NULL,
	`expires_at` datetime NOT NULL,
	`revoked_at` datetime DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `api_tokens_hash` (`token_hash`),
	KEY `api_tokens_user` (`user_id`),
	CONSTRAINT `api_tokens_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
