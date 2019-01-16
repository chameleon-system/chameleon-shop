<h1>Build #1545232400</h1>
<h2>Date: 2018-12-19</h2>
<div class="changelog">
    - -#234 add currency field to used voucher
</div>
<?php

$fieldId = TCMSLogChange::createUnusedRecordId('cms_field_conf');

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
  ->setFields([
      'name' => 'value_used_in_order_currency',
      'translation' => 'Value consumed in the order currency',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_DECIMAL'),
      'cms_tbl_field_tab' => '',
      'isrequired' => '0',
      'fieldclass' => '',
      'modifier' => 'none',
      'field_default_value' => '',
      'length_set' => '10,2',
      'fieldtype_config' => '',
      'restrict_to_groups' => '0',
      'field_width' => '0',
      'position' => '2172',
      '049_helptext' => 'Displays the consumption value of the voucher in the currency in which the order was made at the time of ordering.',
      'row_hexcolor' => '',
      'is_translatable' => '0',
      'validation_regex' => '',
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_voucher_use'),
      'fieldclass_subtype' => '',
      'class_type' => 'Core',
      'id' => $fieldId,
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = "ALTER TABLE `shop_voucher_use`
                        ADD `value_used_in_order_currency` DECIMAL(10,2) NOT NULL COMMENT 'Value consumed in the order currency: Displays the consumption value of the voucher in the currency in which the order was made at the time of ordering.'";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
    ->setFields([
        'translation' => 'Wert verbraucht in Währung der Bestellung',
        '049_helptext' => 'Zeigt den Verbrauchswert des Gutscheins (zum Zeitpunkt der Bestellung) in der Währung an, in der die Bestellung getätigt wurde.',
    ])
    ->setWhereEquals([
        'id' => $fieldId,
    ])
;
TCMSLogChange::update(__LINE__, $data);

$fieldId = TCMSLogChange::createUnusedRecordId('cms_field_conf');

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
  ->setFields([
      'name' => 'pkg_shop_currency_id',
      'translation' => 'Currency in which the order was made',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_EXTENDEDTABLELIST'),
      'cms_tbl_field_tab' => '',
      'isrequired' => '0',
      'fieldclass' => '',
      'modifier' => 'none',
      'field_default_value' => '',
      'length_set' => '',
      'fieldtype_config' => '',
      'restrict_to_groups' => '0',
      'field_width' => '0',
      'position' => '2173',
      '049_helptext' => '',
      'row_hexcolor' => '',
      'is_translatable' => '0',
      'validation_regex' => '',
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_voucher_use'),
      'fieldclass_subtype' => '',
      'class_type' => 'Core',
      'id' => $fieldId,
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$query = "ALTER TABLE `shop_voucher_use`
                        ADD `pkg_shop_currency_id` CHAR(36) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Currency in which the order was made: '";
TCMSLogChange::RunQuery(__LINE__, $query);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
    ->setFields([
        'translation' => 'Währung, in der die Bestellung getätigt wurde',
    ])
    ->setWhereEquals([
        'id' => $fieldId,
    ])
;
TCMSLogChange::update(__LINE__, $data);

$query = 'ALTER TABLE `shop_voucher_use` ADD INDEX ( `pkg_shop_currency_id` )';
TCMSLogChange::RunQuery(__LINE__, $query);
