Chameleon System ShopRatingServiceBundle
========================================

Overview
--------
The ShopRatingServiceBundle provides an extensible framework for collecting, sending, and displaying shop rating data from external providers (e.g., Trusted Shops, eKomi, Idealo, Shopauskunft). It supports automated email invitations after order completion, scheduled imports of ratings, storage of rating history, and frontend widgets to showcase ratings and reviews.

Features
--------
- Configure multiple rating service integrations via CMS records (`pkg_shop_rating_service`).
- Automated email invitation cronjob for completed orders.
- Import ratings from provider APIs on schedule (import cronjob).
- Persist rating history (`pkg_shop_rating_service_rating`) and aggregate main scores.
- Frontend modules:
  - **RatingServiceWidget**: display the current score and widget for a specific service.
  - **RatingTeaser**: show a random review teaser with provider logo and link.
  - **RatingList**: list all imported reviews with sorting and pagination.
- Fully cacheable with triggers for portal, page, category, and configuration changes.

Installation
------------
1. This bundle is included by default. To register manually, add to your AppKernel or bundles.php:
    ```php
    new ChameleonSystem\\ShopRatingServiceBundle\\ChameleonSystemShopRatingServiceBundle(),
    ```
2. Activate the email invitation cronjob:
   - Locate **"Versand der Rating-Email-Aufforderungen"** in the CMS cronjob list and enable it.
   - If EKomi is the sole provider, you may skip this step as EKomi handles invitations automatically.
3. (Optional) Activate the import cronjob:
   - Enable the **Import Ratings** cronjob to fetch ratings from external services.
4. Implement logic to flag orders as shipped in the `pkg_shop_rating_service_order_completely_shipped` field once fully shipped.
   Invitations are only sent for orders with this timestamp set, and only once per order unless configured otherwise.

Configuration
-------------
- In the CMS, go to **Shop » Rating Service** and add entries for each provider:
  - **System Name**: unique key (e.g., `ekomi`, `trustedshops`).
  - **Rating API ID / Credentials**: configure `fieldRatingApiId`, `fieldAffiliateValue`, etc.
  - **URLs**: set `fieldRatingUrl` and `fieldRatingUserinfoUrl` for direct links.
- Override data access implementations by redefining services in `Resources/config/services.xml`:
  - `chameleon_system_shop_rating_service.data_access.trusted_shops` for Trusted Shops API
  - `chameleon_system_shop_rating_service.mapper.ekomi` for eKomi view mapper

Usage
-----
**Send Invitations & Import Ratings**
- Ensure cronjobs are active and configured as above.
- The `TPkgShopRating_CronJob_SendRatingMails` cronjob sends emails based on shop configuration (`Shopreviewmail*` fields).
- The `TPkgShopRating_CronJob_ImportRating` cronjob imports new ratings into `pkg_shop_rating_service_rating`.

**Frontend Modules**
Add these modules to your page layout or Twig templates:
- RatingServiceWidge
- RatingTeaser
- RatingList

API Reference
-------------
```php
// Retrieve configured service by system name
TdbPkgShopRatingService::GetInstanceFromSystemName(string $systemName): ?TdbPkgShopRatingService

// Render a service widget directly
$service = TdbPkgShopRatingService::GetInstanceFromSystemName('ekomi');
echo $service->Render();

// Import ratings manually
$service->Import();

// Send a single invitation email
$service->SendShopRatingEmail(TdbDataExtranetUser $user, array $orderData): bool;

// Get direct links
$service->GetLinkRating(): string;
$service->GetLinkInfoPage(): string;
```

License
-------
This bundle is released under the MIT License. See the main `LICENSE` file for details.

