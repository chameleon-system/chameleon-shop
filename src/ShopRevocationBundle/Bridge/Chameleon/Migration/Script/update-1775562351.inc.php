<h1>Build #1775562351</h1>
<h2>Date: 2026-04-07</h2>
<div class="changelog">
    - #69674: Add revocation field to shop order
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => 'f88d94fc-dd4a-1b2e-e743-4da6ba859cd0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query ="ALTER TABLE `shop_order`
                        ADD `new_field` VARCHAR(255) NOT NULL";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'shop_order_revocation_id', // prev.: 'new_field'
      'translation' => 'Widerruf', // prev.: ''
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_EXTENDEDTABLELIST'), // prev.: '34'
      'cms_tbl_field_tab' => '190ff1bc-ec90-4816-d119-a11addfda3c1', // prev.: ''
      'position' => '3256', // prev.: '0'
  ])
  ->setWhereEquals([
      'id' => 'f88d94fc-dd4a-1b2e-e743-4da6ba859cd0',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `shop_order`
                     CHANGE `new_field`
                            `shop_order_revocation_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Widerruf: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `shop_order` ADD INDEX `shop_order_revocation_id` (`shop_order_revocation_id`)";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `shop_order`
                     ADD INDEX `shop_order_revocation_lookup` (`shop_id`, `data_extranet_user_id`, `canceled`, `datecreated`)";
TCMSLogChange::RunQuery(__LINE__, $query);
