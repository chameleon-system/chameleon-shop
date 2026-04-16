<h1>Build #1773409679</h1>
<h2>Date: 2026-03-13</h2>
<div class="changelog">
    - 69674: Add revocation module
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
  ->setFields([
      'name' => '',
      'id' => '63299932-0f92-e9ae-187f-61542858f339',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
  ->setFields([
      'name' => '',
      'id' => 'b0a3fe63-e886-79b4-5413-29535c81ba55',
  ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
  ->setFields([
      'name' => 'Elektronischer Widerruf ', // prev.: ''
      'description' => 'Dieses Modul erzeugt ein Formular, mit dem der User elektronisch Bestellungen widerrufen kann.', // prev.: ''
      'classname' => 'chameleon_system_shop_revocation.modules.shop_revocation_module', // prev.: ''
      'view_mapper_config' => 'standard=modules/revocationForm/standard.html.twig',
  ])
  ->setWhereEquals([
      'id' => 'b0a3fe63-e886-79b4-5413-29535c81ba55',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
        ->setFields([
                'name' => 'Electronic Revocation ', // prev.: ''
                'description' => 'This modul creates a form, with which the user can create an electronic revocation for an order.', // prev.: ''
        ])
        ->setWhereEquals([
                'id' => 'b0a3fe63-e886-79b4-5413-29535c81ba55',
        ])
;
TCMSLogChange::update(__LINE__, $data);