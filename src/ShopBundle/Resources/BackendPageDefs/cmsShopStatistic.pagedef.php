<?php

// main layout
$layoutTemplate = 'popup_window_iframe';

// modules...
$moduleList = array(
    'pagetitle' => array('model' => 'MTHeader', 'view' => 'title'),
    'contentmodule' => array('model' => 'MTShopStatistic', 'view' => 'standard', 'moduleType' => '@ChameleonSystemShopBundle', '_suppressHistory' => true),
);

// this line needs to be included... do not touch
if (!is_array($moduleList)) {
    $layoutTemplate = '';
}
