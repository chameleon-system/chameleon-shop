# Chameleon System SearchBundle
=============================

Overview
--------
The SearchBundle provides shop product search functionality integration for the Chameleon Shop. It handles search request extraction, result mapping, session tracking, logging, and search indexing status retrieval.

Key Features
------------
- Search request mapping: maps search query and filters to the view using `SearchRequestMapper`.
- Session tracking: tracks user searches to prevent duplicate logging.
- Search logging: logs search queries and result counts to the database (`shop_search_log`).
- Search indexing status: exposes `ShopSearchStatusService` to retrieve current indexer status (started, completed, total rows processed).
- Event-driven: integrates with shop article list events to trigger logging and CMS observer redirects.
- Spell-check suggestion: support for suggestion rendering when available.
- Monolog integration: prepends `search_indexer` channel to Monolog configuration.

Installation
------------
This bundle is included in the `chameleon-system/chameleon-shop` package.
No additional Composer installation is needed.
To register manually (without Symfony Flex), add to `app/AppKernel.php`:
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\SearchBundle\ChameleonSystemSearchBundle(),
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
1. **Search request extraction**  
   The `StateRequestExtractor` automatically extracts the search query (`q`) and filter parameters (`lf`) from the user request and provides it to the article list state.
2. **View mapping**  
   Use the `SearchRequestMapper` in your article list module to map search data (e.g., original query, processed query, spell-check suggestions) to your Twig templates.
3. **Logging searches**  
   Enable **Use Shop Search Log** in your shop settings (`fieldUseShopSearchLog`). On the first execution of a search, `SearchResultLoggerListener` logs the query and result count to `TdbShopSearchLog`. Subsequent identical searches in the same session are ignored.
4. **Search indexing status**  
   Inject `ChameleonSystem\SearchBundle\Bridge\ShopSearchStatusService` into your services/controllers to retrieve a `ShopSearchStatusDataModel` containing indexing start time, completion time, and total processed rows:
   ```php
   public function statusAction(ShopSearchStatusService $statusService)
   {
       $status = $statusService->getSearchStatus();
       // Access $status->getStarted(), getCompleted(), getTotalRowsProcessed()
   }
   ```
5. **No-results redirect**  
   If configured (`fieldRedirectToNotFoundPageProductSearchOnNoResults`), `TPkgSearchObserver` will redirect to the "Not Found" page when a search yields zero results.

Configuration
-------------
This bundle has no additional bundle-specific configuration.
A `search_indexer` Monolog channel is automatically prepended. You can configure handlers for this channel in your `config/packages/monolog.yaml`:
```yaml
monolog:
  channels: ['search_indexer']
  handlers:
    search_indexer:
      type: stream
      path: '%kernel.logs_dir%/%kernel.environment%.search_indexer.log'
      level: info
```

Extensibility
-------------
- **Custom session logger**: override the `chameleon_system_search.session` service to implement `ShopSearchSessionInterface` for custom session storage.
- **Custom search logger**: override the `chameleon_system_search.logger` service to implement `ShopSearchLoggerInterface` for custom logging (e.g., external analytics).
- **Extend mappers**: create a subclass of `SearchRequestMapper` or `StateRequestExtractor` and tag it as a mapper to adjust mapping logic.
- **Override observer behavior**: extend `TPkgSearchObserver` to change no-result redirect logic via CMS events.

License
-------
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.
