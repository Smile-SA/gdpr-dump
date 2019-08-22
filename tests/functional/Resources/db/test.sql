DROP TABLE IF EXISTS `addresses`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `stores`;

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
    `email` varchar(255) DEFAULT NULL,
    `firstname` varchar(255) DEFAULT NULL,
    `lastname` varchar(255) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL,
    `store_id` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`customer_id`),
    UNIQUE KEY (`email`),
    FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`)
);

INSERT INTO `customers` VALUES(1, 'user1@example.org', 'firstname1', 'lastname1', 1, date_sub(now(), interval 90 day), 1);
INSERT INTO `customers` VALUES(2, 'user2@example.org', 'firstname2', 'lastname2', 1, date_sub(now(), interval 35 day), 1);
INSERT INTO `customers` VALUES(3, 'user3@example.org', 'firstname3', 'lastname3', 0, date_sub(now(), interval 30 day), 1);
INSERT INTO `customers` VALUES(4, 'user4@example.org', 'firstname4', 'lastname4', 1, date_sub(now(), interval 25 day), 2);
INSERT INTO `customers` VALUES(5, 'user5@example.org', 'firstname5', 'lastname5', 1, date_sub(now(), interval 20 day), 2);
INSERT INTO `customers` VALUES(6, 'user6@example.org', 'firstname6', 'lastname6', 0, date_sub(now(), interval 15 day), 2);
INSERT INTO `customers` VALUES(7, 'user7@example.org', 'firstname7', 'lastname7', 1, date_sub(now(), interval 10 day), 3);

CREATE TABLE `addresses`(
    `address_id` int(10) unsigned NOT NULL auto_increment,
    `street` varchar(255) DEFAULT NULL,
    `postcode` varchar(255) DEFAULT NULL,
    `city` varchar(255) DEFAULT NULL,
    `country_id` varchar(2) DEFAULT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`address_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
);

INSERT INTO `addresses` VALUES(1, 'street1', '75011', 'Paris', 'FR', 1);
INSERT INTO `addresses` VALUES(2, 'street2', '1000', 'Bruxelles', 'BE', 1);
INSERT INTO `addresses` VALUES(3, 'street1', '44000', 'Nantes', 'FR', 2);
INSERT INTO `addresses` VALUES(4, 'street2', '2000', 'Anvers', 'BE', 2);
INSERT INTO `addresses` VALUES(5, 'street1', '33000', 'Bordeaux', 'FR', 3);
INSERT INTO `addresses` VALUES(6, 'street1', '34000', 'Montpellier', 'FR', 4);
INSERT INTO `addresses` VALUES(7, 'street1', '69000', 'Lyon', 'FR', 5);
INSERT INTO `addresses` VALUES(8, 'street1', '31000', 'Toulouse', 'FR', 6);
INSERT INTO `addresses` VALUES(9, 'street1', '59000', 'Lille', 'FR', 7);
