<h1>Build #1777897764</h1>
<h2>Date: 2026-04-16</h2>
<div class="changelog">
    - ref #70188: set limits of 10 items for the top category and most bought product widgets
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
        ->setFields([
                'query' => 'SELECT
                    base.sColumnName,
                    base.shop_category_name,
                    base.dColumnValue
                FROM (
                    SELECT [{sColumnName}] AS sColumnName,
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
                ) base
                JOIN (
                    SELECT
                        `shop_category`.`name` AS `shop_category_name`,
                        COUNT(`shop_category`.`id`) AS `total_count`
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
                    GROUP BY shop_category_name
                    ORDER BY `total_count` DESC
                    LIMIT 10
                ) top10 ON top10.shop_category_name = base.shop_category_name
                ORDER BY base.dColumnValue DESC',
        ])
        ->setWhereEquals([
                'id' => 'bed3cceb-9b2c-df56-1608-b156c20cbec9',
        ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
        ->setFields([
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
  ORDER BY ranked.sColumnName
                    LIMIT 10',
        ])
        ->setWhereEquals([
                'id' => 'e4bbb32d-91d0-165d-d90d-326021c68d84',
        ])
;
TCMSLogChange::update(__LINE__, $data);
