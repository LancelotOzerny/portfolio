ALTER TABLE `projects`
    ADD COLUMN `category_id` INT UNSIGNED NULL DEFAULT NULL AFTER `active`,
    ADD COLUMN `tags` VARCHAR(1000) NOT NULL DEFAULT '' AFTER `category_id`,
    ADD INDEX `projects_category_id_idx` (`category_id`);

UPDATE `projects` AS `project`
INNER JOIN (
    SELECT `project_id`, MIN(`tag_id`) AS `category_id`
    FROM `projects_tags`
    GROUP BY `project_id`
) AS `legacy_category` ON `legacy_category`.`project_id` = `project`.`id`
SET `project`.`category_id` = `legacy_category`.`category_id`
WHERE `project`.`category_id` IS NULL;

UPDATE `projects` AS `project`
LEFT JOIN (
    SELECT
        `relation`.`project_id`,
        GROUP_CONCAT(`tag`.`name` ORDER BY `relation`.`tag_id` SEPARATOR ', ') AS `tags`
    FROM `projects_tags` AS `relation`
    INNER JOIN `tags` AS `tag` ON `tag`.`id` = `relation`.`tag_id`
    INNER JOIN (
        SELECT `project_id`, MIN(`tag_id`) AS `category_id`
        FROM `projects_tags`
        GROUP BY `project_id`
    ) AS `legacy_category` ON `legacy_category`.`project_id` = `relation`.`project_id`
    WHERE `relation`.`tag_id` <> `legacy_category`.`category_id`
    GROUP BY `relation`.`project_id`
) AS `legacy_tags` ON `legacy_tags`.`project_id` = `project`.`id`
SET `project`.`tags` = COALESCE(`legacy_tags`.`tags`, '');
