# Chameleon System ShopPaymentHandlerSofortueberweisungBundle

## Overview
The ShopPaymentHandlerSofortueberweisungBundle integrates the Sofort (PayNow) payment service into the Chameleon Shop, allowing secure redirect to Sofort’s payment page and handling of notifications (IPN) to update order statuses.

## Features
- Redirect to Sofort’s payment gateway with order parameters.
- Validate and process notification callbacks using SHA256.
- Support sandbox (test) and production environments.
- Configure success, abort, and notification URLs.
- Automatic order status updates and optional email notifications.

## Installation
1. Place the migration script in your CMS update folder:
   ```bash
   cp vendor/chameleon-system/chameleon-shop/src/ShopPaymentHandlerSofortueberweisungBundle/Resources/doc/sofortueberweisung-install-1.inc.php private/extensions/updates/sofortueberweisung
   ```
2. Run **CMS → System → Updates** in the backend to import necessary DB tables and handlers.
3. Clear Chameleon cache.

## Sofort Merchant Portal Setup
1. Log in to Sofort merchant area (https://www.payment-network.com/sue_de/online-anbieterbereich/start).
2. Create a new project:
   - Shop System: *Other Shop System* → *Chameleon Shop*
   - Test Mode: *Yes* (switch to *No* for production)
   - Success URL: `https://<YOUR_URL>?transaction=-TRANSACTION-&amount=-AMOUNT-&created=-TIMESTAMP-&currency_id=-CURRENCY_ID-&user_variable_3=-USER_VARIABLE_3-&user_variable_3_hash_pass=-USER_VARIABLE_3_HASH_PASS-`
   - Abort URL: same as Success URL
   - Notification URL: `https://<YOUR_URL>` (IPN endpoint)
3. In **Advanced Settings → Passwords and Hash Algorithm**, set:
   - **Project Password** and **Notification Password**
   - **Hash Algorithm**: SHA256

## CMS Configuration
1. In Chameleon backend, go to **Shop → Payment Handlers**.
2. Edit or add **Sofortueberweisung** handler:
   - System Name: `sofortueberweisung`
   - Display Name: `Sofort (PayNow)`
   - Project ID (user_variable_0)
   - Notification Key (notification password)
   - Test Mode: enabled/disabled as per portal
   - Notification URL: as configured above
3. Save and clear cache.

## Usage Flow
1. Customer selects **Sofort (PayNow)** at checkout.
2. System redirects to Sofort with order details.
3. Customer completes payment and is redirected back.
4. Sofort sends IPN to your Notification URL.
5. Bundle validates signature, updates order status, and triggers post-payment hooks.

## API Reference
**Smart URL Handler**: `TCMSSmartURLHandler_ShopPaymentSofortueberweisungAPI` handles IPN GET/POST calls.
**Order Status Hook**: `TShopOrder::ExecutePaymentSofortueberweisungHook` processes payment confirmation.

## Resources
- German Integration Handbook (PDF): `Resources/doc/Handbuch_Eigenintegration_sofortueberweisung.de_v.2.0.pdf`

## License
Licensed under the MIT License. See the `LICENSE` file at the project root.