Chameleon System ShopDhlPackstationBundle
=========================================

Overview
--------
The ShopDhlPackstationBundle extends the Chameleon Shop to support DHL Packstation shipping addresses. It integrates with the extranet address book and checkout flow to let customers
mark and use Packstation locations as valid shipping addresses.

Features
--------
- Add "Packstation" flag to customer address forms (extranet)
- Clear / hide inappropriate address fields when selecting a Packstation
- Prevent using a Packstation as billing address
- Persist Packstation selection on user profile and session
- Inject Packstation flag into ShopOrder records (`adr_shipping_is_dhl_packstation`)
- Restrict payment methods not compatible with Packstation delivery
- Checkout step validation for Packstation addresses with clear error messaging

Installation
------------
This bundle is included by default. To activate manually, register in your kernel or bundles.php:
```php
new ChameleonSystem\\ShopDhlPackstationBundle\\ChameleonSystemShopDhlPackstationBundle(),
```

Extranet Integration
--------------------
Use the provided WebModule spot `PkgShopDhlPackstation` on your customer address pages to
render the DHL Packstation selection form.

This module uses `MTExtranet_PkgShopDhlPackstation` to handle address updates, field visibility,
and validation (via flash messages: `PkgShopDhlPackstation-ERROR-SHIPPING-IS-BILLING-MAY-NOT-BE-PACKSTATION`).

Address Model Extensions
------------------------
- `TPkgShopDhlPackstation_DataExtranetUser`: handles `UpdateShippingAddress()`, `fillPackStationFieldValue()`, `DHLPackStationStatusChanged()` and billing address enforcement.
- `TPkgShopDhlPackstation_DataExtranetUserAddress`: adds `SetIsDhlPackstation(bool)` to clear irrelevant fields and overrides `GetRequiredFields()` when Packstation is selected.

Form Mapping
------------
- `TPkgShopDhlPackstation_TPkgExtranetMapper_AddressForm`: maps the `is_dhl_packstation` field into the address form view.

Shop / Checkout Integration
---------------------------
- `TPkgShopDhlPackstation_ShopOrder`: populates the `adr_shipping_is_dhl_packstation` order property.
- `TPkgShopDhlPackstation_TShopPaymentMethod`: disables payment methods (`fieldPkgDhlPackstationAllowForPackstation`) when shipping to a Packstation.
- `TPkgShopDhlPackstation_ShopStepUserDataV2`: checkout step mapper enforcing Packstation rules and error messages.

API Reference
-------------
- TPkgShopDhlPackstation_DataExtranetUser::SetAddressAsBillingAddress(string $addressId)
- TPkgShopDhlPackstation_DataExtranetUser::UpdateShippingAddress(array $addressData)
- TPkgShopDhlPackstation_DataExtranetUserAddress::SetIsDhlPackstation(bool $flag, bool $save=true)
- TPkgShopDhlPackstation_TShopPaymentMethod::IsAllowedForPackstation(): bool

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.
