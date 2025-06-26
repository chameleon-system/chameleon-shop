<h1>Build #1736493666</h1>
<h2>Date: 2025-01-10</h2>
<div class="changelog">
    - #65182: add new statistic group, top category
    - #66938 //UPDATE id of statistic group
</div>
<?php

$id = 'bed3cceb-9b2c-df56-1608-b156c20cbec9';
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        'name' => 'Top Categories',
        'query' => "SELECT `shop_category`.`name` AS `sColumnName`,
                        COUNT(`shop_category`.`id`) AS `dColumnValue`
                    FROM `shop_order`
                    LEFT JOIN `shop_order_item` 
                    ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
                    LEFT JOIN `shop_article`
                    ON `shop_order_item`.`articlenumber` = `shop_article`.`articlenumber`
                    LEFT JOIN `shop_article_shop_category_mlt`
                    ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
                    LEFT JOIN `shop_category`
                    ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`
                    [{sCondition}]
                    AND `shop_order`.`canceled` = '0'
                    GROUP BY [{sColumnName}]
                    ORDER BY `dColumnValue` DESC",
        'hasCurrency' => '0',
        'system_name' => 'top_categories',
        'id' => $id,
    ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'name' => 'Top Kategorien',
        ])
    ->setWhereEquals([
        'id' => $id,
    ])
;
TCMSLogChange::update(__LINE__, $data);
