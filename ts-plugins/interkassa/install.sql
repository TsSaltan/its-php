CREATE TABLE IF NOT EXISTS `cash` (
  `owner` int(11) NOT NULL,
  `balance` decimal(10,4) NOT NULL,
  UNIQUE KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cash_log` ( 
	`id` INT NOT NULL , 
	`owner` INT NOT NULL , 
	`balance` DECIMAL(10,4) NOT NULL , 
	`description` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL , 
	`timestamp` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL , 
	UNIQUE KEY `id` (`id`)
) ENGINE = InnoDB;