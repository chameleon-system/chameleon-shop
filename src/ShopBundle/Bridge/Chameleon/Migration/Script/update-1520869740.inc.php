<h1>Build #1520869740</h1>
<h2>Date: 2018-03-12</h2>
<div class="changelog">
    - Use service IDs for cronjobs.
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.clean_shop_search_log_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_CleanShopSearchLog',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.clean_shop_order_basket_log_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopCleanShopOrderBasketLog',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopImportOrderStatus',
    ])
;
TCMSLogChange::delete(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.search_cache_garbage_collector_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopSearchCacheGarbageCollector',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.search_index_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopSearchIndex',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.send_basket_log_statistics_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopSendBasketLogStatisics',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.send_order_notifications_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopSendOrderNotifications',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_cronjobs', 'en')
    ->setFields([
        'cron_class' => 'chameleon_system_shop.cronjob.time_based_discount_cache_cronjob',
    ])
    ->setWhereEquals([
        'cron_class' => 'TCMSCronJob_ShopTimeBasedDiscountCache',
    ])
;
TCMSLogChange::update(__LINE__, $data);
