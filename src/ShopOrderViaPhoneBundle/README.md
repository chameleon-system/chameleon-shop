Chameleon System ShopOrderViaPhoneBundle
=========================================

Overview
--------
The ShopOrderViaPhoneBundle enables customers to place an order request via phone by sending their basket contents to a predefined email address. It seamlessly integrates into the checkout wizard and basket modules, providing a "telephone order" link and form.

Features
--------
- Add a "Telephone order" link in the basket to open a modal form
- Render a telephone order form to collect customer name, telephone number, and optional subject
- Validate required fields and display inline error messages
- Send an email containing the order details to a configured mailbox
- Redirect to a thank-you page upon successful email dispatch
- Leverage existing ShopOrderWizard and basket services without requiring a full checkout

Installation
------------
This bundle is included by default in the Chameleon Shop. To register manually, add to your kernel or bundles.php:
```php
new ChameleonSystem\\ShopOrderViaPhoneBundle\\ChameleonSystemShopOrderViaPhoneBundle(),
```

Integration
-----------
1. **Telephone Order Link**: In your basket template, render the telephone order link using the mapper:
   ```php
   // Inside the basket view
   $basketHtml = $oBasket->Render('telephoneOrderLink', 'Customer');
   echo $basketHtml;
   ```
   This uses `TPkgShopBasketMapper_TelephoneOrder` to map `aLink` with keys:
   - `sLinkURL`: anchor `#telephoneModal`
   - `sTitle`: translated label `chameleon_system_shop_order_via_phone.action.open_telefon_order_form`

2. **Telephone Order Form**: Define a modal with ID `telephoneModal` in your theme and render the form:
   ```html
   <div id="telephoneModal" class="modal">
     <div class="modal-content">
       <?php echo $oBasket->Render('telephoneOrderForm', 'Customer'); ?>
     </div>
   </div>
   ```
   The `TPkgShopBasketMapper_TelephoneOrderForm` mapper requires:
   - A CMS text block with system name `telephone_order_info_text` for info text
   - Translator key `chameleon_system_shop_order_via_phone.action.open_telefon_order_form`

3. **Order Wizard Hook**: The `MTPkgShopOrderViaPhone_MTShopOrderWizard` class (extending the normal checkout wizard) exposes the `OrderViaPhone` method. It reads user input from the URL parameter `order_via_phone`:
   - Required fields: `firstname`, `lastname`, `tel`
   - Optional `subject` field for email subject override
   After validation, it calls `OrderViaPhoneSendEmail()` and redirects to the thank-you system page `order-via-phone`.

4. **Thank-You Page**: Create a system page with URL key `order-via-phone` to display a confirmation message after sending.

API Reference
-------------
**TPkgShopBasketMapper_TelephoneOrder**
- `Accept(...)` sets `aLink` with `sLinkURL` and `sTitle` for link rendering.

**TPkgShopBasketMapper_TelephoneOrderForm**
- `GetRequirements(...)` injects `oActiveUser`, `oActivePage`, `oTextBlock`, and field names.
- `Accept(...)` maps:
  - `sSpotName`, `sTitle`, `sText` (form configuration)
  - `aFieldFirstName`, `aFieldLastName`, `aFieldTel`, `aFieldReason` arrays with `sError`, `sValue`, `sFieldId`, `sName`.
  - `sRawInfoText` from `telephone_order_info_text` text block
  - `sAction` URL for form submission
  - `sOverallError` for form-level messages

**MTPkgShopOrderViaPhone_MTShopOrderWizard**
- `OrderViaPhone()` – entry-point, reads URL data, validates (`OrderViaPhoneDataValid()`), sends email (`OrderViaPhoneSendEmail()`), redirects (`OrderViaPhoneRedirectToThankYouPage()`).
  * Use the translator key `chameleon_system_shop_order_via_phone.action.open_telefon_order_form` for link label.

**Email Template**
- Provide a DataMailProfile named `order-via-phone` in the CMS (path: Emails > Profiles).
- The profile should define an object view template `vOrderViaPhoneMail` under `emails/Customer` to format the order details in the email.

Translator Keys
---------------
- chameleon_system_shop_order_via_phone.action.open_telefon_order_form
- ORDER-VIA-PHONE-CANT-SEND-EMAIL (message when email fails)
- ERROR-USER-REQUIRED-FIELD-MISSING (form validation errors)

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.
