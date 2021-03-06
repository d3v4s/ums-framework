-- CREATE DATABASE --
CREATE SCHEMA `ums` DEFAULT CHARACTER SET utf8mb4;
USE `ums`;

-- CREATE TABLES --

-- new table for roles of users
CREATE TABLE `roles` (
	`id_role` int(2) unsigned NOT NULL PRIMARY KEY,
	`role` varchar(20) NOT NULL DEFAULT 0,
	`create_user` bit(1) NOT NULL DEFAULT 0,
	`update_user` bit(1) NOT NULL DEFAULT 0,
	`delete_user` bit(1) NOT NULL DEFAULT 0,
	`unlock_user` bit(1) NOT NULL DEFAULT 0,
	`restore_user` bit(1) NOT NULL DEFAULT b'0',
	`change_pass` bit(1) NOT NULL DEFAULT 0,
	`remove_session` bit(1) NOT NULL DEFAULT b'0',
	`remove_enabler_token` bit(1) NOT NULL DEFAULT b'0',
	`gen_rsa` bit(1) NOT NULL DEFAULT 0,
	`gen_sitemap` bit(1) NOT NULL DEFAULT 0,
	`change_settings` bit(1) NOT NULL DEFAULT 0,
	`send_email` bit(1) NOT NULL DEFAULT 0,
	`view_tables` bit(1) NOT NULL DEFAULT 0,

	UNIQUE `role` (`role`)
);

-- new table for users
CREATE TABLE `users` (
	`id_user` INT(15) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL ,
	`username` VARCHAR(64) NOT NULL ,
	`email` VARCHAR(64) NOT NULL ,
	`password` VARCHAR(255) NOT NULL ,
	`role_id` INT(5) unsigned NOT NULL DEFAULT '2',
	`enabled` BOOLEAN NOT NULL DEFAULT FALSE ,
	`registration_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	`expire_lock` datetime NULL DEFAULT NULL,

	UNIQUE `username` (`username`),
	UNIQUE `email` (`email`)
);

-- new table for deleted users
CREATE TABLE `deleted_users` (
	`id_deleted_user` int(15) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`id_user` int(15) unsigned NOT NULL,
	`name` varchar(255) NOT NULL,
	`username` varchar(64) NOT NULL,
	`email` varchar(64) NOT NULL,
	`role_id` int(5) unsigned NOT NULL,
	`registration_datetime` datetime NOT NULL,
	`delete_datetime` datetime NOT NULL DEFAULT current_timestamp(),

	FOREIGN KEY (`role_id`) REFERENCES `roles` (`id_role`) ON DELETE NO ACTION,
	UNIQUE `id_user` (`id_user`),
	INDEX `username` (`username`),
	INDEX `email` (`email`)
);

-- new table for reset passwword request
CREATE TABLE `password_reset_requests` (
	`id_password_reset_request` int(15) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` int(15) unsigned NOT NULL,
	`password_reset_token` varchar(255) NULL,
	`ip_address` varchar(45) NOT NULL,
	`expire_datetime` datetime NOT NULL,

	FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE NO ACTION,
	UNIQUE `password_reset_token` (`password_reset_token`)
);

-- new table for pending new emails
CREATE TABLE `pending_emails` (
	`id_pending_email` int(15) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` int(15) unsigned NOT NULL,
	`new_email` varchar(64) NOT NULL,
	`enabler_token` varchar(255) NULL,
	`expire_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

	FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
	UNIQUE `enabler_token` (`enabler_token`)
);

-- create table for pending user
CREATE TABLE `pending_users` (
	`id_pending_user` int(15) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` int(15) unsigned NULL,
	`name` varchar(255) NOT NULL,
	`username` varchar(64) NOT NULL,
	`email` varchar(64) NOT NULL,
	`password` varchar(255) NOT NULL,
	`role_id` int(5) unsigned NOT NULL DEFAULT '2',
	`registration_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`enabler_token` varchar(255) NULL,
	`expire_datetime` datetime NOT NULL,

	FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
	FOREIGN KEY (`role_id`) REFERENCES `roles` (`id_role`),
	UNIQUE `enabler_token` (`enabler_token`)
);

-- create table for sessions
CREATE TABLE `sessions` (
	`id_session` int(50) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` int(15) unsigned NOT NULL,
	`session_token` varchar(255) NULL,
	`ip_address` varchar(45) NOT NULL,
	`expire_datetime` datetime NOT NULL,

	FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
	UNIQUE `session_token` (`session_token`),
	INDEX `ip_address` (`ip_address`)
);

-- create table for user locks
CREATE TABLE `user_locks` (
	`id_user_lock` int(15) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`user_id` int(15) unsigned NOT NULL,
	`count_wrong_password` int(5) unsigned NOT NULL DEFAULT '0',
	`expire_wrong_password` datetime NULL,
	`count_locks` int(5) NOT NULL DEFAULT '0',

	FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`),
	UNIQUE `user_id` (`user_id`)
);

-- ADD TRIGGER --
DELIMITER ;;
CREATE TRIGGER `add_user_locks` AFTER INSERT ON `users` FOR EACH ROW
	INSERT INTO user_locks (user_id) SELECT id_user FROM users ORDER BY id_user DESC LIMIT 1;;
DELIMITER ;

-- INSERT ROLES --

-- insert admin role
INSERT INTO `roles` (`id_role`, `role`, `create_user`, `update_user`, `delete_user`, `unlock_user`, `restore_user`, `change_pass`, `remove_session`, `remove_enabler_token`, `gen_rsa`, `gen_sitemap`, `change_settings`, `send_email`, `view_tables`, `mange_real_estate`)
	VALUES (0, 'admin', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- insert editor role
INSERT INTO `roles` (`id_role`, `role`, `create_user`, `update_user`, `delete_user`, `unlock_user`, `restore_user`, `change_pass`, `remove_session`, `remove_enabler_token`, `gen_rsa`, `gen_sitemap`, `change_settings`, `send_email`, `view_tables`, `mange_real_estate`)
	VALUES (1, 'editor', 0, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1);

-- insert user role
INSERT INTO `roles` (`id_role`, `role`, `create_user`, `update_user`, `delete_user`, `unlock_user`, `restore_user`, `change_pass`, `remove_session`, `remove_enabler_token`, `gen_rsa`, `gen_sitemap`, `change_settings`, `send_email`, `view_tables`, `mange_real_estate`)
	VALUES (2, 'user', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- INSERT USER --

-- insert admin user
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role_id`, `enabled`)
	VALUES ('admin', 'admin', 'ums@ums.ums', '$2y$10$ESXw8SyNrP5Cj.7FxqnZruTnBHGPuOVON4b5bOqlWtIX4HRKWk2Pq', 0, 1);

-- ADD DB USER --

-- create db user for ums
CREATE USER 'ums'@'localhost' IDENTIFIED BY 'ums';

GRANT ALL PRIVILEGES ON `ums`.* TO 'ums'@'localhost';

FLUSH PRIVILEGES;
