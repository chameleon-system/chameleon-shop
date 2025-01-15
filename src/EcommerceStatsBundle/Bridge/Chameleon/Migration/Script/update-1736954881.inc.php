<h1>Build #1736954881</h1>
<h2>Date: 2025-01-15</h2>
<div class="changelog">
    -
</div>
<?php

// add right "ecommerce_stats_show_module" to admin user
$data = TCMSLogChange::createMigrationQueryData('cms_role_cms_right_mlt', 'de')
  ->setFields([
      'source_id' => '1',
      'target_id' => '7dca3b88-ab6f-7a22-ef56-7b628cd98145',
      'entry_sort' => '16',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_role_cms_right_mlt', 'de')
  ->setFields([
      'source_id' => '1',
      'target_id' => '10',
      'entry_sort' => '17',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

TCMSLogChange::SetFieldPosition(TCMSLogChange::GetTableId('cms_role'), '049_trans', 'id');

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      // 'name' => '049_trans',
      'translation' => 'Titel', // prev.: 'German translation'
  ])
  ->setWhereEquals([
      'id' => '360',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `cms_role`
                     CHANGE `049_trans`
                            `049_trans` VARCHAR(40) NOT NULL COMMENT 'Titel: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` CHANGE `049_trans__de` 049_trans__de  varchar(40) NOT NULL COMMENT 'translation German'";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` DROP INDEX `049_trans__de` ";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` ADD INDEX ( `049_trans__de` )";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      // 'name' => '049_trans',
      'translation' => 'Name', // prev.: 'German translation'
  ])
  ->setWhereEquals([
      'id' => '360',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `cms_role`
                     CHANGE `049_trans`
                            `049_trans` VARCHAR(40) NOT NULL COMMENT 'Name: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` CHANGE `049_trans__de` 049_trans__de  varchar(40) NOT NULL COMMENT 'translation German'";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` DROP INDEX `049_trans__de` ";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `cms_role` ADD INDEX ( `049_trans__de` )";
TCMSLogChange::RunQuery(__LINE__, $query);

