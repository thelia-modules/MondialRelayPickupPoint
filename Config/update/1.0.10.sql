ALTER TABLE `mondial_relay_pickup_point_price`
    CHANGE `price_with_tax` `price_without_tax` decimal(16,6) NOT NULL DEFAULT '0.000000' AFTER `max_weight`;

ALTER TABLE `mondial_relay_pickup_point_insurance`
    CHANGE `price_with_tax` `price_without_tax` decimal(16,6) NOT NULL DEFAULT '0.000000' AFTER `max_value`;
