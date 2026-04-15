<h1>Build #1776239749</h1>
<h2>Date: 2026-04-15</h2>
<div class="changelog">
    - #68714: add external link field to shop primary navigation table
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('pkg_shop_primary_navi'),
      'name' => 'new_field',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
      'id' => '0d7dbdf2-378f-fa3b-2cb1-413a4f2421cb',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query ="ALTER TABLE `pkg_shop_primary_navi`
                        ADD `new_field` VARCHAR(255) NOT NULL";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
      'name' => 'external_url', // prev.: 'new_field'
      'translation' => 'Externe Url', // prev.: ''
      'position' => '3248', // prev.: '0'
          '049_helptext' => 'Geben Sie eine vollständige Internetadresse an, die mit https:// oder https:// beginnt. Verwenden Sie dieses Feld, wenn der Navigationspunkt auf eine externe Website weiterleiten soll.

Beispiel:

https://www.beispielseite.de',
  ])
  ->setWhereEquals([
      'id' => '0d7dbdf2-378f-fa3b-2cb1-413a4f2421cb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'External Url',
                '049_helptext' => 'Enter a full web address that begins with https://. Use this field when the navigation item should link to an external website.

  Example:

  https://www.example.com',
        ])
        ->setWhereEquals([
                'id' => '0d7dbdf2-378f-fa3b-2cb1-413a4f2421cb',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `pkg_shop_primary_navi`
                     CHANGE `new_field`
                            `external_url` VARCHAR(255) NOT NULL COMMENT 'Externe Url: '";
TCMSLogChange::RunQuery(__LINE__, $query);

TCMSLogChange::SetFieldPosition('pkg_shop_primary_navi', 'external_url', 'target_two');

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('pkg_shop_primary_navi'),
                'name' => 'new_field',
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_STRING'),
                'id' => '32b9f2dd-578c-b92b-f32a-c39f268bd7c2',
        ])
;
TCMSLogChange::insert(__LINE__, $data);

$query ="ALTER TABLE `pkg_shop_primary_navi`
                        ADD `new_field` VARCHAR(255) NOT NULL";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
        ->setFields([
                'name' => 'open_external_link_in_new_tab', // prev.: 'new_field'
                'translation' => 'Externen Link In Neuem Fenster Öffnen', // prev.: ''
                'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_BOOLEAN'), // prev.: '34'
                'position' => '3249', // prev.: '0'
                '049_helptext' => 'Wählen Sie **Ja**, wenn externe Links in einem neuen Fenster geöffnet werden sollen (_blank).',
        ])
        ->setWhereEquals([
                'id' => '32b9f2dd-578c-b92b-f32a-c39f268bd7c2',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
        ->setFields([
                'translation' => 'Open External Link In New Tab',
                '049_helptext' => 'Choose **Yes** if external links should open in a new window (_blank).',
        ])
        ->setWhereEquals([
                'id' => '32b9f2dd-578c-b92b-f32a-c39f268bd7c2',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `pkg_shop_primary_navi`
                     CHANGE `new_field`
                            `open_external_link_in_new_tab` ENUM('0','1') DEFAULT '0' NOT NULL COMMENT 'Externen Link In Neuem Fenster Öffnen: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `pkg_shop_primary_navi` ADD INDEX `open_external_link_in_new_tab` (`open_external_link_in_new_tab`)";
TCMSLogChange::RunQuery(__LINE__, $query);

TCMSLogChange::SetFieldPosition('pkg_shop_primary_navi', 'open_external_link_in_new_tab', 'external_url');

$query ="ALTER TABLE `pkg_shop_primary_navi`
                     CHANGE `external_url`
                            `external_url` VARCHAR(255) NOT NULL COMMENT 'Externe Url: Geben Sie eine vollständige Internetadresse an, die mit https:// oder https:// beginnt. Verwenden Sie dieses Feld, wenn der Navigationspunkt auf eine externe Website weiterleiten soll.\\r\\n\\r\\nBeispiel:\\r\\n\\r\\nhttps://www.beispielseite.de'";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `pkg_shop_primary_navi` DROP INDEX `open_external_link_in_new_tab`";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `pkg_shop_primary_navi`
                     CHANGE `open_external_link_in_new_tab`
                            `open_external_link_in_new_tab` ENUM('0','1') DEFAULT '0' NOT NULL COMMENT 'Externen Link In Neuem Fenster Öffnen: Wählen Sie **Ja**, wenn externe Links in einem neuen Fenster geöffnet werden sollen (_blank).'";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `pkg_shop_primary_navi` ADD INDEX `open_external_link_in_new_tab` (`open_external_link_in_new_tab`)";
TCMSLogChange::RunQuery(__LINE__, $query);