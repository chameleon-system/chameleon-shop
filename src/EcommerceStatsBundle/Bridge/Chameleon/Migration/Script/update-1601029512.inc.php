<h1>Build #1601029512</h1>
<h2>Date: 2020-09-25</h2>
<div class="changelog">
    - ref #636: New backend module for statistics
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'de')
    ->setFields([
        'classname' => 'chameleon_system_ecommerce_stats.backend_module.ecommerce_stats',
        'name' => 'Shop Umsatzstatistiken Backendmodul',
        'description' => '',
        'icon_font_css_class' => 'fas fa-chart-pie',
        'view_mapper_config' => 'standard=ecommerceStats/module/standard.html.twig',
        'mapper_chain' => '',
        'view_mapping' => '',
        'revision_management_active' => '0',
        'is_copy_allowed' => '0',
        'show_in_template_engine' => '0',
        'is_restricted' => '1',
        'id' => '90eae2e0-796c-4b25-b3c8-62324df047d4',
    ]);
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module', 'en')
    ->setFields(['name' => 'Shop Sales Statistics Backendmodule']);
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_tpl_module_cms_usergroup_mlt', 'de')
  ->setFields([
      'source_id' => '90eae2e0-796c-4b25-b3c8-62324df047d4',
      'target_id' => '11',
      'entry_sort' => '0',
  ]);
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_menu_custom_item', 'de')
    ->setFields([
        'name' => 'Umsatzstatistiken',
        'url' => '/cms?pagedef=ecommerceStats&_pagedefType=@ChameleonSystemEcommerceStatsBundle',
        'id' => '9c06702f-7ffb-426d-afe9-5ffd5a9cd122',
    ]);
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_menu_custom_item', 'en')
    ->setFields(['name' => 'Sales statistics']);
TCMSLogChange::update(__LINE__, $data);

$menuCategoryId = (string) TCMSLogChange::getDatabaseConnection()
    ->executeQuery('SELECT id FROM cms_menu_category WHERE system_name="analytics"')
    ->fetchColumn();
$data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'de')
    ->setFields([
        'name' => 'Umsätze',
        'target' => '9c06702f-7ffb-426d-afe9-5ffd5a9cd122',
        'target_table_name' => 'cms_menu_custom_item',
        'icon_font_css_class' => 'fas fa-chart-pie',
        'position' => '22',
        'cms_menu_category_id' => $menuCategoryId,
        'id' => '552424ec-8131-4102-8406-3f56dad583e6',
    ]);
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_menu_item', 'en')
    ->setFields(['name' => 'Sales']);
TCMSLogChange::update(__LINE__, $data);
