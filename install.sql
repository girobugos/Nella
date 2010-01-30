SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Table `modules`
-- -----------------------------------------------------
CREATE  TABLE `modules` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `namespacename` VARCHAR(100) NOT NULL ,
  `version` VARCHAR(20) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `modules`
-- -----------------------------------------------------
INSERT INTO `modules` (`name`, `namespacename`, `version`) VALUES
('Pages', 'Page', '0.1');


-- -----------------------------------------------------
-- Table `options`
-- -----------------------------------------------------
CREATE  TABLE `options` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `key` VARCHAR(50) NOT NULL ,
  `value` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `key_UNIQUE` (`key` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `options`
-- -----------------------------------------------------
INSERT INTO `options` (`key`, `value`) VALUES
('webname', 'Nella DEMO'),
('registrations', '1'),
('mailverifyregistration', '1'),
('termsregistration', 'asdf'),
('mail', 'no-reply@nellacms.com'),
('editor', 'CKEditor'),
('gacode', '');


-- -----------------------------------------------------
-- Table `pages`
-- -----------------------------------------------------
CREATE  TABLE `pages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(128) NOT NULL ,
  `lang` VARCHAR(5) NOT NULL DEFAULT 'en' ,
  `slug` VARCHAR(128) NOT NULL ,
  `text` TEXT NULL DEFAULT NULL ,
  `keywords` TEXT NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `revision` INT(11) NOT NULL DEFAULT '1' ,
  `status` TINYINT(4) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `slug_UNIQUE` (`slug` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 23
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `pages`
-- -----------------------------------------------------
INSERT INTO `pages` (`id`, `name`, `lang`, `slug`, `text`, `keywords`, `description`, `revision`, `status`) VALUES
(1, 'It works!', 'en', 'homepage', '<h2><b>Congratulations on your first Nette Framework powered page.</b></h2>We hope you enjoy this system!', '', '', 1, 1);


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE  TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(50) NOT NULL ,
  `password` VARCHAR(40) NOT NULL ,
  `mail` VARCHAR(150) NOT NULL ,
  `status` TINYINT(4) NULL DEFAULT '0' ,
  `role` VARCHAR(50) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  UNIQUE INDEX `mail_UNIQUE` (`mail` ASC) )
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `users`
-- -----------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password`, `mail`, `status`, `role`) VALUES
(1, 'admin', '139649fc204a736c11649ce0089fe58f1df871e2', 'admin@nellacms.com', 1, NULL);


-- -----------------------------------------------------
-- Table `images`
-- -----------------------------------------------------
CREATE  TABLE `images` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `suffix` VARCHAR(5) NOT NULL ,
  `datetime` DATETIME NOT NULL ,
  `user_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_images_users1` (`user_id` ASC) ,
  CONSTRAINT `fk_images_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `pagesarchive`
-- -----------------------------------------------------
CREATE  TABLE `pagesarchive` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `page_id` INT(11) NOT NULL ,
  `name` VARCHAR(128) NOT NULL ,
  `slug` VARCHAR(128) NOT NULL ,
  `text` TEXT NULL DEFAULT NULL ,
  `keywords` TEXT NULL DEFAULT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `revision` INT(11) NULL DEFAULT '1' ,
  `datetime` DATETIME NOT NULL ,
  `user_id` INT(11) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_pagesarchive_pages1` (`page_id` ASC) ,
  INDEX `fk_pagesarchive_users1` (`user_id` ASC) ,
  CONSTRAINT `fk_pagesarchive_pages1`
    FOREIGN KEY (`page_id` )
    REFERENCES `nella`.`pages` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_pagesarchive_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `nella`.`users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `pagesarchive`
-- -----------------------------------------------------
INSERT INTO `pagesarchive` (`id`, `page_id`, `name`, `slug`, `text`, `keywords`, `description`, `revision`, `datetime`, `user_id`) VALUES
(1, 1, 'It works!', 'homepage', '<h2><b>Congratulations on your first Nette Framework powered page.</b></h2>We hope you enjoy this system!', '', '', 1, '2010-01-01 00:00:00', 1);


-- -----------------------------------------------------
-- Table `userprivileges`
-- -----------------------------------------------------
CREATE  TABLE `userprivileges` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `resource` VARCHAR(50) NOT NULL ,
  `privilege` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_userprivileges_users` (`user_id` ASC) ,
  CONSTRAINT `fk_userprivileges_users`
    FOREIGN KEY (`user_id` )
    REFERENCES `nella`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table data `userprivileges`
-- -----------------------------------------------------
INSERT INTO `userprivileges` (`user_id`, `resource`, `privilege`) VALUES
(1, 'Dashboard', 'default'),
(1, 'Auth', 'superadmin'),
(1, 'Auth', 'privilege'),
(1, 'Auth', 'add'),
(1, 'Auth', 'list'),
(1, 'Auth', 'status'),
(1, 'Settings', 'options'),
(1, 'Settings', 'modules'),
(1, 'Page', 'create'),
(1, 'Page', 'delete'),
(1, 'Page', 'publicate'),
(1, 'Page', 'edit'),
(1, 'Page', 'list'),
(1, 'Media', 'images');


-- -----------------------------------------------------
-- Table `usertokens`
-- -----------------------------------------------------
CREATE  TABLE `usertokens` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `key` VARCHAR(40) NOT NULL ,
  `created` DATETIME NOT NULL ,
  `type` TINYINT(4) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_usertokens_users1` (`user_id` ASC) ,
  CONSTRAINT `fk_usertokens_users1`
    FOREIGN KEY (`user_id` )
    REFERENCES `nella`.`users` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = utf8;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;