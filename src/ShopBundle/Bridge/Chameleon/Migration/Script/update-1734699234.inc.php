<h1>Build #1734699234</h1>
<h2>Date: 2024-12-20</h2>
<div class="changelog">
    - ref #65248: remove MTPkgExternalTracker_MTShopArticleCatalogCore extension
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_cms_class_manager_extension', 'de')
  ->setWhereEquals([
      'class' => 'MTPkgExternalTracker_MTShopArticleCatalogCore',
  ])
;
TCMSLogChange::delete(__LINE__, $data);

TCMSLogChange::UpdateVirtualNonDbClasses();
