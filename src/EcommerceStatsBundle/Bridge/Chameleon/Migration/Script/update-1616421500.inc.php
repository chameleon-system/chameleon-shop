<h1>Build #1616421500</h1>
<h2>Date: 2020-09-25</h2>
<div class="changelog">
    - ref #636: Add routing definitions
</div>
<?php
$data = TCMSLogChange::createMigrationQueryData('pkg_cms_routing', 'de')
    ->setFields([
        'name' => 'Ecommerce Stats',
        'short_description' => 'Export Endpunkte für Statistiken (backend)',
        'type' => 'yaml',
        'resource' => '@ChameleonSystemEcommerceStatsBundle/Resources/config/routing.yml',
        'position' => '5',
        'system_page_name' => '',
        'active' => '1',
        'id' => '8ae4139d-9beb-41e5-9f0f-8cda96d4932d',
    ]);
TCMSLogChange::insert(__LINE__, $data);
