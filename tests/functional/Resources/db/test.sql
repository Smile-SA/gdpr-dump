SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `stores`;
DROP TABLE IF EXISTS `config`;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE `stores`(
    `store_id` smallint(5) unsigned NOT NULL auto_increment,
    `code` varchar(255) NOT NULL,
    `parent_store_id` smallint(5) unsigned DEFAULT NULL, -- cyclic dependency on same table
    PRIMARY KEY (`store_id`)
);

INSERT INTO `stores` VALUES(1, 'store1', 2);
INSERT INTO `stores` VALUES(2, 'store2', 3);
INSERT INTO `stores` VALUES(3, 'store3', null);

CREATE TABLE `customers`(
    `customer_id` int(10) unsigned NOT NULL auto_increment,
    `email` varchar(255) NOT NULL,
    `firstname` varchar(255) NOT NULL,
    `lastname` varchar(255) NOT NULL,
    `is_active` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL,
    `billing_address_id` int(10) unsigned NOT NULL, -- cyclic dependency between two tables
    `shipping_address_id` int(10) unsigned NOT NULL, -- cyclic dependency between two tables
    `store_id` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`customer_id`),
    UNIQUE KEY (`email`),
    FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`)
);

INSERT INTO `customers` VALUES(1, 'user1@test.org', 'firstname1', 'lastname1', 1, date_sub(now(), interval 60 day), 1, 2, 1);
INSERT INTO `customers` VALUES(2, 'user2@test.org', 'firstname2', 'lastname2', 1, date_sub(now(), interval 50 day), 3, 4, 1);
INSERT INTO `customers` VALUES(3, 'user3@test.org', 'firstname3', 'lastname3', 1, date_sub(now(), interval 40 day), 5, 6, 1);
INSERT INTO `customers` VALUES(4, 'user4@test.org', 'firstname4', 'lastname4', 1, date_sub(now(), interval 30 day), 7, 7, 2);
INSERT INTO `customers` VALUES(5, 'user5@test.org', 'firstname5', 'lastname5', 1, date_sub(now(), interval 20 day), 8, 9, 3);

CREATE TABLE `addresses`(
    `address_id` int(10) unsigned NOT NULL auto_increment,
    `street` varchar(255) NOT NULL,
    `postcode` varchar(255) NOT NULL,
    `city` varchar(255) NOT NULL,
    `country_id` varchar(2) NOT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`address_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
);

INSERT INTO `addresses` VALUES(1, 'street1', '75011', 'Paris', 'FR', 1);
INSERT INTO `addresses` VALUES(2, 'street2', '1000', 'Bruxelles', 'BE', 1);
INSERT INTO `addresses` VALUES(3, 'street3', '44000', 'Nantes', 'FR', 2);
INSERT INTO `addresses` VALUES(4, 'street4', '2000', 'Anvers', 'BE', 2);
INSERT INTO `addresses` VALUES(5, 'street5', '33000', 'Bordeaux', 'FR', 3);
INSERT INTO `addresses` VALUES(6, 'street6', '34000', 'Montpellier', 'FR', 3);
INSERT INTO `addresses` VALUES(7, 'street7', '69000', 'Lyon', 'FR', 4);
INSERT INTO `addresses` VALUES(8, 'street8', '31000', 'Toulouse', 'FR', 5);
INSERT INTO `addresses` VALUES(9, 'street9', '59000', 'Lille', 'FR', 5);

-- This table won't be included in the dump
CREATE TABLE `config`(
    `config_id` int(10) unsigned NOT NULL auto_increment,
    `path` varchar(255) NOT NULL,
    `value` text DEFAULT NULL,
    `store_id` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`config_id`),
    FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`)
);

INSERT INTO `config` VALUES(1, 'currency', 'EUR', 1);
INSERT INTO `config` VALUES(2, 'currency', 'USD', 2);
INSERT INTO `config` VALUES(3, 'currency', 'GBP', 3);

-- Create cyclic dependencies
ALTER TABLE `stores` ADD FOREIGN KEY (`parent_store_id`) REFERENCES `stores` (`store_id`);
ALTER TABLE `customers` ADD FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`address_id`);
ALTER TABLE `customers` ADD FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`address_id`);
