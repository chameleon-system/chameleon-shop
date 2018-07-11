<h1>update - Build #1492608540</h1>
<h2>Date: 2017-04-19</h2>
<div class="changelog">
</div>
<?php

  $data = TCMSLogChange::createMigrationQueryData('pkg_cms_routing', 'en')
      ->setFields(array(
          'name' => 'chameleon_system_shop.searchsuggest',
          'type' => 'yaml',
          'resource' => '@ChameleonSystemShopBundle/Resources/config/route_searchsuggest.yml',
      ))
      ->setWhereEquals(array(
          'name' => 'esono_customer.searchsuggest',
      ))
  ;
  TCMSLogChange::update(__LINE__, $data);

  $data = TCMSLogChange::createMigrationQueryData('pkg_cms_routing', 'en')
    ->setFields(array(
        'resource' => '@ChameleonSystemShopBundle/Resources/config/route.yml',
    ))
    ->setWhereEquals(array(
        'resource' => '@ChameleonSystemShopBundle/config/route.yml',
    ))
  ;
  TCMSLogChange::update(__LINE__, $data);
