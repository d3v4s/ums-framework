CREATE SCHEMA `ums` DEFAULT CHARACTER SET utf8mb4;

CREATE TABLE `ums`.`users` (
	`id` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR(255) NOT NULL ,
	`username` VARCHAR(64) NOT NULL ,
	`email` VARCHAR(64) NOT NULL ,
	`password` VARCHAR(255) NOT NULL ,
	`token_reset_pass` VARCHAR(255) NULL DEFAULT NULL ,
	`datetime_req_reset_pass_expire` DATETIME NULL DEFAULT NULL ,
	`roletype` ENUM('user','editor','admin') NOT NULL DEFAULT 'user' ,
	`enabled` BOOLEAN NOT NULL DEFAULT FALSE ,
	`registration_day` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`token_account_enabler` VARCHAR(255) NULL DEFAULT NULL ,
	`new_email` VARCHAR(64) NULL DEFAULT NULL ,
	`token_confirm_email` VARCHAR(255) NULL ,
	`n_wrong_password` INT UNSIGNED NOT NULL DEFAULT '0' ,
	`datetime_reset_wrong_password` DATETIME NULL DEFAULT NULL ,
	`datetime_unlock_user` DATETIME NULL DEFAULT NULL ,
	`n_locks` INT UNSIGNED NOT NULL DEFAULT '0' ,
	
	PRIMARY KEY (`id`),
	UNIQUE `u_username` (`username`),
	UNIQUE `u_email` (`email`),
	UNIQUE `idu_token_reset_pass` (`token_reset_pass`),
	UNIQUE `idu_token_account_enabler` (`token_account_enabler`),
	UNIQUE `u_new_email` (`new_email`),
	UNIQUE `idu_token_confirm_email` (`token_confirm_email`)
) ENGINE = InnoDB;

INSERT INTO `ums`.`users` (`name`, `username`, `email`, `password`, `roletype`, `enabled`) VALUES ('Andrea Serra', 'andreaserra', 'info@andreaserra.it', '$2y$10$ESXw8SyNrP5Cj.7FxqnZruTnBHGPuOVON4b5bOqlWtIX4HRKWk2Pq', 'admin', 1);

INSERT INTO `ums`.`users` (`name`, `username`, `email`, `password`, `roletype`, `enabled`) VALUES ('ums', 'ums', 'ums@ums.it', '$2y$10$ESXw8SyNrP5Cj.7FxqnZruTnBHGPuOVON4b5bOqlWtIX4HRKWk2Pq', 'admin', 1);

CREATE USER 'ums'@'localhost' IDENTIFIED BY 'ums';

GRANT ALL PRIVILEGES ON `ums`.* TO 'ums'@'localhost';