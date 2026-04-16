<h1>Build #1773648194</h1>
<h2>Date: 2026-03-16</h2>
<div class="changelog">
    - #69674: Add revocation_additional_days field to shop table
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => '9416a12c-2317-1f05-d591-f95e9a71778f',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = 'ALTER TABLE `shop`
                        ADD `new_field` VARCHAR(255) NOT NULL';
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'revocation_additional_days', // prev.: 'new_field'
      'translation' => 'Widerruf Tage (14 + x)', // prev.: ''
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_NUMBER'), // prev.: '34'
      'cms_tbl_field_tab' => '9d27523d-6e19-40bb-6f58-2408908a5c25', // prev.: ''
      'field_default_value' => '3', // prev.: ''
      'position' => '3249', // prev.: '0'
      '049_helptext' => 'Anzahl Tage, die addiert werden, wenn kein Lieferdatum verfügbar ist', // prev.: ''
  ])
  ->setWhereEquals([
      'id' => '9416a12c-2317-1f05-d591-f95e9a71778f',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'name' => 'revocation_additional_days', // prev.: 'new_field'
                'translation' => 'Revocation days (14 + x)', // prev.: ''
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_NUMBER'), // prev.: '34'
                'cms_tbl_field_tab' => '9d27523d-6e19-40bb-6f58-2408908a5c25', // prev.: ''
                'field_default_value' => '3', // prev.: ''
                'position' => '3249', // prev.: '0'
                '049_helptext' => 'Amount of days that get added to the revocation day limit.', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => '9416a12c-2317-1f05-d591-f95e9a71778f',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query = "ALTER TABLE `shop`
                     CHANGE `new_field`
                            `revocation_additional_days` INT(11) DEFAULT '3' NOT NULL COMMENT 'Widerruf Tage (14 + x): Anzahl Tage, die addiert werden, wenn kein Lieferdatum verfügbar ist'";
TCMSLogChange::RunQuery(__LINE__, $query);
