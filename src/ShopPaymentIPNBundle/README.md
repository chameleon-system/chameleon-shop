# Chameleon System ShopPaymentIPNBundle
=====================================

Overview
--------
The ShopPaymentIPNBundle provides robust handling of Instant Payment Notifications (IPN) from external payment providers. It queues incoming notifications, retries failed deliveries via cronjobs, and invokes configured payment handler logic to update orders and transactions.

Features
--------
- **IPN Queue**: Stores incoming IPN messages with payload, target URL, order reference, portal context, and status codes.
- **Smart URL Handler**: `TPkgShopPaymentIPN_TCMSSmartURLHandler` captures IPN requests, persists messages, and responds with `OK` or failure headers.
- **Cronjob Processing**: `TPkgShopPaymentIPN_TCmsCronJob_ProcessTrigger` retries delivery of queued IPN messages to target endpoints.
- **Retry Strategy**: Automatic backoff schedule after delivery failures (0, 5m, 15m, 1h, 4h, 4h, 8h, 24h, 24h, 24h) before marking as failed.
- **Trigger Execution**: `TPkgShopPaymentIpnTrigger::runTrigger()` sends HTTP POST with serialized payload and logs responses.
- **PaymentHandler Integration**: `TPkgShopPaymentIPN_TPkgShopPaymentHandlerGroup` validates IP source, parses requests, and dispatches to handler chain (`IPkgShopPaymentIPNHandler` implementations).
- **Transaction Hooks**: Invokes transaction processing via `TPkgShopPaymentIPN_TransactionDetails` and `TPkgShopPaymentTransactionManager`.
- **Extensible**: Implement `IPkgShopPaymentIPNHandler` to support custom IPN protocols.

Installation
------------

**Bundle Registration**: Already included by default; if needed, add to `AppKernel::registerBundles()`:

   ```php
   new ChameleonSystem\\ShopPaymentIPNBundle\\ChameleonSystemShopPaymentIPNBundle(),
   ```

Configuration
-------------
No additional parameters are required. IPN endpoints are configured via the `ipn_group_identifier` and handler group records in the `shop_payment_handler_group` table.

Quick Start
-----------
1. **Generate IPN URL**:
   ```php
   $ipnManager = new TPkgShopPaymentIPNManager();
   $ipnUrl = $ipnManager->getIPNURL($portal, $order);
   // Provide this URL to your payment provider as the IPN callback
   ```
2. **Handle Incoming IPN**:
   - Access `<baseUrl>/_api_pkgshopipn_<groupIdentifier>__<orderCmsIdent>` via GET/POST
   - The smart URL handler automatically logs and queues the IPN.
3. **Verify Delivery**:
   - Cronjob `chameleon_system_shop_payment_ipn.cronjob.process_triggers_cronjob` processes triggers.
   - Check the `pkg_shop_payment_ipn_message_trigger` table for retry status and logs.

Extending IPN Handling
----------------------
- **Custom Handler Chain**: Extend `TPkgShopPaymentIPN_TPkgShopPaymentHandlerGroup::getIPNHandlerChain()` to return custom handlers implementing `IPkgShopPaymentIPNHandler`.
- **Request Validation**: Override `validateIPNRequestData()` in your handler group to enforce signature or payload checks.
- **Post-Processing Hooks**: Use `handleIPNHook()` and `handleTransactionHook()` for additional order or notification logic.

Entities & Tables
-----------------
- `pkg_shop_payment_ipn_message` – stores raw IPN messages
- `pkg_shop_payment_ipn_trigger` – defines delivery endpoints and retry logic
- `pkg_shop_payment_ipn_message_trigger` – junction table queuing individual message deliveries
- `pkg_shop_payment_ipn_status` – lookup of IPN status codes per handler group

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root for details.