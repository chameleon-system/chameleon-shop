<h1>Build #1600037440</h1>
<h2>Date: 2024-12-17</h2>
<div class="changelog">
    -
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_module', 'en')
  ->setWhereEquals([
      'uniquecmsname' => 'shopstats',
  ])
;
TCMSLogChange::delete(__LINE__, $data);
