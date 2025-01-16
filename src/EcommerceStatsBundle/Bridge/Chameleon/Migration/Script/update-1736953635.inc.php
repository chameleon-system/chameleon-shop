<h1>Build #1736953635</h1>
<h2>Date: 2025-01-15</h2>
<div class="changelog">
    - ref #65487: add new right "ecommerce_stats_show_module" for the ecommerce stats module
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_right', 'en')
  ->setFields([
      'name' => '',
      'id' => '7dca3b88-ab6f-7a22-ef56-7b628cd98145',
      '049_trans' => 'Show e-commerce sales statistics', // prev.: ''
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_right', 'de')
  ->setFields([
      'name' => 'ecommerce_stats_show_module', // prev.: ''
      '049_trans' => 'E-Commerce Umsatzstatistiken anzeigen', // prev.: ''
  ])
  ->setWhereEquals([
      'id' => '7dca3b88-ab6f-7a22-ef56-7b628cd98145',
  ])
;
TCMSLogChange::update(__LINE__, $data);

