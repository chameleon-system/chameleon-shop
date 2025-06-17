<h1>Build #1749134488</h1>
<h2>Date: 2025-06-05</h2>
<div class="changelog">
    - #66802 fix query for sales_without_shipping
    - #66802 add explanation of new <code>&lt;trans&gt;field&lt;/trans&gt;</code> marker for translateable fields
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'en')
->setFields([
    // 'translation' => 'Query',
    '049_helptext' => 'The query is used to collect the data. It is important that the query returns the following values:

1. sColumnName: Name for each individual tuple (corresponds to the X-axis), for example in the format YYYY-mm-dd, YYYY-mm, or YYYY.
2. dColumnValue: The value for the respective tuple (corresponds to the Y-axis).
3. All fields to be grouped by.
4. Any fields that are translateable should be wrapped in <code>&lt;trans&gt;field&lt;/trans&gt;</code> tags.

This ensures that the data is provided in a structured way, allowing for clear grouping and analysis.',
])
->setWhereEquals([
    'id' => 'e64db517-7cd9-a4e2-3049-f0b20b4bf5f2',
])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
    ->setFields([
        // 'translation' => 'Query',
        '049_helptext' => 'Die Abfrage dient der Sammlung der Daten. Wichtig ist, dass die Abfrage folgende Werte zurückgibt:

1. sColumnName: Name für jedes einzelne Tupel (entspricht der X-Achse), zum Beispiel im Format YYYY-mm-dd, YYYY-mm oder YYYY.
2. dColumnValue: Der Wert für das jeweilige Tupel (entspricht der Y-Achse).
3. Alle Felder, über die gruppiert werden soll.
4. Alle Felder, die übersetzbar sind sollten in <code>&lt;trans&gt;field&lt;/trans&gt;</code> tags eingeschlossen werden.

Dadurch werden die Daten strukturiert bereitgestellt und ermöglichen eine übersichtliche Gruppierung und Auswertung.'
    ])
    ->setWhereEquals([
        'id' => 'e64db517-7cd9-a4e2-3049-f0b20b4bf5f2',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
//        'name' => 'Revenue excluding shipping',
        'query' => "
SELECT
    [{sColumnName}] AS sColumnName,
    (`shop_order_item`.`price_discounted` * `shop_order_item`.`order_amount`) AS dColumnValue,
    <trans>shop_payment_method.name</trans> AS shop_payment_method_name
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
