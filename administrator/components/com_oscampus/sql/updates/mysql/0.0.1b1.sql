-- MySQL Workbench Synchronization
-- Generated: 2015-10-30 12:52
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__oscampus_pathways`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
ADD COLUMN `access` INT(11) NULL DEFAULT NULL AFTER `published`;

UPDATE `#__oscampus_pathways`
SET access = 1
WHERE access IS NULL;

ALTER TABLE `#__oscampus_teachers`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_courses`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_modules`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_lessons`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ,
ADD INDEX `idx_ordering` (`ordering` ASC);

ALTER TABLE `#__oscampus_certificates`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_courses_pathways`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_users_lessons`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_tags`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_courses_tags`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_files`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_files_courses`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_files_lessons`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

ALTER TABLE `#__oscampus_wistia_downloads`
CHARACTER SET = utf8 , COLLATE = utf8_general_ci ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
