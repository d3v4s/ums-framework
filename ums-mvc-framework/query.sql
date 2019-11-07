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

INSERT INTO `ums`.`users` (`name`, `username`, `email`, `password`, `roletype`) VALUES ('Andrea Serra', 'andreaserra', 'info@andreaserra.it', '$2y$10$0k8fCuGXuwSODy1Ts6XjWeaFF1RfslHiDOuj6dBpYvsRiX2ba0DHa', 'admin');

INSERT INTO `ums`.`users` (`name`, `username`, `email`, `password`, `roletype`) VALUES ('ums', 'ums', 'ums@ums.it', '$2y$10$0k8fCuGXuwSODy1Ts6XjWeaFF1RfslHiDOuj6dBpYvsRiX2ba0DHa', 'admin');

CREATE USER 'ums'@'localhost' IDENTIFIED BY 'ums';

GRANT ALL PRIVILEGES ON `ums`.* TO 'ums'@'localhost';



-- ALTER TABLE `users` ADD `enabled` BOOLEAN NOT NULL DEFAULT FALSE AFTER `roletype`, ADD `token_confirm_email` VARCHAR(255) NULL AFTER `enabled`, ADD UNIQUE `idu_token_confirm_email` (`token_confirm_email`);

-- ALTER TABLE `users` ADD `registration_day` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `enabled`, ADD `token_account_enabler` VARCHAR(255) NULL DEFAULT NULL AFTER `registration_day`, ADD `new_email` VARCHAR(64) NULL DEFAULT NULL AFTER `token_account_enabler`, ADD UNIQUE `u_new_email` (`new_email`), ADD UNIQUE `idu_token_account_enabler` (`token_account_enabler`);

-- ALTER TABLE `users` ADD `token_reset_pass` VARCHAR(255) NULL DEFAULT NULL AFTER `password`, ADD `datetime_req_reset_pass_expire` DATETIME NULL DEFAULT NULL AFTER `token_reset_pass`, ADD UNIQUE `idu_token_reset_pass` (`token_reset_pass`);

-- ALTER TABLE `users` ADD `n_wrong_password` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `token_confirm_email`, ADD `datetime_reset_wrong_password` DATETIME NULL DEFAULT NULL AFTER `n_wrong_password`, ADD `datetime_unlock_user` DATETIME NULL DEFAULT NULL AFTER `datetime_reset_wrong_password`, ADD `n_locks` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `datetime_unlock_user`;
