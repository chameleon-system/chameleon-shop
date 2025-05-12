Chameleon System ShopAffiliateBundle
====================================

Overview
--------
The ShopAffiliateBundle provides affiliate tracking support for the Chameleon Shop. It detects affiliate program codes from incoming requests or cookies, persists them in the user’s session/cookie, and injects tracking parameters into links and order confirmation pages.

Key Features
------------
- Automatic affiliate code detection: scans URL parameters and cookies for configured affiliate codes.
- Session and cookie persistence: stores the affiliate code in session and, optionally, as a cookie with configurable validity.
- Affiliate program configuration: manage multiple affiliate programs via CMS tables (`pkg_shop_affiliate` and `pkg_shop_affiliate_parameter`).
- Tracking parameter injection: retrieve parameter collections to append to outbound URLs for tracking referrals.
- Order success integration: embed custom tracking/confirmation code on the order success page.
- Pluggable program handlers: support for custom affiliate program classes under `objects/db/TPkgShopAffiliatePrograms`.

Installation
------------
This bundle is included in the `chameleon-system/chameleon-shop` package.
No additional Composer installation is required.
To register manually (without Symfony Flex), add to `app/AppKernel.php`:
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\ShopAffiliateBundle\ChameleonSystemShopAffiliateBundle(),
    ];
    return $bundles;
}
```
Clear the cache:
```bash
php bin/console cache:clear
```

Usage
-----
1. **Configure affiliate programs**
   - In the CMS table **Affiliate Programs** (`pkg_shop_affiliate`), create one or more entries:
     - **Name**: human-readable identifier (e.g., "TradeDoubler").
     - **URL Parameter Name**: query parameter to read the affiliate code (e.g., `tduid`).
     - **Validity**: number of seconds the code remains valid in a cookie.
     - **Order Success Code**: HTML/JS snippet to inject on the order confirmation page.
   - In **Affiliate Program Parameters** (`pkg_shop_affiliate_parameter`), define key/value pairs to append to outgoing links.

2. **Affiliate code detection**
   The system automatically calls `TdbPkgShopAffiliate::ScanURLForAffiliateProgramCodes()` on each page load to detect and store codes.

3. **Retrieve active affiliate**
   To get the current affiliate program and the affiliate code stored in session:
   ```php
   use TdbPkgShopAffiliate;

   $affiliate = TdbPkgShopAffiliate::GetActiveInstance();
   if (null !== $affiliate) {
       $sessionKey = TdbPkgShopAffiliate::SESSION_AFFILIATE_PROGRAM_CODE;
       $data = $_SESSION[$sessionKey] ?? [];
       $code = $data['sCode'] ?? null;
       // $affiliate contains the program metadata
   }
   ```

4. **Inject tracking parameters**
   Before generating shop URLs, retrieve and merge the affiliate parameters:
   ```php
   $params = [];
   if ($affiliate) {
       foreach ($affiliate->GetFieldPkgShopAffiliateParameterList() as $param) {
           $params[$param->fieldName] = $param->fieldValue;
       }
   }
   $url = $router->generate('shop_article_detail', $routeParams + $params);
   ```

5. **Order confirmation snippet**
   On your order success page template, render the configured snippet:
   ```twig
   {% if affiliate = TdbPkgShopAffiliate::GetActiveInstance() %}
     {{ affiliate.fieldOrderSuccessCode|raw }}
   {% endif %}
   ```

Configuration
-------------
No additional bundle-specific configuration is required. Affiliate programs are managed via CMS tables. Doctrine mappings are auto-loaded from `Resources/config/doctrine`.

Extensibility
-------------
- **Custom program handlers**: add new classes under `objects/db/TPkgShopAffiliatePrograms` and set the **Class** and **Class subtype** fields in the CMS to use them.
- **Override scan logic**: subclass `TdbPkgShopAffiliate` and override `ScanURLForAffiliateProgramCodes()` or `FoundCodeHook()` for custom detection or event dispatch.
- **Custom parameter storage**: implement new parameter collection logic by extending `PkgShopAffiliateParameter` or its list.

License
-------
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.
