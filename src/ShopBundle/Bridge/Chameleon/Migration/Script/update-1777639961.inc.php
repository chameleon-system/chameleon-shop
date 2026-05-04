<h1>Build #1777639961</h1>
<h2>Date: 2026-05-01</h2>
<div class="changelog">
    - ref #70143: add query cache
</div>
<?php

TCMSLogChange::requireBundleUpdates('ChameleonSystemCmsCacheBundle', 1777558170);

$tableList = [
    'pkg_image_hotspot',
    'pkg_image_hotspot_item',
    'pkg_shop_article_review_module_shop_article_review_configuration',
    'pkg_shop_credit_check_rule',
    'pkg_shop_currency',
    'pkg_shop_listfilter_item',
    'shop',
    'shop_article_image_size',
    'shop_article_shop_stock_message_mlt',
    'shop_attribute',
    'shop_category',
    'shop_cms_portal_mlt',
    'shop_manufacturer',
    'shop_module_article_list',
    'shop_module_article_list_filter',
    'shop_module_article_list_shop_article_group_mlt',
    'shop_module_articlelist_orderby',
    'shop_multi_warehouse',
    'shop_order_step',
    'shop_payment_handler',
    'shop_payment_handler_group',
    'shop_payment_handler_group_config',
    'shop_payment_handler_parameter',
    'shop_payment_method',
    'shop_shipping_group',
    'shop_shipping_type',
    'shop_stock_message',
    'shop_stock_message_trigger',
    'shop_vat',
];

foreach ($tableList as $tableName) {
    $data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'de')
        ->setFields([
            'enable_query_cache' => '1', // prev.: 'new_field'
        ])->setWhereEquals(['name' => $tableName]);

    TCMSLogChange::update(__LINE__, $data);
}
