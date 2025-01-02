<h1>Build #1735809903</h1>
<h2>Date: 2025-01-02</h2>
<div class="changelog">
    - #65182: change order of statistic fields, place name first
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '1',
  ])
  ->setWhereEquals([
      'id' => 'f08dcadb-ab89-e200-2fe4-c5f9fc87efd7',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '2',
  ])
  ->setWhereEquals([
      'id' => '32572de5-c269-14b6-65b1-b6758feb64c3',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '3',
  ])
  ->setWhereEquals([
      'id' => '33347d95-77f0-838a-ad0b-bcb6e17ced5c',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '4',
  ])
  ->setWhereEquals([
      'id' => 'e64db517-7cd9-a4e2-3049-f0b20b4bf5f2',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '5',
  ])
  ->setWhereEquals([
      'id' => '844df0d2-fce2-81aa-16ee-270f148c1261',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '6',
  ])
  ->setWhereEquals([
      'id' => '4ca1c7a6-8280-f3f6-c1e8-b7282590bcc3',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'position' => '7',
  ])
  ->setWhereEquals([
      'id' => '3d15e718-9b3e-cfe1-a195-507a646e7abd',
  ])
;
TCMSLogChange::update(__LINE__, $data);

