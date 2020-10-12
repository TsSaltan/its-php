CREATE TABLE IF NOT EXISTS `log` (
	`id` varchar(36) NOT NULL, `type` varchar(250) NOT NULL,
  	`data` text NOT NULL COMMENT 'JSON',
  	`date` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP INDEX IF EXISTS `type` ON `log`; -- MariaDB only
ALTER TABLE `log` ADD INDEX(`type`);

ALTER TABLE `log` CHANGE `data` `data` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'JSON'; 