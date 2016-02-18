-- MySQL Script generated by MySQL Workbench
-- 02/18/16 13:47:49
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`category` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(155) NOT NULL,
  `imgURL` VARCHAR(255) NOT NULL,
  `googleId` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`data` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `creationDate` DATETIME NOT NULL,
  `description` TEXT NULL,
  `imgURL` TEXT NULL,
  `lat` FLOAT NOT NULL,
  `lng` FLOAT NOT NULL,
  `category_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_data_category1_idx` (`category_id` ASC),
  INDEX `fk_data_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_data_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `mydb`.`category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_data_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`dataType`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`dataType` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`company`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`company` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`dataType_has_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`dataType_has_data` (
  `dataType_id` INT NOT NULL,
  `data_id` INT NOT NULL,
  PRIMARY KEY (`dataType_id`, `data_id`),
  INDEX `fk_dataType_has_data_data1_idx` (`data_id` ASC),
  INDEX `fk_dataType_has_data_dataType_idx` (`dataType_id` ASC),
  CONSTRAINT `fk_dataType_has_data_dataType`
    FOREIGN KEY (`dataType_id`)
    REFERENCES `mydb`.`dataType` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_dataType_has_data_data1`
    FOREIGN KEY (`data_id`)
    REFERENCES `mydb`.`data` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`company_has_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`company_has_data` (
  `company_id` INT NOT NULL,
  `data_id` INT NOT NULL,
  PRIMARY KEY (`company_id`, `data_id`),
  INDEX `fk_company_has_data_data1_idx` (`data_id` ASC),
  INDEX `fk_company_has_data_company1_idx` (`company_id` ASC),
  CONSTRAINT `fk_company_has_data_company1`
    FOREIGN KEY (`company_id`)
    REFERENCES `mydb`.`company` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_company_has_data_data1`
    FOREIGN KEY (`data_id`)
    REFERENCES `mydb`.`data` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
