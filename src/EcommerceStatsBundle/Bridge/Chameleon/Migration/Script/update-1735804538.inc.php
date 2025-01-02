<h1>Build #1735804538</h1>
<h2>Date: 2025-01-02</h2>
<div class="changelog">
    - #65182: add hasCurrency field to statistic groups, set hasCurrency to true for some groups
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
  ->setFields([
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('pkg_shop_statistic_group'),
      'name' => 'hasCurrency',
      'translation' => 'Needs Currency',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_BOOLEAN'),
      'cms_tbl_field_tab' => '',
      'isrequired' => '0',
      'fieldclass' => '',
      'fieldclass_subtype' => '',
      'class_type' => 'Core',
      'modifier' => 'none',
      'field_default_value' => '',
      'length_set' => '',
      'fieldtype_config' => '',
      'restrict_to_groups' => '0',
      'field_width' => '',
      'position' => '2179',
      '049_helptext' => 'Indicates whether this group requires a currency (usually the case if the group contains payment statistics)',
      'row_hexcolor' => '',
      'is_translatable' => '0',
      'validation_regex' => '',
      'id' => '3d15e718-9b3e-cfe1-a195-507a646e7abd',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
    ->setFields([
        'translation' => 'Benötigt Währung',
        '049_helptext' => 'Gibt, an ob diese Gruppe eine Währung benötigt (in der Regel dann der Fall, wenn es sich bei der Gruppe um Zahlungsstatistiken handelt)',
    ])
    ->setWhereEquals([
        'id' => '3d15e718-9b3e-cfe1-a195-507a646e7abd',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `pkg_shop_statistic_group`
                        ADD `hasCurrency` ENUM('0','1') DEFAULT '0' NOT NULL COMMENT 'Benötigt Währung: Gibt, an ob diese Gruppe eine Währung benötigt (in der Regel dann der Fall, wenn es sich bei der Gruppe um Zahlungsstatistiken handelt)'";
TCMSLogChange::RunQuery(__LINE__, $query);

$query ="ALTER TABLE `pkg_shop_statistic_group` ADD INDEX `hasCurrency` (`hasCurrency`)";
TCMSLogChange::RunQuery(__LINE__, $query);

$statisticGroupsThatNeedCurrencies = [
        '74292e9c-2b9a-11df-9c53-00fcefbad5fb',
        '5960bc8c-2b9b-11df-9c53-00fcefbad5fb',
        'd577de75-6494-9075-15b7-7cb887d7b065'
];

$dbConnection = TCMSLogChange::getDatabaseConnection();

$query = "UPDATE `pkg_shop_statistic_group` 
            SET `hasCurrency` = '1'
            WHERE `id` = :id";

foreach ($statisticGroupsThatNeedCurrencies as $statisticGroup) {
    $dbConnection->executeQuery($query, ['id' => $statisticGroup]);
}