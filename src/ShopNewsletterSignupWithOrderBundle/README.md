# Chameleon System ShopNewsletterSignupWithOrderBundle

Overview
--------
The ShopNewsletterSignupWithOrderBundle adds an optional newsletter signup step to the order confirmation process. During checkout, customers can opt in to receive newsletters—and the bundle handles persisting their choice and subscribing them in the `pkg_newsletter_user` table.

Features
--------
- Inject a "Sign up for newsletter" checkbox on the order confirmation step
- Persist the opt-in choice in the basket and order record (`newsletter_signup` flag)
- Automatically create or update subscriber entries in `pkg_newsletter_user`
- Avoid duplicate subscriptions for existing opt-ins
- Prevent signup when orders are canceled

Installation
------------
This bundle is included by default. To register manually:
```php
new ChameleonSystem\\ShopNewsletterSignupWithOrderBundle\\ChameleonSystemShopNewsletterSignupWithOrderBundle(),
```

Setup
-----
Add the following lines in the order confirm view to show the newsletter checkbox:

```php
<?php if ($bShowNewsletterSignup) { ?>
    <div class="newsletter">
        <label><input type="checkbox" value="1" <?php if ($newsletter) echo 'checked="checked"';?> name="aInput[newsletter]" /> <?=\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_newsletter.form.subscribe_to_all')?></label>
    </div>
<?php } ?>
```

API Reference
-------------
**TPkgShopNewsletterSignupWithOrder_TShopStepConfirm**
- `addDataToBasket(TShopBasket $oBasket): void` – capture checkbox input
- `GetAdditionalViewVariables(string $viewName, string $viewType): array` – exposes `$bShowNewsletterSignup` and `$newsletter` flags to views

**TPkgShopNewsletterSignupWithOrder_TShopOrder**
- `LoadFromBasketPostProcessData(TShopBasket $oBasket, array &$aOrderData): void` – add `newsletter_signup` to order data
- `CreateOrderInDatabaseCompleteHook(): void` – subscribe user on order completion

Newsletter Subscription Logic
------------------------------
- On order completion, if `newsletter_signup` is true:
  * Check for existing `TdbPkgNewsletterUser` by `data_extranet_user_id` or email
  * If none exists, create with `optin = 1` and `optincode = 'signup-via-order-confirm-page'`
  * If exists but unsubscribed, update `optin` and `optin_date`

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.