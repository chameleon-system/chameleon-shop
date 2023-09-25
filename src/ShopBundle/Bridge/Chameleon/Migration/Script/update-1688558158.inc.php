<h1>Build #1688558158</h1>
<h2>Date: 2023-07-05</h2>
<div class="changelog">
    - ref #822: enlarge class field in shop_order_step to prevent truncation of namespaces
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
  ->setFields([
//      'name' => 'class',
      'cms_field_type_id' => TCMSLogChange::GetFieldType('CMSFIELD_TEXT'),
  ])
  ->setWhereEquals([
      'name' => 'class',
      'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order_step'),
  ])
;
TCMSLogChange::update(__LINE__, $data);

$query ="ALTER TABLE `shop_order_step`
                     CHANGE `class`
                            `class` LONGTEXT NOT NULL";
TCMSLogChange::RunQuery(__LINE__, $query);
