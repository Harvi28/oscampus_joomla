-- MySQL Workbench Synchronization
-- Generated: 2016-02-15 10:23
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__oscampus_pathways`
CHANGE COLUMN `users_id` `users_id` INT(11) NOT NULL COMMENT 'User FK of this pathway owner (optional)' ;

ALTER TABLE `#__oscampus_courses`
CHANGE COLUMN `teachers_id` `teachers_id` INT(11) NOT NULL COMMENT 'Teachers FK' ;

ALTER TABLE `#__oscampus_files_links`
CHANGE COLUMN `lessons_id` `lessons_id` INT(11) NOT NULL ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
