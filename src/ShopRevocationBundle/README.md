ShopRevocationBundle
====================

Overview
--------
The ShopRevocationBundle provides a reusable frontend revocation flow for Chameleon shops.
It is intended as a generic core bundle: the bundle delivers the form module, validation,
relevance checks, persistence, and notification mails, while each project integrates the
actual page, route, footer link, and legal content.

Current functionality
---------------------
The bundle currently provides:

- A CMS module `Elektronischer Widerruf` with the service class
  `chameleon_system_shop_revocation.modules.shop_revocation_module`
- A default Twig template for the revocation form at
  `modules/revocationForm/standard.html.twig`
- A guest flow with mandatory fields `name`, `email`, and `ordernumber`
- A logged-in flow with prefilled `name` and `email`, an order dropdown, and a fallback
  to manual order number entry if no relevant orders are available
- Server-side validation for required fields, email format, shop ownership, email/order
  matching, and revocation relevance
- Persistence into `shop_order_revocation`
- Back-reference from `shop_order.shop_order_revocation_id` to the latest saved revocation
- Automatic customer and shop-owner notification mails via the mail profiles
  `revocation_customer` and `revocation_shop_owner`
- German and English translations for the default UI

Default behavior
----------------
### Order relevance
The revocation relevance check is implemented in `ShopRevocationOrderRelevanceService`.

By default:

- canceled orders are excluded
- the relevance window is `14 + x` days
- `x` is read from `shop.revocation_additional_days`
- the default additional-days value is `3`
- the default relevance date comes from `shop_order.datecreated`

The use of `datecreated` is a pragmatic core fallback. Projects that can provide a real
delivery date should override the delivery date provider.

### Logged-in order selection
For logged-in users, the bundle preselects candidate orders from `shop_order` with the
following generic defaults:

- active shop only
- active user only
- `canceled = 0`
- newest first
- maximum 25 candidates
- initial query window of 3 months

The final relevance check is always performed server-side on submit.

Persistence and mails
---------------------
On successful submit, the bundle:

1. creates a record in `shop_order_revocation`
2. links the new revocation record back to the order via `shop_order.shop_order_revocation_id`
3. sends the configured customer and shop-owner mails

If persistence or the order back-reference update fails, the submit is treated as failed and
no success message is shown.

The default mail placeholders are:

- `[{customerName}]`
- `[{customerMail}]`
- `[{ordernumber}]`
- `[{orderDate}]`
- `[{customerText}]`
- `[{revocationDate}]`
- `[{revocationTime}]`

Installation and integration
----------------------------
The bundle is part of `chameleon-system/chameleon-shop`; no separate Composer package is needed.

The bundle ships the core functionality, but the project still has to integrate it.

Recommended project setup steps:

1. Register the bundle if the project does not use automatic bundle discovery:

   ```php
   public function registerBundles()
   {
       $bundles = [
           // ...
           new ChameleonSystem\ShopRevocationBundle\ChameleonSystemShopRevocationBundle(),
       ];

       return $bundles;
   }
   ```

2. Add the bundle views to the relevant frontend theme snippet chain so the bundled Twig
   template can be resolved
   - `@ChameleonSystemShopRevocationBundle/Resources/views` 

3. Run the project’s CMS/database update process so the module, table, fields, and mail
   profiles are installed.

4. Configure the mail recipients in the mail profiles after installation:

   - `revocation_customer`
   - `revocation_shop_owner`

5. Create the project-side page and add the CMS module to it.

The bundle itself does not currently add its frontend views to a theme snippet chain automatically,
because that depends on the target project theme.

After the technical setup, the project still has to integrate the feature:

- create a reachable frontend page or route for the revocation form
- place the CMS module on that page
- expose the entry point, for example via a footer link
- adjust the revocation notice and any order-confirmation wording in the project context
- configure the mail recipients in the mail profiles `revocation_customer` and
  `revocation_shop_owner`

Extension points
----------------
The bundle is designed to be extended per project without changing the core implementation.

### Delivery date provider
Override the delivery date provider alias
`chameleon_system_shop_revocation.delivery_date.delivery_date_provider`
with a custom implementation of:

```php
interface DeliveryDateProviderInterface
{
    public function getDeliveryDate(\TShopOrder $order): ?\DateTimeInterface;
}
```

Returning `null` means that the provider cannot supply a usable relevance date for the order.

Use this extension point if a project can determine a better relevance date than the generic
fallback `shop_order.datecreated`, for example from ERP shipment data or a dedicated delivery
timestamp.

The provider is consumed by `ShopRevocationOrderRelevanceService::isOrderRelevantForRevocation()`.
That means:

