<h1>Build #1736339437</h1>
<h2>Date: 2025-01-03</h2>
<div class="changelog">
    - #65182: add new statistic groups
</div>
<?php

$id = TCMSLogChange::createUnusedRecordId('pkg_shop_statistic_group');

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        'name' => 'Payment Method',
        'groups' => 'payment_methods',
        'query' => "SELECT `shop_payment_method`.`name` AS sColumnName, 1 AS dColumnValue
                    FROM `shop_order`
                    LEFT JOIN `shop_payment_method` ON `shop_order`.`shop_payment_method_id` = `shop_payment_method`.`id`
               [{sCondition}]
                    AND `shop_order`.`canceled` = '0'
                    ORDER BY  [{sColumnName}]",
        'id' => $id

    ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'name' => 'Genutzte Bezahlmethoden',
        ])
    ->setWhereEquals([
        'id' => $id,
    ])
;
TCMSLogChange::update(__LINE__, $data);