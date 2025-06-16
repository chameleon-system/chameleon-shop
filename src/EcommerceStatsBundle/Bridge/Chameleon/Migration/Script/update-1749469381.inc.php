<h1>Build #1749469381</h1>
<h2>Date: 2025-06-09</h2>
<div class="changelog">
    - ref #66837 translate static group queries and introduce "static" translateable values to queries
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('cms_field_conf', 'de')
    ->setFields([
        // 'translation' => 'Query',
        '049_helptext' => 'The query is used to collect the data. It is important that the query returns the following values:

1. sColumnName: Name for each individual tuple (corresponds to the X-axis), for example in the format YYYY-mm-dd, YYYY-mm, or YYYY.
2. dColumnValue: The value for the respective tuple (corresponds to the Y-axis).
3. All fields to be grouped by.
4. Any fields that are translateable should be wrapped in <code>&lt;trans&gt;field&lt;/trans&gt;</code> tags.
5. You can provide your own language dependent values by writing them like so: <code>&lt;trans&gt;{"de": "Neukunde", "en": "new customer", "default": "en"}&lt;/trans&gt;</code> where <code>de</code> and <code>en</code> are language codes.

This ensures that the data is provided in a structured way, allowing for clear grouping and analysis.',
        '049_helptext__de' => 'Die Abfrage dient der Sammlung der Daten. Wichtig ist, dass die Abfrage folgende Werte zurückgibt:

1. sColumnName: Name für jedes einzelne Tupel (entspricht der X-Achse), zum Beispiel im Format YYYY-mm-dd, YYYY-mm oder YYYY.
2. dColumnValue: Der Wert für das jeweilige Tupel (entspricht der Y-Achse).
3. Alle Felder, über die gruppiert werden soll.
4. Alle Felder, die übersetzbar sind sollten in <code>&lt;trans&gt;field&lt;/trans&gt;</code> tags eingeschlossen werden.
5. Eigene sprachabhängige Werte können so angegeben werden: <code>&lt;trans&gt;{"de": "Neukunde", "en": "new customer", "default": "en"}&lt;/trans&gt;</code>, wobei <code>de</code> und <code>en</code> Sprachcodes sind.

Dadurch werden die Daten strukturiert bereitgestellt und ermöglichen eine übersichtliche Gruppierung und Auswertung.'
    ])
    ->setWhereEquals([
        'id' => 'e64db517-7cd9-a4e2-3049-f0b20b4bf5f2',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Kundentypen',
      'query' => 'SELECT 
                    CASE 
                        WHEN `data_extranet_user`.`id` IS NOT NULL THEN
                            CASE 
                                WHEN DATE(`shop_order`.`datecreated`) = DATE(`data_extranet_user`.`datecreated`) THEN \'<trans>{"de": "Neukunde", "en": "new customer", "default": "de"}</trans>\'
                                ELSE \'<trans>{"de": "Bestandskunde", "en": "existing customer", "default": "de"}</trans>\'
                            END
                        ELSE \'<trans>{"de": "Gastkunde", "en": "guest", "default": "de"}</trans>\'
                        END AS `sColumnName`,
                        COUNT(*) AS `dColumnValue`
                    FROM `shop_order`
                    LEFT JOIN `data_extranet_user`
                    ON `shop_order`.`data_extranet_user_id` = `data_extranet_user`.`id`
                            [{sCondition}]
                    AND `shop_order`.`canceled` = \'0\'
                    GROUP BY `sColumnName`',
  ])
  ->setWhereEquals([
      'id' => '14218a53-dc84-81cf-0885-7c1801497ed9',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Top Kategorien',
      'query' => 'SELECT <trans>`shop_category`.`name`</trans> AS `sColumnName`,
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
                    AND `shop_order`.`canceled` = \'0\'
                    GROUP BY [{sColumnName}]
                    ORDER BY `dColumnValue` DESC',
  ])
  ->setWhereEquals([
      'id' => '70c1d8e9-7c01-5c9e-c54e-4f4a6ad111bb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Umsatz ohne Versand',
      'query' => 'SELECT
    [{sColumnName}] AS sColumnName,
    (`shop_order_item`.`price_discounted` * `shop_order_item`.`order_amount`) AS dColumnValue,
    <trans>shop_payment_method.name</trans> AS shop_payment_method_name
FROM `shop_order`
JOIN shop_payment_method ON shop_order.shop_payment_method_id = shop_payment_method.id
LEFT JOIN `shop_order_item` ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
   [{sCondition}]
   AND `shop_order`.`canceled` = \'0\'
ORDER BY datecreated
      ',
  ])
  ->setWhereEquals([
      'id' => '74292e9c-2b9a-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Anzahl bestellter Artikel',
      'query' => '        SELECT [{sColumnName}] AS sColumnName,
               `shop_order_item`.`order_amount` AS dColumnValue,
               <trans>`shop_payment_method`.`name`</trans> AS shop_payment_method_name
          FROM `shop_order`
     JOIN shop_payment_method ON shop_order.shop_payment_method_id = shop_payment_method.id
     LEFT JOIN `shop_order_item` ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
               [{sCondition}]
           AND `shop_order`.`canceled` = \'0\'
      ORDER BY datecreated
  ',
  ])
  ->setWhereEquals([
      'id' => '07f209fa-2b9b-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Hersteller (Verkäufe)',
      'query' => 'SELECT [{sColumnName}] AS sColumnName,
               `shop_order_item`.`order_amount` AS totalordered,
               `shop_order_item`.`order_price_after_discounts` AS dColumnValue,
               <trans>`shop_payment_method`.`name`</trans>,
               `shop_order_item`.`shop_manufacturer_name` AS manufacturerName,
               `shop_order_item`.*
          FROM `shop_order_item`
     LEFT JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
         JOIN shop_payment_method ON shop_order.shop_payment_method_id = shop_payment_method.id
               [{sCondition}]
           AND `shop_order`.`canceled` = \'0\'
     ORDER BY  [{sColumnName}]
  ',
  ])
  ->setWhereEquals([
      'id' => '5960bc8c-2b9b-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Genutzte Bezahlmethoden',
      'query' => 'SELECT <trans>shop_payment_method.name</trans> AS sColumnName, 
                    COUNT(<trans>shop_payment_method.name</trans>) AS `dColumnValue`
                    FROM `shop_order`
                    JOIN shop_payment_method ON shop_order.shop_payment_method_id = shop_payment_method.id
               [{sCondition}]
                    AND `shop_order`.`canceled` = \'0\'
                    GROUP BY <trans>shop_payment_method.name</trans>
                    ORDER BY  [{sColumnName}]',
  ])
  ->setWhereEquals([
      'id' => '7f8d9be6-ff13-a724-e2f7-ccf49cd7a550',
  ])
;
TCMSLogChange::update(__LINE__, $data);

