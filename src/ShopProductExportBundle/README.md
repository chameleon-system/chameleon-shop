Chameleon System ShopProductExportBundle
=========================================

Overview
--------
The ShopProductExportBundle provides a flexible product export framework. You can define export formats (CSV, XML, or custom handlers), cache export output, and serve exports via a CMS module spot.

Features
--------
- Register multiple export handlers implementing `ShopProductExportHandlerInterface` (CSV, XML included)
- Central `ShopProductExporter` service implementing `ShopProductExporterInterface` to coordinate exports
- HTTP-accessible export module (`ShopProductExportModule`) with optional cache resetting
- Cache export results to filesystem for performance; invalidate via URL parameter `reset=1`
- Integration with ArticleList result factory and ShopService for filtering and context
- Installer scripts to deploy view templates and assets for frontend snippets

Installation
------------
1. **Bundle Registration** (if not already):
   ```php
   new ChameleonSystem\\ShopProductExportBundle\\ChameleonSystemShopProductExportBundle(),
   ```
2. **Run Updates** 
3. Copy `installation/tocopy/framework` core snippets to the project folder).
3. **Clear Cache**: Flush Chameleon cache.

Handler Registration
--------------------
Default CSV and XML handlers are auto-registered in `Resources/config/services.xml`:

```xml
<service id="chameleon_system_shop_product_export.export_handler.csv" class="TPkgShopProductExportCSV">
  <tag name="chameleon_system_shop_product_export.export_handler" alias="csv" />
</service>
<service id="chameleon_system_shop_product_export.export_handler.xml" class="TPkgShopProductExportXML">
  <tag name="chameleon_system_shop_product_export.export_handler" alias="xml" />
</service>
```

To add a custom handler:
```xml
<service id="my_export.handler" class="App\\Export\\MyExportHandler">
  <tag name="chameleon_system_shop_product_export.export_handler" alias="myformat" />
</service>
```

Usage
-----
Place the export module in a CMS layout spot.

Request an export URL:
```
/your-export-page/(sModuleSpotName)/productExport/view/{alias}/key/{exportKey}.txt?reset=1
```
Parameters:
- `{alias}`: registered handler alias (e.g. `csv` or `xml`)
- `{exportKey}`: secret key from shop configuration (`shop.export_key`)
- `reset=1`: optional flag to force re-export and cache refresh

API Reference
-------------
**ShopProductExporterInterface** (`chameleon_system_shop_product_export.exporter`)
- `isValidExportKey(string $exportKey): bool`
- `aliasExists(string $alias): bool`
- `registerHandler(string $alias, ShopProductExportHandlerInterface $handler): void`
- `export(ConfigurationInterface $config, string $alias): string`

**ShopProductExportHandlerInterface**
- `Init(): void` – perform initialization before export
- `SetArticleList(TIterator $articles): void` – provide list of articles to export
- `SetDebug(bool $debug): void` – enable debug output
- `Run(): bool` – execute export, output data via buffer

**Modules**
- `ShopProductExportModule` (service id `chameleon_system_shop_product_export.module.exporter`) – integrates into CMS spots, handles caching and datasource

Configuration
-------------
Adjust base cache directory via parameter `%chameleon_system_product_export.base_export_cache_dir%` (default `CMS_TMP_DIR`).

Customization
-------------
- Override the default handlers or add new handlers via service tags.
- Extend `ShopProductExporter` or implement `ShopServiceInterface` to customize export key retrieval.

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.