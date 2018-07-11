<h1>Build #1520413512</h1>
<h2>Date: 2018-03-07</h2>
<div class="changelog">
    - Set module location for shop CMS modules
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_module', 'de')
  ->setFields([
      'module_location' => '@ChameleonSystemShopBundle',
  ])
  ->setWhereEquals([
      'module_location' => 'pkgshop',
      'uniquecmsname' => 'shopstats',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_module', 'de')
  ->setFields([
      'module_location' => '@ChameleonSystemShopBundle',
  ])
  ->setWhereEquals([
      'module_location' => 'pkgshop',
      'uniquecmsname' => 'articlesearchindex',
  ])
;
TCMSLogChange::update(__LINE__, $data);
