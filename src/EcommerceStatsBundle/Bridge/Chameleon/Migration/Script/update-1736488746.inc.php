<h1>Build #1736488746</h1>
<h2>Date: 2025-01-10</h2>
<div class="changelog">
    - #65182: add new statistic group, top seller
</div>
<?php

$id = TCMSLogChange::createUnusedRecordId('pkg_shop_statistic_group');

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        'name' => 'Topseller',
        'query' => "SELECT `shop_order_item`.`name` AS `sColumnName`,
                        SUM(`shop_order_item`.`order_amount`) AS `dColumnValue`
                    FROM `shop_order`
                    LEFT JOIN `shop_order_item` 
                    ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
                    [{sCondition}]
                    AND `shop_order`.`canceled` = '0'
                    GROUP BY [{sColumnName}]
                    ORDER BY `dColumnValue` DESC
                    LIMIT 10",
        'hasCurrency' => '0',
        'system_name' => 'top_seller',
        'id' => $id,
    ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'name' => 'Meistverkaufte Produkte',
        ])
    ->setWhereEquals([
        'id' => $id,
    ])
;
TCMSLogChange::update(__LINE__, $data);
