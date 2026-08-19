-- Коды пользовательских ролей
-- Выполнить на сервере через Разработка → SQL запросы.

ALTER TABLE `user_rights`
	ADD COLUMN `code` varchar(64) NOT NULL DEFAULT '' AFTER `role`;

UPDATE `user_rights`
SET `code` = 'guest'
WHERE `role` = 'Гость' AND `code` = '';

UPDATE `user_rights`
SET `code` = 'admin'
WHERE `role` = 'Админ' AND `code` = '';

UPDATE `user_rights`
SET `code` = LOWER(REPLACE(TRIM(`role`), ' ', '-'))
WHERE `code` = '' AND `role` REGEXP '^[A-Za-z0-9 _-]+$';

UPDATE `user_rights`
SET `code` = CONCAT('role-', `id`)
WHERE `code` = '';

ALTER TABLE `user_rights`
	ADD UNIQUE INDEX `user_rights_code` (`code`);
