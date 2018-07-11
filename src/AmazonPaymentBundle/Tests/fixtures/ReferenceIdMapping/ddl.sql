DROP TABLE IF EXISTS `amazon_payment_id_mapping`;
CREATE TABLE `amazon_payment_id_mapping` (
  `id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `cmsident` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Key is used so that records can be easily identified in Chameleon',
  `local_id` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `shop_order_id` char(36) CHARACTER SET latin1 COLLATE latin1_general_ci  NOT NULL,
  `amazon_order_reference_id` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci   NOT NULL,
  `amazon_id` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci   NOT NULL,
  `value` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `type` SMALLINT NOT NULL DEFAULT 0,
  `request_mode` SMALLINT NOT NULL DEFAULT 1,
  `capture_now` enum('0','1') NOT NULL DEFAULT '0',
  `pkg_shop_payment_transaction_id`char(36) CHARACTER SET latin1 COLLATE latin1_general_ci   NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE `localid` (`local_id`, `type`),
  UNIQUE KEY `cmsident` (`cmsident`)
) ENGINE=InnoDb DEFAULT CHARSET=utf8;