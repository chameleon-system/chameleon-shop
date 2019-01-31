<?php

$layoutTemplate = 'default';
$moduleList = array(
    'contentmodule' => array('model' => 'MTShopStatistic', 'view' => 'standard', 'moduleType' => '@ChameleonSystemShopBundle', '_suppressHistory' => true),
);

addDefaultPageTitle($moduleList);
addDefaultHeader($moduleList);
addDefaultBreadcrumb($moduleList);
addDefaultSidebar($moduleList);

