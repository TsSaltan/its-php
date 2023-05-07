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

CREATE TABLE IF NOT EXISTS `blog-categories` (
	`id` INT NOT NULL AUTO_INCREMENT, 
	`parent-id` INT NOT NULL DEFAULT '-1' , 
	`title` VARCHAR(255) NOT NULL, 
	`alias` VARCHAR(255) NULL DEFAULT NULL, 
	UNIQUE KEY (`alias`),
	PRIMARY KEY (`id`)
) CHARACTER SET utf8 COLLATE utf8_general_ci; 

CREATE TABLE `blog-post-to-category` (
	`post-id` INT NOT NULL, 
	`category-id` INT NOT NULL
) CHARACTER SET utf8 COLLATE utf8_general_ci; 