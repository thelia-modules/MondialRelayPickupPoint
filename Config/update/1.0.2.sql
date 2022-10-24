-- ---------------------------------------------------------------------
-- mondial_relay_pickup_point_freeshipping
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `mondial_relay_pickup_point_freeshipping`;

CREATE TABLE `mondial_relay_pickup_point_freeshipping`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `active` TINYINT(1) DEFAULT 0,
    `freeshipping_from` DECIMAL(18,2),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB;