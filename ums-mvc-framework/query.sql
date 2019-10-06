CREATE TABLE `ums`.`users` (
	`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR(255) NOT NULL ,
	`username` VARCHAR(64) NOT NULL ,
	`email` VARCHAR(64) NOT NULL ,
	`password` VARCHAR(255) NOT NULL ,
	`roletype` ENUM('user','editor','admin') NOT NULL DEFAULT 'user' ,
	PRIMARY KEY (`id`), UNIQUE `u_username` (`username`), UNIQUE `u_email` (`email`)
) ENGINE = InnoDB;

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `roletype`) VALUES (NULL, 'ums', 'ums', 'ums@ums.it', 'ums', 'admin');

CREATE USER 'ums'@'localhost' IDENTIFIED BY PASSWORD 'ums';

GRANT ALL PRIVILEGES ON `ums`.* TO 'ums'@'localhost';

ALTER TABLE `users` ADD `enabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER `roletype`, ADD `hash_confirm` VARCHAR(255) NULL AFTER `enabled`, ADD UNIQUE `idu_hash_confirm` (`hash_confirm`);