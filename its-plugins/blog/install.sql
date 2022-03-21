CREATE TABLE IF NOT EXISTS `blog-posts` ( 
	`id` INT AUTO_INCREMENT, 
	`alias` VARCHAR(255) NOT NULL, 
	`title` VARCHAR(255) NOT NULL, 
	`content` TEXT NOT NULL , 
	`create_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`update_time` TIMESTAMP NULL, 
	`author_id` INT NOT NULL, 
	`type` VARCHAR(1) NOT NULL, 
	UNIQUE KEY (`alias`),
	PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci; 