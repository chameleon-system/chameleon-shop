<h1>Build #1750164616</h1>
<h2>Date: 2025-06-09</h2>
<div class="changelog">
    - ref #66938 fix statistic groups everywhere
</div>
<?php

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\FieldTranslationUtil;

/** @var FieldTranslationUtil $util */
$util = ServiceLocator::get('chameleon_system_core.util.field_translation');

$germanLanguage = \TdbCmsLanguage::GetNewInstance();
$germanLanguage->LoadFromField('iso_6391', 'de');

$fieldName = 'name';
if ($util->isTranslationNeeded($germanLanguage)) {
    $fieldName = $util->getTranslatedFieldName('pkg_shop_statistic_group', 'name', $germanLanguage);
}

//Payment Method Group -> update-1736339437.inc.php
$paymentMethodCorrectedId = '86d68501-3159-c5dd-95aa-850c63e95caf';
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'id' => $paymentMethodCorrectedId,
        'system_name' => 'used_payments',
    ])
    ->setWhereEquals(
        [$fieldName => 'Genutzte Bezahlmethoden']
    );
TCMSLogChange::update(__LINE__, $data);

//Toppseller Group -> update-1736488746.inc.php
$topSellerCorrectedId = 'e4bbb32d-91d0-165d-d90d-326021c68d84';
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'id' => $topSellerCorrectedId,
        'system_name' => 'top_seller',
    ])
    ->setWhereEquals(
        [$fieldName => 'Meistverkaufte Produkte']
    );
TCMSLogChange::update(__LINE__, $data);

//Top Category Group -> update-1736493666.inc.php
$topCategoryCorrectedId = 'bed3cceb-9b2c-df56-1608-b156c20cbec9';
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'id' => $topCategoryCorrectedId,
        'system_name' => 'top_categories',
    ])
    ->setWhereEquals(
        [$fieldName => 'Top Kategorien']
    );
TCMSLogChange::update(__LINE__, $data);

//Top Customer Group -> update-1736503086.inc.php
$topCustomerCorrectedId = '7abee4ca-3232-6257-97bd-809df1ed3921';
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'id' => $topCustomerCorrectedId,
        'system_name' => 'customer_types',

        //update-1737003086.inc.php
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
    ->setWhereEquals(
        [$fieldName => 'Kundentypen']
    );
TCMSLogChange::update(__LINE__, $data);

//update-1749469381.inc.php
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
        'id' => $topCustomerCorrectedId,
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
        'id' => 'bed3cceb-9b2c-df56-1608-b156c20cbec9',
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
        'id' => '86d68501-3159-c5dd-95aa-850c63e95caf',
    ])
;
TCMSLogChange::update(__LINE__, $data);

//update-1750164616.inc.php
$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'sales_count',
    ])
    ->setWhereEquals([
        $fieldName => 'Anzahl Bestellungen',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'basket_size_without_shipping',
    ])
    ->setWhereEquals([
        $fieldName => 'Ø Warenkorb Wert ohne Versand',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'sales_without_shipping',
    ])
    ->setWhereEquals([
        $fieldName => 'Umsatz ohne Versand',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'used_payments',
    ])
    ->setWhereEquals([
        $fieldName => 'Genutzte Bezahlmethoden',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'number_of_ordered_items',
    ])
    ->setWhereEquals([
        $fieldName => 'Anzahl bestellter Artikel',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'sales_by_manufacturer',
    ])
    ->setWhereEquals([
        $fieldName => 'Hersteller (Verkäufe)',
    ])
;
TCMSLogChange::update(__LINE__, $data);

$data = TCMSLogChange::createMigrationQueryData('pkg_shop_statistic_group', 'de')
    ->setFields([
        'system_name' => 'average_basket_products',
    ])
    ->setWhereEquals([
        $fieldName => 'Ø Warenkorb Größe',
    ])
;
TCMSLogChange::update(__LINE__, $data);