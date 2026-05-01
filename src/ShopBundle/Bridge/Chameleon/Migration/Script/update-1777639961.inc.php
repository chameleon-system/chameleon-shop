<h1>Build #1777639961</h1>
<h2>Date: 2026-05-01</h2>
<div class="changelog">
    - ref #70143: add query cache
</div>
<?php

TCMSLogChange::requireBundleUpdates('ChameleonSystemCmsCacheBundle', 1777558170);


$tableList = [
        'shop',
        'shop_cms_portal_mlt',
        'pkg_shop_currency',
        'shop_attribute',
        'shop_category',
        'shop_article_image_size',
        'shop_stock_message_trigger',
        'shop_module_article_list',
        'shop_module_article_list_filter',
        'shop_module_articlelist_orderby',
        'pkg_shop_listfilter_item',
        'shop_module_article_list_shop_article_group_mlt',
];

foreach ($tableList as $tableName) {
    $data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf', 'de')
            ->setFields([
                    'enable_query_cache' => '1', // prev.: 'new_field'
            ])->setWhereEquals(['name'=>$tableName]);
    ;
    TCMSLogChange::update(__LINE__, $data);
}