<?php

declare(strict_types=1);

$layoutTemplate = 'default';
$moduleList = [
    'contentmodule' => [
        'model' => 'chameleon_system_ecommerce_stats.backend_module.ecommerce_stats',
        'view' => 'standard',
        'moduleType' => '@ChameleonSystemEcommerceStatsBundle',
        '_suppressHistory' => true,
    ],
];

addDefaultPageTitle($moduleList);
addDefaultHeader($moduleList);
addDefaultBreadcrumb($moduleList);
addDefaultSidebar($moduleList);
