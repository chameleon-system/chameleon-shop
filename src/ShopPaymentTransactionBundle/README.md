Chameleon System ShopPaymentTransactionBundle
=============================================

Overview
--------
The ShopPaymentTransactionBundle implements capture, refund, and transaction management for orders in the Chameleon Shop. It integrates with the payment transaction workflow to record partial or full payments, refunds, and reversals directly from the shop backend or via automation.

Features
--------
- Compute and process payment captures at order creation or shipment
- Support refunds and payment reversals with item-level granularity
- Automatic determination of capture-on-shipment items vs. immediate capture
- Mappers to render collection/refund forms in backend order details
- Entities representing transactions, transaction positions, and types
- Helper service `PaymentTransactionHelper` for calculating item amounts
- Exceptions for invalid amounts, types, and unsupported handlers

Installation
------------
This bundle is included by default. To register manually, add to your AppKernel or bundles.php:
```php
new ChameleonSystem\\ShopPaymentTransactionBundle\\ChameleonSystemShopPaymentTransactionBundle(),
```
Run **CMS → System → Updates** if you have pending database migrations under `ShopPaymentTransactionBundle/install`.

Services
--------
- `chameleon_system_shop_payment_transaction.transaction_helper`:
  - Class: `ChameleonSystem\ShopPaymentTransactionBundle\Service\PaymentTransactionHelper`
  - Methods:
    - `getProductsCaptureOnOrderCreation(TdbShopOrder $order, bool $isCaptureOnShipment): array` – IDs and amounts to capture immediately
    - `getProductsCaptureOnShipping(TdbShopOrder $order, bool $isCaptureOnShipment): array` – IDs and amounts to capture later on shipment
    - `allowProductCaptureOnShipment(TdbShopOrderItem $orderedProduct): bool` – whether item qualifies for capture-on-shipment

Backend Integration
-------------------
Use the `CollectionFormForOrder` mapper to inject a partial capture/refund form into your order detail backend template:
```php
$data = [
  'order' => $orderObject,
  'paymentType' => TPkgShopPaymentTransactionData::TYPE_PAYMENT // or TYPE_CREDIT
];
echo $this->renderView('pkgShopPaymentTransaction/collection-form.html.twig', $data);
```

This uses `TPkgShopPaymentTransactionMapper_CollectionFormForOrder` to map:
- `sHeadline` – localized heading for payment or refund
- `items` – list of order items with available amounts
- `valueProducts`, `valueShipping`, etc. – summary fields
- `transactionList` – historical transaction log for the order
- `sHiddenFields` – form metadata for partial debit action

Programmatic API
----------------
To execute transactions programmatically, use `TPkgShopPaymentTransactionManager`:
```php
$manager = new TPkgShopPaymentTransactionManager($order);
$transactionData = $manager->getTransactionDataFromOrder(
   TPkgShopPaymentTransactionData::TYPE_PAYMENT, // or TYPE_CREDIT
   $itemAmountsMap // optional item restriction array
);
// Then call handler via payment method or directly record the transaction
```

Exceptions
----------
- `TPkgShopPaymentTransactionException_InvalidAmount`
- `TPkgShopPaymentTransactionException_InvalidTransactionType`
- `TPkgShopPaymentTransactionException_PaymentHandlerDoesNotSupportTransaction`

Customization
-------------
- Decorate `PaymentTransactionHelper` or implement `PaymentTransactionHelperInterface` for custom capture logic
- Extend mappers or Twig templates in `pkgShopPaymentTransaction/views` to adjust form display

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root for details.
