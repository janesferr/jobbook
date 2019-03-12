-- create database for logins
CREATE DATABASE `app_login`;

-- create the user account to access the login database
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'mTD6UdawCFxXtqu3s';
GRANT SELECT, INSERT, UPDATE, DELETE ON `app_login`.* TO 'app_user'@'localhost';

-- create the user table to hold users for the application
CREATE TABLE IF NOT EXISTS `app_login`.`users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(32) NOT NULL,
  `password` char(128) NOT NULL,
  `salt` char(128) NOT NULL,
  `role` varchar(64) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- create the database for job book data
CREATE DATABASE `app_jobbook`;

-- create the user account to access the jobs database
CREATE USER 'app_jobbook_user'@'localhost' IDENTIFIED BY 'uvTGJDZhFTpUh7rdT';
GRANT SELECT, INSERT, UPDATE, DELETE ON `app_jobbook`.* TO 'app_jobbook_user'@'localhost';

-- create the jobs table to hold the job data
CREATE TABLE IF NOT EXISTS `app_jobbook`.`jobs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `job_number` INT(11) NOT NULL UNIQUE,
  `date` VARCHAR(128) NOT NULL,
  `client_name` VARCHAR(128) NOT NULL,
  `description` TEXT,
  `initials` VARCHAR(45) DEFAULT NULL,
  `invoice_date` VARCHAR(128) DEFAULT NULL,
  `p_number` VARCHAR(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;










