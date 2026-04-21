<h1>Build #1776344036</h1>
<h2>Date: 2026-04-16</h2>
<div class="changelog">
    - ref #69940: fix queries, add missing groups, adjust query
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
        ->setFields([
            // 'name' => 'Umsatz ohne Versand',
                'query' => "  SELECT [{sColumnName}] AS sColumnName,
      SUM(`shop_order_item`.`price_discounted` * `shop_order_item`.`order_amount`) AS dColumnValue,
      `shop_payment_method`.`name` AS shop_payment_method_name
  FROM `shop_order`
  JOIN `shop_payment_method`
      ON `shop_order`.`shop_payment_method_id` = `shop_payment_method`.`id`
  LEFT JOIN `shop_order_item`
      ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
     [{sCondition}]
     AND `shop_order`.`canceled` = '0'
  GROUP BY [{sColumnName}], `shop_payment_method_name`
  ORDER BY [{sColumnName}], `shop_payment_method_name`
      ", // prev.: ...'<trans>shop_payment_method.name</trans> AS shop_payment_method_name\nFROM `shop_order`\nJOIN shop_paym'...
        ])
        ->setWhereEquals([
                'id' => '74292e9c-2b9a-11df-9c53-00fcefbad5fb',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Kundentypen',
       'groups' => 'customer_type', // prev.: ''
      'query' => 'SELECT [{sColumnName}] AS sColumnName,
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
                    GROUP BY `sColumnName`', // prev.: ...'COUNT(*) AS dColumnValue,\n         CASE\n             WHEN `data_extranet_user`.`id` IS NOT NULL THEN'...
  ])
  ->setWhereEquals([
      'id' => '7abee4ca-3232-6257-97bd-809df1ed3921',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Anzahl bestellter Artikel',
      'groups' => '',
      'query' => 'SELECT [{sColumnName}] AS sColumnName,
         SUM(`shop_order_item`.`order_amount`) AS dColumnValue
    FROM `shop_order`
    LEFT JOIN `shop_order_item`
           ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
         [{sCondition}]
     AND `shop_order`.`canceled` = \'0\'
   GROUP BY [{sColumnName}] 
   ORDER BY [{sColumnName}] ', // prev.: ...'        SELECT [{sColumnName}] AS sColumnName,\n               `shop_order_item`.`order_amount` AS dC'...
  ])
  ->setWhereEquals([
      'id' => '07f209fa-2b9b-11df-9c53-00fcefbad5fb',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Genutzte Bezahlmethoden',
      'query' => "SELECT [{sColumnName}] AS sColumnName,
      COUNT(DISTINCT `shop_order`.`id`) AS dColumnValue,
      `shop_payment_method`.`name` AS shop_payment_method_name
  FROM `shop_order`
  JOIN `shop_payment_method`
      ON `shop_order`.`shop_payment_method_id` = `shop_payment_method`.`id`
     [{sCondition}]
     AND `shop_order`.`canceled` = '0'
GROUP BY [{sColumnName}], shop_payment_method_name
ORDER BY [{sColumnName}], shop_payment_method_name
  "])
  ->setWhereEquals([
      'id' => '86d68501-3159-c5dd-95aa-850c63e95caf',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Top Kategorien',
      'groups' => 'shop_category_name',
      'query' => 'SELECT [{sColumnName}] AS sColumnName,
`shop_category`.`name` AS `shop_category_name`,
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
                    GROUP BY [{sColumnName}], shop_category_name
                    ORDER BY `dColumnValue` DESC', // prev.: ...'<trans>`shop_category`.`name`</trans> AS `sColumnName`,\n                        COUNT(`shop_category'...
  ])
  ->setWhereEquals([
      'id' => 'bed3cceb-9b2c-df56-1608-b156c20cbec9',
  ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
  ->setFields([
      // 'name' => 'Meistverkaufte Produkte',
      'groups' => 'product_name',
      'query' => 'SELECT ranked.sColumnName,
         ranked.product_name,
         ranked.dColumnValue
  FROM (
      SELECT [{sColumnName}] AS sColumnName,
             `shop_order_item`.`name` AS product_name,
             SUM(`shop_order_item`.`order_amount`) AS dColumnValue,
             ROW_NUMBER() OVER (
                 PARTITION BY [{sColumnName}]
                 ORDER BY SUM(`shop_order_item`.`order_amount`) DESC, `shop_order_item`.`name` ASC
             ) AS row_num
        FROM `shop_order`
        LEFT JOIN `shop_order_item`
               ON `shop_order`.`id` = `shop_order_item`.`shop_order_id`
             [{sCondition}]
         AND `shop_order`.`canceled` = \'0\'
       GROUP BY [{sColumnName}], `shop_order_item`.`name`
  ) ranked
  WHERE ranked.row_num = 1
  ORDER BY ranked.sColumnName',
  ])
  ->setWhereEquals([
      'id' => 'e4bbb32d-91d0-165d-d90d-326021c68d84',
  ])
;
TCMSLogChange::update(__LINE__, $data);
