<h1>Build #1735894711</h1>
<h2>Date: 2025-01-03</h2>
<div class="changelog">
    - #65366: change category statistics to manufacturer sales stats
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        'name' => 'Manufacturer (Sales)',
        'groups' => 'manufacturerName',
        'query' => "SELECT [{sColumnName}] AS sColumnName,
               `shop_order_item`.`order_amount` AS totalordered,
               `shop_order_item`.`order_price_after_discounts` AS dColumnValue,
               `shop_order`.`shop_payment_method_name`,
               `shop_order_item`.`shop_manufacturer_name` AS manufacturerName,
               `shop_order_item`.*
          FROM `shop_order_item`
     LEFT JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
               [{sCondition}]
           AND `shop_order`.`canceled` = '0'
     ORDER BY  [{sColumnName}]
  ",
    ])
    ->setWhereEquals([
        'id' => '5960bc8c-2b9b-11df-9c53-00fcefbad5fb',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'name' => 'Hersteller (Verkäufe)',
        ])
    ->setWhereEquals([
        'id' => '5960bc8c-2b9b-11df-9c53-00fcefbad5fb',
    ])
;
TCMSLogChange::update(__LINE__, $data);
