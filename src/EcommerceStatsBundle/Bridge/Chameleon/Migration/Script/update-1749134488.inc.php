<h1>Build #1749134488</h1>
<h2>Date: 2025-06-05</h2>
<div class="changelog">
    - #66802 fix query for sales_without_shipping
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
//        'name' => 'Revenue excluding shipping',
        'query' => "
SELECT
    [{sColumnName}] AS sColumnName,
    (`shop_order_item`.`price_discounted` * `shop_order_item`.`order_amount`) AS dColumnValue,
    shop_payment_method.name AS shop_payment_method_name
FROM `shop_order`
JOIN shop_payment_method ON shop_order.shop_payment_method_id = shop_payment_method.id
LEFT JOIN `shop_order_item` ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
   [{sCondition}]
   AND `shop_order`.`canceled` = '0'
ORDER BY datecreated
      ",
    ])
    ->setWhereEquals([
        'system_name' => 'sales_without_shipping',
    ])
;
TCMSLogChange::update(__LINE__, $data);
