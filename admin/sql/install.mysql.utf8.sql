DROP TABLE IF EXISTS `#__anchor`;

CREATE TABLE `#__anchor` (
	`anchor_id`       INT(11)     NOT NULL AUTO_INCREMENT,
	`article_alias` VARCHAR(255) NULL DEFAULT NULL,
	`keyword` VARCHAR(255) NULL DEFAULT NULL,
	`new_keyword` VARCHAR(255) NULL DEFAULT NULL,
	`inner_url` VARCHAR(2048) NULL DEFAULT NULL,
	`target_url` VARCHAR(2048)  NULL DEFAULT NULL,
	`published` tinyint(2)NOT NULL DEFAULT 0,
	`match_state` tinyint(2)NOT NULL DEFAULT 0,
	`created_date` datetime NULL DEFAULT CURRENT_TIMESTAMP,
	`modified_date` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`remark` VARCHAR(255),
	PRIMARY KEY (`anchor_id`)
)
	ENGINE=InnoDB
	AUTO_INCREMENT =0
	DEFAULT CHARSET=utf8mb4
	DEFAULT COLLATE=utf8mb4_unicode_ci;