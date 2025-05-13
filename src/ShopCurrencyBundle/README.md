# Chameleon System ShopCurrencyBundle

Overview
--------
The ShopCurrencyBundle provides currency management for the Chameleon Shop. It offers services and helpers to retrieve, format, and switch the active currency per user/session, persist selection across requests, and integrate currency-aware logic into shop modules and views.

Features
--------
- Retrieve active currency symbol, ISO code, and full currency object
- Format numeric values according to the active currency locale and symbol
- Persist active currency via session, cookie, and user profile
- Request-level cache decorator for improved performance
- CMS action plugin to change currency at runtime (`ChangeCurrency`)
- Request state provider for finite state management and URL generation
- Navigation mapper to build currency selection menus in site navigation
- Bridge extension for basket/payment modules to reload payment methods on currency change
- Static helpers for currency conversion and formatting: `Convert`, `ConvertToActiveCurrency`

Installation
------------
This bundle is included by default in the Chameleon System Shop installation. To register manually in your application kernel:
```php
// in AppKernel::registerBundles() or bundles.php
new ChameleonSystem\\ShopCurrencyBundle\\ChameleonSystemShopCurrencyBundle(),
```

Configuration
-------------
No additional configuration parameters are required. All services are defined in `Resources/config/services.xml`. You may decorate or override services if needed.

Usage
-----
### Accessing the active currency
```php
// fetch via DI or service locator
$currencyService = $container->get('chameleon_system_shop_currency.shop_currency');
$symbol = $currencyService->getSymbol();       // e.g. "€"
$code   = $currencyService->getIso4217Code(); // e.g. "EUR"
$formatted = $currencyService->formatNumber(1234.56); // e.g. "1,234.56 €"
```

### Changing the currency
Place a CMS action link (via `pkgCurrency` plugin) or integrate a currency selector in navigation.

API Reference
-------------
**Service**: `chameleon_system_shop_currency.shop_currency` (implements `ShopCurrencyServiceInterface`)
- `getSymbol(): string` - Currency display symbol
- `getIso4217Code(): string` - ISO-4217 currency code
- `formatNumber(float $value): string` - Format value with currency symbol
- `getActiveCurrencyId(bool $useDefaultIfNotDefined): ?string` - Active currency record ID
- `getObject(): ?TdbPkgShopCurrency` - Active currency record
- `reset(): void` - Clear request-level cache

**Model**: `TdbPkgShopCurrency`
- `GetFormattedCurrency(float $value): string` - Format value with symbol
- `getISO4217Code(): string`
- `GetCurrencyDisplaySymbol(): string`
- `SetAsActive(): void` - Persist this currency selection
- `Convert(float $value, TdbPkgShopCurrency $base = null): float` - Convert value from base to this currency
- `ConvertToActiveCurrency(float $value): float` - Shortcut to convert from base into active currency

**Action Plugin**: `TPkgShopCurrency_PkgCmsActionPlugin::ChangeCurrency(array $data, bool $redirect)`

**Mapper**: `TPkgShopCurrencyMapper` (tagged `chameleon_system.mapper`) maps `oCurrency` into view vars `sCurrencyName` and `sCurrency`.

**Request State Provider**: `CurrencyRequestStateProvider` (tagged `chameleon_system_core.request_state_element_provider`) delivers `sActivePkgShopCurrencyId` state.

**Navigation Mapper**: `TPkgCmsNavigation_CurrencySelection` builds a currency dropdown/tree for site navigation.

Available Services
------------------
- chameleon_system_shop_currency.shop_currency
- chameleon_system_shop_currency.shop_currency_request_level_cache_decorator
- chameleon_system_shop_currency.mapper.shop_currency_mapper
- chameleon_system_shop_currency.currency_request_state_provider
- (and related bridge, CMS plugin, and navigation mapper services)

Events
------
The bundle dispatches the `CURRENCY_CHANGED` event (`chameleon_system_shop_currency.currency_changed_event`) when the active currency is updated.

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root for details.