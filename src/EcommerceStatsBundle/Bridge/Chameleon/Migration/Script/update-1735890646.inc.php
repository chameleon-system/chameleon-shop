<h1>Build #1735890646</h1>
<h2>Date: 2025-01-03</h2>
<div class="changelog">
    - ref #65366: update routing system name
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_cms_routing', 'de')
  ->setFields([
      'name' => 'chameleon_system_ecommerce_stats', // prev.: ...'Ecommerce Stats'..., now: ...'chameleon_system_ecommerce_stats'...
  ])
  ->setWhereEquals([
      'id' => '8ae4139d-9beb-41e5-9f0f-8cda96d4932d',
  ])
;
TCMSLogChange::update(__LINE__, $data);
