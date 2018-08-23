<h1>update - Build #1521183372</h1>
<h2>Date: 2018-03-16</h2>
<div class="changelog">
    #41274 - improve database indexes
</div>
<?php

// index exists in some of projects as an entry in cms_tbl_conf_index but not in the table itself.
$query = "SELECT EXISTS (SELECT 1 FROM `cms_tbl_conf_index`
          WHERE `cms_tbl_conf_id` = :shopOrderTableId
            AND `name` = 'order_ident'
          )";
$indexEntryExists = TCMSLogChange::getDatabaseConnection()->fetchColumn(
    $query,
    ['shopOrderTableId' => TCMSLogChange::GetTableId('shop_order')]
);
if (1 === (int) $indexEntryExists) {
    TCMSLogChange::delete(
        __LINE__,
        TCMSLogChange::createMigrationQueryData('cms_tbl_conf_index', 'en')
            ->setWhereEquals(['cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order'), 'name' => 'order_ident'])
    );
}

$query = "show index from shop_order where Key_name = 'order_ident'";
$row = TCMSLogChange::getDatabaseConnection()->fetchAssoc($query);
if (false !== $row) {
    $query = 'ALTER TABLE `shop_order` DROP INDEX  `order_ident`';
    TCMSLogChange::RunQuery(__LINE__, $query);
}

$orderIdentIndexId = TCMSLogChange::createUnusedRecordId('cms_tbl_conf_index');
$data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_index', 'de')
    ->setFields(
        array(
            'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_order'),
            'name' => 'order_ident',
            'definition' => 'order_ident',
            'type' => 'INDEX',
            'id' => $orderIdentIndexId,
        )
    );
TCMSLogChange::insert(__LINE__, $data);
$query = 'ALTER TABLE `shop_order`
                        ADD INDEX  `order_ident` ( order_ident )';
TCMSLogChange::RunQuery(__LINE__, $query);

// --------------------------------------------------------
$orderIndicies = [
    'datecreated',
    'shop_id',
    'adr_billing_salutation_id',
    'adr_billing_country_id',
    'adr_shipping_use_billing',
    'adr_shipping_salutation_id',
    'adr_shipping_country_id',
    'shop_shipping_group_id',
    'shop_payment_method_id',
    'pkg_shop_affiliate_id',
    'order_is_paid_date',
    'cms_language_id',
    'pkg_shop_currency_id',
    'adr_shipping_is_dhl_packstation',
    'newsletter_signup',
    'canceled_date',
];
foreach ($orderIndicies as $orderIndex) {
    $query = 'show index from shop_order where Key_name = :keyName';
    $row = TCMSLogChange::getDatabaseConnection()->fetchAssoc($query, ['keyName' => $orderIndex]);
    if (false !== $row) {
        $query = sprintf(
            'ALTER TABLE `shop_order` DROP INDEX  %s',
            TCMSLogChange::getDatabaseConnection()->quoteIdentifier($orderIndex)
        );
        TCMSLogChange::RunQuery(__LINE__, $query);
    }
}

$query = "SELECT EXISTS (SELECT 1 FROM `cms_tbl_conf_index`
          WHERE `cms_tbl_conf_id` = :shopOrderTableId
            AND `name` = 'sales_per_article'
          )";
$indexEntryExists = TCMSLogChange::getDatabaseConnection()->fetchColumn(
    $query,
    ['shopOrderTableId' => TCMSLogChange::GetTableId('shop_article_stats')]
);
if (0 === (int) $indexEntryExists) {
    $data = TCMSLogChange::createMigrationQueryData('cms_tbl_conf_index', 'de')
        ->setFields(
            array(
                'cms_tbl_conf_id' => TCMSLogChange::GetTableId('shop_article_stats'),
                'name' => 'sales_per_article',
                'definition' => '`stats_sales`,`shop_article_id`',
                'type' => 'INDEX',
                'id' => TCMSLogChange::createUnusedRecordId('cms_tbl_conf_index'),
            )
        );
    TCMSLogChange::insert(__LINE__, $data);

    $query = 'ALTER TABLE `shop_article_stats`
                        ADD INDEX  `sales_per_article` ( `stats_sales`,`shop_article_id` )';
    TCMSLogChange::RunQuery(__LINE__, $query);
}
