<h1>Build #1775109713</h1>
<h2>Date: 2026-04-02</h2>
<div class="changelog">
    - #69674: Add table for shop order revocation
</div>
<?php

$query = "CREATE TABLE `shop_order_revocation` (
                  `id` CHAR( 36 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL ,
                  `cmsident` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Key is used so that records can be easily identified in Chameleon',
                  PRIMARY KEY ( `id` ),
                  UNIQUE (`cmsident`)
                ) ENGINE = InnoDB";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'de')
  ->setFields([
      'name' => 'shop_order_revocation',
      'id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role1_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role2_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role3_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role6_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role4_mlt', 'de')
  ->setFields([
      'source_id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'de')
  ->setFields([
      'name' => 'shop_order_revocation', // prev.: 'shop_order_revocation'
      'translation' => 'Bestellung Widerruf', // prev.: ''
      'cms_usergroup_id' => '6', // prev.: ''
  ])
  ->setWhereEquals([
      'id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'en')
        ->setFields([
                'translation' => 'Order Revocation', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop_order_revocation` COMMENT 'Bestellung Widerruf: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$menuCategoryId = (string) TCMSLogChange::getDatabaseConnection()
     ->executeQuery('SELECT id FROM cms_menu_category WHERE system_name="orders"')
     ->fetchColumn();

if ('' !== $menuCategoryId) {
    $data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
            ->setFields([
                    'name' => '',
                    'cms_menu_category_id' => $menuCategoryId,
                    'id' => 'a1cf4bc3-dfa2-7ce6-8c1d-0b8a9c4f196c',
            ])
    ;
    TCMSLogChange::insert(__LINE__, $data);

    $data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
            ->setFields([
                    'name' => 'Widerrufe', // prev.: ''
                    'target' => '8f5dd31d-9f38-aaf2-887a-e5590033b8e9', // prev.: ''
                    'icon_font_css_class' => ' fas  fa-reply', // prev.: ''
                    'position' => '29', // prev.: '0'
            ])
            ->setWhereEquals([
                    'id' => 'a1cf4bc3-dfa2-7ce6-8c1d-0b8a9c4f196c',
            ])
    ;
    TCMSLogChange::update(__LINE__, $data);

    $data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'en')
            ->setFields([
                    'name' => 'Revocation',
            ])
            ->setWhereEquals([
                    'id' => 'a1cf4bc3-dfa2-7ce6-8c1d-0b8a9c4f196c',
            ])
    ;
    TCMSLogChange::update(__LINE__, $data);

    $data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
            ->setFields([
                    'target_table_name' => 'cms_tbl_conf',
            ])
            ->setWhereEquals([
                    'id' => 'a1cf4bc3-dfa2-7ce6-8c1d-0b8a9c4f196c',
            ])
    ;
    TCMSLogChange::update(__LINE__, $data);
}
