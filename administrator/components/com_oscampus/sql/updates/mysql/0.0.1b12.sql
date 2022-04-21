-- MySQL Workbench Synchronization
-- Generated: 2015-12-14 16:29
-- Model: OSCampus Database
-- Version: 1.0
-- Project: OSCampus
-- Author: Bill Tomczak

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='';

ALTER TABLE `#__oscampus_modules`
DROP COLUMN `checked_out_time`,
DROP COLUMN `checked_out`,
DROP COLUMN `modified_by`,
DROP COLUMN `modified`,
DROP COLUMN `created_by_alias`,
DROP COLUMN `created_by`,
DROP COLUMN `created`,
DROP COLUMN `published`,
DROP COLUMN `alias`;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
