SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `stores`;
DROP TABLE IF EXISTS `config`;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE `stores`(
    `store_id` smallint(5) unsigned NOT NULL auto_increment,
    `code` varchar(255) NOT NULL,
    PRIMARY KEY (`store_id`)
);

INSERT INTO `stores` VALUES(1, 'store1');
INSERT INTO `stores` VALUES(2, 'store2');
INSERT INTO `stores` VALUES(3, 'store3');

CREATE TABLE `customers`(
    `customer_id` int(10) unsigned NOT NULL auto_increment,
    `email` varchar(255) NOT NULL,
    `firstname` varchar(255) NOT NULL,
    `lastname` varchar(255) NOT NULL,
    `is_active` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL,
    `store_id` smallint(5) unsigned NOT NULL,
    `main_address_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`customer_id`),
    UNIQUE KEY (`email`),
    FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`)
);

INSERT INTO `customers` VALUES(1, 'user1@test.org', 'firstname1', 'lastname1', 1, date_sub(now(), interval 60 day), 1, 1);
INSERT INTO `customers` VALUES(2, 'user2@test.org', 'firstname2', 'lastname2', 1, date_sub(now(), interval 50 day), 1, 3);
INSERT INTO `customers` VALUES(3, 'user3@test.org', 'firstname3', 'lastname3', 1, date_sub(now(), interval 40 day), 1, 5);
INSERT INTO `customers` VALUES(4, 'user4@test.org', 'firstname4', 'lastname4', 1, date_sub(now(), interval 30 day), 2, 7);
INSERT INTO `customers` VALUES(5, 'user5@test.org', 'firstname5', 'lastname5', 1, date_sub(now(), interval 20 day), 3, 8);

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
INSERT INTO `addresses` VALUES(3, 'street1', '44000', 'Nantes', 'FR', 2);
INSERT INTO `addresses` VALUES(4, 'street2', '2000', 'Anvers', 'BE', 2);
INSERT INTO `addresses` VALUES(5, 'street1', '33000', 'Bordeaux', 'FR', 3);
INSERT INTO `addresses` VALUES(6, 'street1', '34000', 'Montpellier', 'FR', 3);
INSERT INTO `addresses` VALUES(7, 'street1', '69000', 'Lyon', 'FR', 4);
INSERT INTO `addresses` VALUES(8, 'street1', '31000', 'Toulouse', 'FR', 5);
INSERT INTO `addresses` VALUES(9, 'street1', '59000', 'Lille', 'FR', 5);

-- Create a cyclic dependency between customers and addresses
ALTER TABLE `customers` ADD FOREIGN KEY (`main_address_id`) REFERENCES `addresses` (`address_id`);

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
