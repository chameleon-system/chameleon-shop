<h1>Build #1739765632</h1>
<h2>Date: 2025-02-17</h2>
<div class="changelog">
    - ref #65474: removed outdated payment handler
</div>
<?php

$outdatedPaymentHandlers = [
    'TShopPaymentHandlerEasyCash',
    'TShopPaymentHandlerEasyCash_Debit',
    'TShopPaymentHandlerSofortueberweisung',
    'TShopPaymentHandlerPayOne',
    'TShopPaymentHandlerMontrada',
];

$connection = TCMSLogChange::getDatabaseConnection();

foreach ($outdatedPaymentHandlers as $paymentHandler) {
    $paymentHandlerId = $connection->fetchOne(
        'SELECT id FROM `shop_payment_handler` WHERE class = :class',
        ['class' => $paymentHandler]
    );
    if(false === $paymentHandlerId) {
        continue;
    }

    $tableEditor = \TTools::GetTableEditorManager('shop_payment_handler');
    $tableEditor->AllowDeleteByAll(true);

    //Delete everything references
    $tableEditor->Delete($paymentHandlerId);
}