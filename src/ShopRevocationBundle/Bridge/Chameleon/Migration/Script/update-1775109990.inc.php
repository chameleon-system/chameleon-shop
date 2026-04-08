<h1>Build #1775109990</h1>
<h2>Date: 2026-04-02</h2>
<div class="changelog">
    - #69674: Add new fields to order revocation table
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => 'new_field',
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
                'id' => '74c6a5ed-5bf9-9907-2efe-b715f4cf2cc6',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop_order_revocation`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
                'name' => 'shop_order_id', // prev.: 'new_field'
                'translation' => 'Bestellung', // prev.: ''
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_EXTENDEDTABLELIST'), // prev.: '34'
                'position' => '3255', // prev.: '0'
        ])
        ->setWhereEquals([
                'id' => '74c6a5ed-5bf9-9907-2efe-b715f4cf2cc6',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Order',
        ])
        ->setWhereEquals([
                'id' => '74c6a5ed-5bf9-9907-2efe-b715f4cf2cc6',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation`
                     CHANGE `new_field`
                            `shop_order_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Bestellung: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query = 'ALTER TABLE `shop_order_revocation` ADD INDEX `shop_order_id` (`shop_order_id`)';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => 'e382b149-924f-4bfe-f50f-60fa388fd8ba',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop_order_revocation`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'name', // prev.: 'new_field'
      'translation' => 'Name des Kunden', // prev.: ''
      'position' => '3251', // prev.: '0'
  ])
  ->setWhereEquals([
      'id' => 'e382b149-924f-4bfe-f50f-60fa388fd8ba',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Name of the customer',
        ])
        ->setWhereEquals([
                'id' => 'e382b149-924f-4bfe-f50f-60fa388fd8ba',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation`
                     CHANGE `new_field`
                            `name` VARCHAR(255) NOT NULL COMMENT 'Name des Kunden: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => '33ed29b7-510f-0a09-2507-35b3f176d526',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop_order_revocation`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'email', // prev.: 'new_field'
      'translation' => 'Email des Kunden', // prev.: ''
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_EMAIL'), // prev.: '34'
      'position' => '3252', // prev.: '0'
  ])
  ->setWhereEquals([
      'id' => '33ed29b7-510f-0a09-2507-35b3f176d526',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Email of the customer',
        ])
        ->setWhereEquals([
                'id' => '33ed29b7-510f-0a09-2507-35b3f176d526',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation`
                     CHANGE `new_field`
                            `email` VARCHAR(255) NOT NULL COMMENT 'Email des Kunden: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => '40bd5b34-2a6e-fe6e-5c7c-461079d09a65',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop_order_revocation`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'revocation_datetime', // prev.: 'new_field'
      'translation' => 'Zeitpunkt des Widerrufs', // prev.: ''
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_DATETIME'), // prev.: '34'
      'position' => '3253', // prev.: '0'
  ])
  ->setWhereEquals([
      'id' => '40bd5b34-2a6e-fe6e-5c7c-461079d09a65',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Time of revocation',
        ])
        ->setWhereEquals([
                'id' => '40bd5b34-2a6e-fe6e-5c7c-461079d09a65',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation`
                     CHANGE `new_field`
                            `revocation_datetime` DATETIME NOT NULL COMMENT 'Zeitpunkt des Widerrufs: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query = 'ALTER TABLE `shop_order_revocation` ADD INDEX `revocation_datetime` (`revocation_datetime`)';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => '32958296-644e-194a-c0ac-2e721f02618b',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop_order_revocation`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'customer_note', // prev.: 'new_field'
      'translation' => 'Kundennotiz', // prev.: ''
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_TEXT'), // prev.: '34'
      'position' => '3254', // prev.: '0'
  ])
  ->setWhereEquals([
      'id' => '32958296-644e-194a-c0ac-2e721f02618b',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Customer note',
        ])
        ->setWhereEquals([
                'id' => '32958296-644e-194a-c0ac-2e721f02618b',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation`
                     CHANGE `new_field`
                            `customer_note` LONGTEXT NOT NULL COMMENT 'Kundennotiz: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'de')
        ->setFields([
                'title' => 'Name des Kunden',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => '`shop_order_revocation`.`name`',
                'db_alias' => 'name',
                'position' => '1',
                'id' => 'f8785575-f784-df59-3fcd-b8b4535580cd',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'en')
        ->setFields([
                'title' => 'Name of the customer', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => 'f8785575-f784-df59-3fcd-b8b4535580cd',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'de')
        ->setFields([
                'title' => 'Email des Kunden',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => '`shop_order_revocation`.`email`',
                'db_alias' => 'email',
                'position' => '2',
                'id' => 'd46d9613-5aa4-b690-8170-a43a257cc1c3',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'en')
        ->setFields([
                'title' => 'Email des Kunden', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => 'd46d9613-5aa4-b690-8170-a43a257cc1c3',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'de')
        ->setFields([
                'title' => 'Zeitpunkt des Widerrufs',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => '`shop_order_revocation`.`revocation_datetime`',
                'db_alias' => 'revocation_datetime',
                'position' => '3',
                'id' => '62546bde-e710-6966-2713-7191e0921010',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'en')
        ->setFields([
                'title' => 'Zeitpunkt des Widerrufs', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => '62546bde-e710-6966-2713-7191e0921010',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'de')
        ->setFields([
                'title' => 'Kundennotiz',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => '`shop_order_revocation`.`customer_note`',
                'db_alias' => 'customer_note',
                'position' => '4',
                'id' => 'ae8816e0-13c6-9313-6ed0-e66c895723a3',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'en')
        ->setFields([
                'title' => 'Kundennotiz', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => 'ae8816e0-13c6-9313-6ed0-e66c895723a3',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'de')
        ->setFields([
                'title' => 'Bestellung',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'name' => '`shop_order_revocation`.`shop_order_id`',
                'db_alias' => 'shop_order_id',
                'position' => '5',
                'id' => 'f3d3cec8-c03b-2b02-7ffa-9ec24177cf1c',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_list_fields', 'en')
        ->setFields([
                'title' => 'Bestellung', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => 'f3d3cec8-c03b-2b02-7ffa-9ec24177cf1c',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_orderfields', 'de')
        ->setFields([
                'name' => '`shop_order_revocation`.`revocation_datetime`',
                'position' => '1',
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_revocation'),
                'id' => 'd934c988-1513-0691-f46f-aa87b87f1ceb',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_display_orderfields', 'de')
        ->setFields([
            // 'name' => '`shop_order_revocation`.`revocation_datetime`',
                'sort_order_direction' => 'DESC', // prev.: 'ASC'
        ])
        ->setWhereEquals([
                'id' => 'd934c988-1513-0691-f46f-aa87b87f1ceb',
        ])
;
TCMSLogChange::update(__LINE__, $data);
