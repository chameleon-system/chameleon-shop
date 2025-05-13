Chameleon System ShopOrderStatusBundle
======================================

Overview
--------
The ShopOrderStatusBundle adds robust order status management to the Chameleon Shop. It provides:
- A programmatic endpoint to record and validate status updates with timestamps and custom messages.
- Persistence of status history and individual status items (order line adjustments).
- Automatic logging and optional email notifications on status changes.
- View mappers to render a chronological status timeline with order item details.

Features
--------
- Add arbitrary status updates to orders via `TPkgShopOrderStatusManagerEndPoint`
- Validate order and item consistency before saving statuses
- Store multiple status items (quantities, prices) per update
- Hook-based post-processing (e.g. send customer notification mail)
- Monolog channel `order_status` for structured status update logs
- Mapper `TPkgShopOrderStatusMapper_Status` to map order/status data for views
- Frontend action `SendOrderStatusEMail` to dispatch status emails

Installation
------------
This bundle is included by default. To register manually, add to your kernel or bundles.php:
```php
new ChameleonSystem\\ShopOrderStatusBundle\\ChameleonSystemShopOrderStatusBundle(),
```

Configuration
-------------
No special configuration parameters are required beyond enabling the bundle. The Monolog channel `order_status` is prepended automatically in the DI extension.

Programmatic API
----------------
Use the endpoint to add statuses in your custom services or controllers:
```php
use TPkgShopOrderStatusManagerEndPoint;
use TPkgShopOrderStatusDataEndPoint;
use TPkgShopOrderStatusItemData;

$endpoint = new TPkgShopOrderStatusManagerEndPoint();
$data = (new TPkgShopOrderStatusDataEndPoint($order, 'shipped', time(), 'Your order has been shipped'))
    ->addItem(new TPkgShopOrderStatusItemData($orderItemId, $quantity, $info));
$status = $endpoint->addStatus($data);
```

View Integration
----------------
Map and render the status timeline in order detail templates using the status mapper:
```php
// Prepare source objects for the mapper
$mapper = new TPkgShopOrderStatusMapper_Status();
$visitor = new MapperVisitorRestricted();
$visitor->SetSourceObject('oObject', $order);   // TdbShopOrder or TdbShopOrderStatus
$visitor->SetSourceObject('local', TCMSLocal::GetActive());

$mapper->Accept($visitor, true, $cacheTriggerManager);
$data = $visitor->GetMappedValues();

foreach ($data['aStatusList'] as $status) {
    echo '<div class="status-entry">';
    echo '<span class="status-date">'.$status['date'].'</span>';
    echo '<span class="status-name">'.$status['codeName'].'</span>';
    echo '<p>'.$status['info'].'</p>';
    // render each position if needed
    echo '</div>';
}
```

Frontend Action
---------------
To send status update emails via browser/API, use the `SendOrderStatusEMail` frontend action:
```php
$action = TTools::GetModuleObject('pkgRunFrontendAction', 'standard', [], 'SendOrderStatusEMail');
$action->_CallMethod('SendOrderStatusEMail', ['orderId' => $order->id, 'statusId' => $status->id]);
```

API Reference
-------------
**TPkgShopOrderStatusManagerEndPoint**
- `addStatus(IPkgShopOrderStatusData $data): TdbShopOrderStatus` – validate & save status + items

**TPkgShopOrderStatusDataEndPoint** (implements `IPkgShopOrderStatusData`)
- Payload container for order, status code, timestamp, info, and items

**TPkgShopOrderStatusItemData** (implements `IPkgShopOrderStatusItemData`)
- Payload container for individual order item updates

**TPkgShopOrderStatusMapper_Status**
- Maps `oObject` (order or status) + `local` into `aStatusList` or single status view vars

**TPkgRunFrontendAction_SendOrderStatusEMail**
- `_CallMethod('SendOrderStatusEMail', array $data)` – trigger customer notification email

Exceptions
----------
- `TPkgShopOrderStatusException_PostOrderStatusAddedExceptions` – aggregates exceptions from post-add hooks
- `TPkgShopOrderStatusException_OrderStatusCodeNotFound` – missing status code mapping

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.
