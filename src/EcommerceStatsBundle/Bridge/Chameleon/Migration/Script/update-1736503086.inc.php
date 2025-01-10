<h1>Build #1736503086</h1>
<h2>Date: 2025-01-10</h2>
<div class="changelog">
    - #65182: add new statistic group, customer type
</div>
<?php

$id = TCMSLogChange::createUnusedRecordId('pkg_shop_statistic_group');

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'en')
    ->setFields([
        'name' => 'Customer Types',
        'query' => "SELECT 
                    CASE 
                        WHEN `data_extranet_user`.`customer_number` IS NOT NULL THEN
                            CASE 
                                WHEN DATE(`shop_order`.`datecreated`) = DATE(`data_extranet_user`.`datecreated`) THEN 'Neukunde'
                                ELSE 'Bestandskunde'
                            END
                        ELSE 'Gastkunde'
                        END AS `sColumnName`,
                        COUNT(*) AS `dColumnValue`
                    FROM `shop_order`
                    LEFT JOIN `data_extranet_user`
                    ON `shop_order`.`customer_number` = `data_extranet_user`.`customer_number`
                            [{sCondition}]
                    AND `shop_order`.`canceled` = '0'
                    GROUP BY `sColumnName`",
        'hasCurrency' => '0',
        'system_name' => 'customer_types',
        'id' => $id,
    ])
;
TCMSLogChange::insert(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'name' => 'Kundentypen',
        ])
    ->setWhereEquals([
        'id' => $id,
    ])
;
TCMSLogChange::update(__LINE__, $data);