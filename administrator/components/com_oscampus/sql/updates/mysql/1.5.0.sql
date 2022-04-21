-- MySQL Workbench Synchronization
-- Generated: 2019-06-11 09:05
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = '';

ALTER TABLE `#__oscampus_certificates`
  RENAME TO `#__oscampus_courses_certificates`,
  ADD COLUMN `certificates_id` INT(11) NOT NULL AFTER `date_earned`,
  ADD INDEX `idx_certificates_id` (`certificates_id` ASC);

ALTER TABLE `#__oscampus_courses`
  CHANGE COLUMN `image` `image` VARCHAR(255) NOT NULL COMMENT 'Thumbnail image for course';

CREATE TABLE IF NOT EXISTS `#__oscampus_certificates`
(
  `id`               INT(11)      NOT NULL AUTO_INCREMENT,
  `title`            VARCHAR(255) NOT NULL,
  `default`          INT(11)      NOT NULL DEFAULT 0,
  `image`            VARCHAR(255) NULL     DEFAULT NULL,
  `font`             VARCHAR(100) NULL     DEFAULT NULL,
  `fontsize`         INT(11)      NULL     DEFAULT NULL,
  `fontcolor`        CHAR(6)      NULL     DEFAULT NULL,
  `movable`          TEXT         NULL     DEFAULT NULL,
  `published`        INT(11)      NOT NULL DEFAULT 1,
  `created`          DATETIME     NULL     DEFAULT NULL,
  `created_by`       INT(11)      NULL     DEFAULT NULL,
  `created_by_alias` VARCHAR(255) NULL     DEFAULT NULL,
  `modified`         DATETIME     NULL     DEFAULT NULL,
  `modified_by`      INT(11)      NULL     DEFAULT NULL,
  `checked_out`      INT(11)      NULL     DEFAULT NULL,
  `checked_out_time` DATETIME     NULL     DEFAULT NULL,
  PRIMARY KEY (`id`)
)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8;

SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;
