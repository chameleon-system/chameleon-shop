<h1>Build #1736347109</h1>
<h2>Date: 2025-01-08</h2>
<div class="changelog">
    - ref #65182: add system names for stats groups<br>
</div>
<?php

$query = "ALTER TABLE `pkg_shop_statistic_group` COMMENT 'Statistik Gruppe: Hier werden die Auswertungsgruppen für die Shop-Statistik definiert.'";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'de')
  ->setFields([
      // 'name' => 'pkg_shop_statistic_group',
      'translation' => 'Statistik Gruppen', // prev.: 'Umsatzgruppen'
  ])
  ->setWhereEquals([
      'id' => 'dc6da9ff-0faa-9f0f-2a41-f2b9ff89daec',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `pkg_shop_statistic_group` COMMENT 'Statistik Gruppen: Hier werden die Auswertungsgruppen für die Shop-Statistik definiert.'";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
  ->setFields([
      'name' => 'Statistikgruppen', // prev.: 'Umsatzgruppen'
  ])
  ->setWhereEquals([
      'id' => '136a48bf-d0fa-9060-3e00-7c30237398d7',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'DE')
  ->setFields([
      'target_table_name' => 'cms_tbl_conf',
  ])
  ->setWhereEquals([
      'id' => '136a48bf-d0fa-9060-3e00-7c30237398d7',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_cms_role3_mlt', 'de')
  ->setFields([
      'source_id' => 'dc6da9ff-0faa-9f0f-2a41-f2b9ff89daec',
      'target_id' => '1',
      'entry_sort' => '0',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('pkg_shop_statistic_group'),
      'name' => 'system_name', // prev.: 'new_field'
      'translation' => 'Systemname', // prev.: ''
      'position' => '2184', // prev.: '0'
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => 'c4202cf2-bfd5-22c3-8ba5-7439d81510cf',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = "ALTER TABLE `pkg_shop_statistic_group`
                        ADD `system_name` VARCHAR(255) NOT NULL COMMENT 'Systemname: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'translation' => 'Systemname', // prev.: ''
  ])
  ->setWhereEquals([
      'id' => 'c4202cf2-bfd5-22c3-8ba5-7439d81510cf',
  ])
;
TCMSLogChange::update(__LINE__, $data);

TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('pkg_shop_statistic_group'), 'system_name', 'name');

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Genutzte Bezahlmethoden',
      'system_name' => 'used_payments', // prev.: ''
      'position' => '4', // prev.: '0'
  ])
  ->setWhereEquals([
      'name' => 'Payment Method',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Umsatz ohne Versand',
      'system_name' => 'sales_without_shipping', // prev.: ''
  ])
  ->setWhereEquals([
      'name' => 'Revenue excluding shipping',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Hersteller (Verkäufe)',
      'system_name' => 'sales_by_manufacturer', // prev.: ''
  ])
  ->setWhereEquals([
      'name' => 'Manufacturer (Sales)',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Anzahl Bestellungen',
      'system_name' => 'sales_count', // prev.: ''
  ])
  ->setWhereEquals([
      'name' => 'Number of orders',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Ø Warenkorb Größe',
      'system_name' => 'average_basket_products', // prev.: ''
  ])
  ->setWhereEquals([
      'name' => 'Ø basket size',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
  ->setFields([
      // 'name' => 'Ø Warenkorb Wert ohne Versand',
      'system_name' => 'basket_size_without_shipping', // prev.: ''
  ])
  ->setWhereEquals([
      'name' => 'Ø basket value excl. shipping',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        // 'name' => 'Anzahl bestellter Artikel',
        'system_name' => 'number_of_ordered_items', // prev.: ''
    ])
    ->setWhereEquals([
        'name' => 'Number of ordererd items',
    ])
;
TCMSLogChange::update(__LINE__, $data);