- the provider does not need to know the 14-day rule itself
- the provider only has to return the correct business date for the order
- returning `null` causes the order to be treated as not relevant
- project code should prefer overriding this provider instead of replacing the full relevance service

### Additional days provider
Override the alias
`chameleon_system_shop_revocation.additional_days.revocation_additional_days_provider`
with a custom implementation of:

```php
interface RevocationAdditionalDaysProviderInterface
{
    public function getAdditionalDays(\TShopOrder $order): int;
}
```

Use this when the generic shop setting `shop.revocation_additional_days` is not sufficient.
For example, a project could compute additional days based on shop, order type, or fulfillment
rules.

The returned value is consumed by `ShopRevocationOrderRelevanceService::isOrderRelevantForRevocation()`
when calculating the final cutoff date.

### Relevance service
`ShopRevocationOrderRelevanceService` is the central class that decides whether an order is
currently revocable.

In most projects this service should not be replaced directly. The intended extension model is:

- keep the core relevance service
- override `DeliveryDateProviderInterface` if the date source must change
- override `RevocationAdditionalDaysProviderInterface` if the time window must change

Replace the full service only if the project needs different core semantics than:

- canceled orders are never relevant
- a missing relevance date means the order is not relevant
- the revocation window is `14 + x` days

### Order selection
`ShopRevocationOrderSelectionService::getSelectableOrders()` builds the logged-in dropdown list.
It uses `ShopRevocationOrderSelectionDataAccess` for the initial candidate query and then filters
those candidates again through the relevance service.

This service is the right customization point if a project needs different order-selection behavior,
for example:

- a different initial query window
- additional generic filters at query level
- a different label format for the dropdown
- a different candidate limit

There is no dedicated alias for this service at the moment, so changing its behavior means replacing
the service definition itself rather than just swapping a provider.

### Validation
`ShopRevocationValidationService::validate()` is the central server-side validation entry point.
It handles:

- required field checks
- email validation
- guest order lookup by order number
- logged-in order ownership checks
- email-to-order matching
- final relevance validation

Projects should normally keep this service unchanged and adapt the lower-level extension points
first, especially delivery date and additional days. Replace the validation service only if the
project needs fundamentally different validation semantics or additional business rules.

### Templates
The form template is block-based and can be overridden project-side if the visual integration
or wording needs to differ.

The default template is `Resources/views/snippets/modules/revocationForm/standard.html.twig`.
It is intentionally split into Twig blocks so projects can override small parts without copying
the whole template.

Typical override points are:

- `revocation_form_header`
- `revocation_form_messages`
- `revocation_form_invalid_order_hint`
- `revocation_form_order_select_field`
- `revocation_form_logged_in_order_number_fallback`
- `revocation_form_submit`

Use template overrides for presentation concerns. Do not move business validation into Twig.

### Frontend module
`ShopRevocationModule` is the frontend entry point that prepares template data and handles the
submit flow in `submitRevocation()`.

This class coordinates:

- logged-in prefill of customer data
- loading of selectable orders
- form-data mapping from POST
- validation
- persistence
- order back-reference linking
- mail dispatch

Projects should usually leave the module logic unchanged and extend the underlying services or
the Twig template instead. Replacing the module is only useful if the project needs a materially
different frontend flow.

### Mail extensions
`ShopRevocationMailService` supports optional attachment arrays for customer and shop-owner
mails. Projects can replace or extend the service if they need custom recipient logic,
attachments, or additional mail data.

The service entry point is:

```php
public function sendRevocationMails(
    RevocationMailDataModel $mailData,
    array $customerAttachments = [],
    array $shopOwnerAttachments = []
): void
```

It also exposes a few protected methods that are useful in subclasses:

- `sendCustomerConfirmation()`
- `sendShopOwnerNotification()`
- `populateMailProfile()`
- `getMailProfile()`
- `sendMailProfile()`

Typical reasons to subclass or replace `ShopRevocationMailService` are:

- deriving recipients dynamically instead of using static mail profile recipients
- attaching return labels, PDFs, or project-specific documents
- adding custom placeholders or additional mail data
- changing how customer and shop-owner notifications are split or formatted

If additional placeholders are needed, extend `RevocationMailDataModel` usage in the calling flow
and adjust `populateMailProfile()` or the mail data source accordingly.

Scope boundaries
----------------
The bundle deliberately does not decide project-specific concerns such as:

- where the revocation page is routed
- where the footer link is placed
- how the legal text is embedded in the revocation notice
- which recipient addresses are configured in productive systems
- how a project determines a real delivery date from ERP or shipping systems

License
-------
This bundle is released under the same license as the Chameleon System.
