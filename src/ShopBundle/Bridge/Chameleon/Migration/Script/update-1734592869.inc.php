<h1>Build #1734592869</h1>
<h2>Date: 2024-12-19</h2>
<div class="changelog">
    - #59148: remove amazon payment refrerences from db objects
</div>
<?php

$dbConnection = TCMSLogChange::getDatabaseConnection();
$classesToBeDeleted = [
    '\ChameleonSystem\AmazonPaymentBundle\pkgExtranetUser\AmazonPaymentExtranetAddress',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\db\AmazonShopPaymentMethod',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\db\AmazonShopOrder',
    '\ChameleonSystem\AmazonPaymentBundle\pkgExtranetUser\AmazonPaymentExtranetUser',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentShopOrder'

];

foreach ($classesToBeDeleted as $class) {
    $query = "DELETE FROM `cms_tbl_extension`
                WHERE `name` = :class";

    $dbConnection->executeStatement($query, ['class' => $class]);
}

$classesToBeDeleted = [
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonBasketOrderStep',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\WebModules\AmazonShopOrderWizard',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentBasket',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\WebModules\AmazonShopBasket'
];

foreach ($classesToBeDeleted as $class) {
    $query = "DELETE FROM `pkg_cms_class_manager_extension`
                WHERE `class` = :class";

    $dbConnection->executeStatement($query, ['class' => $class]);
}

$query = "SELECT * 
            FROM `shop_order_step` 
            WHERE `class` LIKE '%AmazonPaymentBundle%';";

$classesToBeDeleted = [
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonLoginStep',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonShippingStep',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonConfirmOrderStep',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonAddressStep',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonLoginStep, ',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonShippingStep, ',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonConfirmOrderStep, ',
    '\ChameleonSystem\AmazonPaymentBundle\pkgShop\OrderSteps\AmazonAddressStep, '
    ];

$steps = $dbConnection->fetchAllAssociative($query);

foreach ($steps as $step) {
    $updatedClass = str_replace(
        $classesToBeDeleted,
        '',
        $step['class']
    );

    $updatedClass = trim($updatedClass, ',');

    $updateQuery = "UPDATE `shop_order_step` SET `class` = :class WHERE `id` = :id";
    $dbConnection->executeStatement($updateQuery, ['class' => $updatedClass, 'id' => $step['id']]);
}