-- MySQL Workbench Synchronization
-- Generated: 2016-02-11 11:56
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__oscampus_pathways`
CHANGE COLUMN `access` `access` INT(11) NOT NULL ,
ADD COLUMN `metadata` TEXT NOT NULL AFTER `ordering`;

ALTER TABLE `#__oscampus_courses`
CHANGE COLUMN `released` `released` DATE NOT NULL COMMENT 'First date to make public' ,
ADD COLUMN `metadata` TEXT NOT NULL AFTER `released`;

ALTER TABLE `#__oscampus_lessons`
ADD COLUMN `length` INT(11) NOT NULL DEFAULT 0 AFTER `footer`,
ADD COLUMN `metadata` TEXT NOT NULL AFTER `ordering`;

ALTER TABLE `#__oscampus_certificates`
ADD COLUMN `snapshot` TEXT NOT NULL AFTER `date_earned`;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
