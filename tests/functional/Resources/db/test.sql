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

CREATE TABLE `customers`(
    `customer_id` int(10) unsigned NOT NULL auto_increment,
    `email` varchar(255) DEFAULT NULL,
    `firstname` varchar(255) DEFAULT NULL,
    `lastname` varchar(255) DEFAULT NULL,
    `phone_number` varchar(255) DEFAULT NULL,
    `store_id` smallint(5) unsigned NOT NULL,
    PRIMARY KEY (`customer_id`),
    UNIQUE KEY (`email`),
    FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`)
);

INSERT INTO `customers` VALUES(1, 'user1@example.org', 'firstname1', 'lastname1', '0240102031', 1);
INSERT INTO `customers` VALUES(2, 'user2@example.org', 'firstname2', 'lastname2', '0240102032', 1);
INSERT INTO `customers` VALUES(3, 'user3@example.org', 'firstname3', 'lastname3', '0240102033', 2);
INSERT INTO `customers` VALUES(4, 'user4@example.org', 'firstname4', 'lastname4', '0240102034', 2);

CREATE TABLE `addresses`(
    `address_id` int(10) unsigned NOT NULL auto_increment,
    `street` varchar(255) DEFAULT NULL,
    `postcode` varchar(255) DEFAULT NULL,
    `city` varchar(255) DEFAULT NULL,
    `country_id` varchar(255) DEFAULT NULL,
    `customer_id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`address_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
);

INSERT INTO `addresses` VALUES(1, 'street1', '75011', 'Paris', 'FR', 1);
INSERT INTO `addresses` VALUES(2, 'street2', '44200', 'Nantes', 'FR', 1);
INSERT INTO `addresses` VALUES(3, 'street3', '35000', 'Rennes', 'FR', 2);
INSERT INTO `addresses` VALUES(4, 'street4', '1120', 'Rennes', 'FR', 2);
INSERT INTO `addresses` VALUES(5, 'street5', '33000', 'Bordeaux', 'FR', 3);
INSERT INTO `addresses` VALUES(6, 'street5', '34000', 'Montpellier', 'FR', 4);
