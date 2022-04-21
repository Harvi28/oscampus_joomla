-- MySQL Workbench Synchronization
-- Generated: 2015-12-16 15:54
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

CREATE TABLE IF NOT EXISTS `#__oscampus_files_links` (
  `files_id`   INT(11) NOT NULL,
  `courses_id` INT(11) NOT NULL,
  `lessons_id` INT(11) NULL DEFAULT NULL,
  `ordering`   INT(11) NOT NULL,
  PRIMARY KEY (`files_id`, `courses_id`, `lessons_id`),
  INDEX `fk_courses_id_idx` (`courses_id` ASC),
  INDEX `fk_lessons_id_idx` (`lessons_id` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

DROP TABLE IF EXISTS `#__oscampus_files_lessons` ;

DROP TABLE IF EXISTS `#__oscampus_files_courses` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
