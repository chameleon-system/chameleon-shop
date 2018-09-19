<h1>Build #1530092686</h1>
<h2>Date: 2018-06-27</h2>
<div class="changelog">
    - Add English names for modules
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
  ->setFields([
      'name' => 'Shop - Produktliste',
  ])
  ->setWhereEquals([
      'classname' => 'chameleon_system_shop.module.article_list',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Product list',
  ])
  ->setWhereEquals([
      'classname' => 'chameleon_system_shop.module.article_list',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Product comments',
  ])
  ->setWhereEquals([
      'classname' => 'MTPkgShopArticleReview',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Primary navigation',
  ])
  ->setWhereEquals([
      'classname' => 'MTPkgShopPrimaryNavigation',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Wish list',
  ])
  ->setWhereEquals([
      'classname' => 'MTPkgShopWishlist',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'MTPkgShopWishlistPublic',
  ])
  ->setWhereEquals([
      'classname' => 'MTPkgShopWishlistPublic',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Rating list',
  ])
  ->setWhereEquals([
      'classname' => 'MTRatingList',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Rating widget',
  ])
  ->setWhereEquals([
      'classname' => 'MTRatingServiceWidget',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Rating teaser',
  ])
  ->setWhereEquals([
      'classname' => 'MTRatingTeaser',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Shop product catalog',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopArticleCatalog',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Central shop handler',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopCentralHandler',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'description' => '',
      'name' => 'Shop - Manufacturer/brand catalog',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopManufacturerArticleCatalog',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - My account',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopMyAccount',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Place order',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopOrderWizard',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Search form',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopSearchForm',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Search filters and forms',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopSearchForm',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Tag cloud',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopSearchTags',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
  ->setFields([
      'name' => 'Shop - Search tag cloud',
  ])
  ->setWhereEquals([
      'classname' => 'MTShopSearchTags',
  ])
;
TCMSLogChange::update(__LINE__, $data);
