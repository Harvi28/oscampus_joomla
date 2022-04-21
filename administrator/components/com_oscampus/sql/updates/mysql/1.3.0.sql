-- MySQL Workbench Synchronization
-- Generated: 2018-11-14 09:54
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

CREATE TABLE IF NOT EXISTS `#__oscampus_downloads` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `users_id` INT(11) NOT NULL,
  `ip` CHAR(15) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `download_id` VARCHAR(255) NOT NULL,
  `lesson_type` VARCHAR(10) NOT NULL,
  `downloaded` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_users_id` (`users_id` ASC),
  INDEX `idx_download_id` (`download_id` ASC))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

#
# This will be handled in the install script
#
#DROP TABLE IF EXISTS `#__oscampus_wistia_downloads` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
