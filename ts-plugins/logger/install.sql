CREATE TABLE IF NOT EXISTS `log` (
	`id` varchar(36) NOT NULL, `type` varchar(250) NOT NULL,
  	`data` text NOT NULL COMMENT 'JSON',
  	`date` timestamp(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP INDEX IF EXISTS `type` ON `log`; -- MariaDB only
ALTER TABLE `log` ADD INDEX(`type`);