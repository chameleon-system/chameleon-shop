<h1>Build #1737003086</h1>
<h2>Date: 2025-04-24</h2>
<div class="changelog">
    - #65182, #66402: fix query for customer types
</div>
<?php

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
//        'name' => 'Customer Types',
        'query' => "SELECT 
                    CASE 
                        WHEN `data_extranet_user`.`id` IS NOT NULL THEN
                            CASE 
                                WHEN DATE(`shop_order`.`datecreated`) = DATE(`data_extranet_user`.`datecreated`) THEN 'Neukunde'
                                ELSE 'Bestandskunde'
                            END
                        ELSE 'Gastkunde'
                        END AS `sColumnName`,
                        COUNT(*) AS `dColumnValue`
                    FROM `shop_order`
                    LEFT JOIN `data_extranet_user`
                    ON `shop_order`.`data_extranet_user_id` = `data_extranet_user`.`id`
                            [{sCondition}]
                    AND `shop_order`.`canceled` = '0'
                    GROUP BY `sColumnName`",
    ])
    ->setWhereEquals([
        'system_name' => 'customer_types',
    ])
;
TCMSLogChange::update(__LINE__, $data);
