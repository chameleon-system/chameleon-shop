<h1>Build #1750164616</h1>
<h2>Date: 2025-06-09</h2>
<div class="changelog">
    - ref #66891 fix missing system names for stats groups
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Anzahl Bestellungen',
      'system_name' => 'sales_count',
  ])
  ->setWhereEquals([
      'id' => '6e4af164-8792-9e01-573b-4bb36fd68cdc',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Ø Warenkorb Wert ohne Versand',
      'system_name' => 'basket_size_without_shipping',
  ])
  ->setWhereEquals([
      'id' => 'd577de75-6494-9075-15b7-7cb887d7b065',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Umsatz ohne Versand',
      'system_name' => 'sales_without_shipping',
  ])
  ->setWhereEquals([
      'id' => '74292e9c-2b9a-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Genutzte Bezahlmethoden',
      'system_name' => 'used_payments',
  ])
  ->setWhereEquals([
      'id' => '86d68501-3159-c5dd-95aa-850c63e95caf',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Anzahl bestellter Artikel',
      'system_name' => 'number_of_ordered_items',
  ])
  ->setWhereEquals([
      'id' => '07f209fa-2b9b-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Hersteller (Verkäufe)',
      'system_name' => 'sales_by_manufacturer',
  ])
  ->setWhereEquals([
      'id' => '5960bc8c-2b9b-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Ø Warenkorb Größe',
      'system_name' => 'average_basket_products',
  ])
  ->setWhereEquals([
      'id' => 'c3bd7230-effb-3083-50a5-f495c34f7283',
  ])
;
TCMSLogChange::update(__LINE__, $data);

