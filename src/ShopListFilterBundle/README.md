Chameleon System ShopListFilterBundle
====================================

Overview
--------
The ShopListFilterBundle adds dynamic filtering to existing ArticleList modules. It supplies a filter UI (facets, sliders, checkboxes) that can post-filter results after initial list generation, enabling faceted search across product listings.

Features
--------
- Faceted, post-search filtering for product lists
- Support for multiple filter types: checkbox, range slider, etc. (via FilterInterface implementations)
- Decoupled Filter API service for managing query, state, cache, and result factory
- Tags into the ArticleList module via result modifiers and state extractors
- Easy integration: simply add the filter WebModule spot and configure your list

Installation
------------
This bundle is included by default in the Chameleon Shop installation. To enable manually, register in your kernel or bundles.php:
```php
new ChameleonSystem\\ShopListFilterBundle\\ChameleonSystemShopListFilterBundle(),
```

Configuration
-------------
No special configuration parameters are needed. All services are registered in `Resources/config/services.xml`. You can override or decorate:
- `chameleon_system_shop_list_filter.filter_api` (implements `FilterApiInterface`)
- `chameleon_system_shop_list_filter.result_modification` (tagged `chameleon_system_shop.article_list_module.result_modification`)
- `chameleon_system_shop_list_filter.state_extractor` (tagged `chameleon_system_shop.article_list_module.state_extractor`)

Usage / How-To
--------------
1. **Add Filter UI**: Insert the filter WebModule spot on your page template with.
2. **Configure ArticleList**: Place an ArticleList module instance on the same page and mark it as "post-filterable" in its module settings. Ensure it's the first such list.
3. **Render Filterable List**: Use the view `TShopModuleArticlelistFilterSearchFallbackAll` (or similar) so the list falls back to all items and is then filtered.
4. **Populate Filter State**: The Bundle captures URL/state parameters (keys starting with `aPkgShopListfilter`) via the state extractor, and passes them into the filter API.
5. **Customize Facets**: Implement `pkgshoplistfilter\Interfaces\FilterInterface` on your filter model to supply facets and their renderable view data.

#### Example: Prefiltered Landing Page
```yaml
# In your page module configuration
Filter-Parameter: "aPkgShopListfilter[0][field]=brand&aPkgShopListfilter[0][value]=nike"
```
Results will initially show only Nike products, and the filter UI will exclude the brand facet.

API Reference
-------------
**FilterApiInterface** (`chameleon_system_shop_list_filter.filter_api`):
- `getArticleListQuery(): string` – SQL/parameterized query for the list
- `getArticleListFilterRelevantState(): array` – subset of state to consider
- `allowCache(): bool` – should the results be cached?
- `getCacheParameter(): array` – cache key components
- `getCacheTrigger(): array` – DB triggers to invalidate cache
- `getResultFactory(): ResultFactoryInterface` – underlying result factory service
- `getListConfiguration(): ConfigurationInterface` – module configuration object
- `getArticleListState(): StateInterface` – current list state (page, sort, filter values)

**FilterInterface** (implement on custom filter models):
- `getFacets(): array` – return an array of facet definitions (e.g. categories, price ranges)

Services
--------
- **chameleon_system_shop_list_filter.db_adapter** – low-level DB adapter for filter data
- **chameleon_system_shop_list_filter.filter_api** – main API service for filter operations
- **chameleon_system_shop_list_filter.result_modification** – integrates into ArticleList to apply filtering post-render
- **chameleon_system_shop_list_filter.state_extractor** – reads filter parameters from request into list state

Customization
-------------
- Override or extend `FilterApiInterface` to change how filters are applied or state is managed.
- Implement `FilterInterface` on new filter models to support custom facet logic.
- Decorate the result modification or state extractor services for advanced behaviors.

License
-------
Licensed under the MIT License. See the `LICENSE` file at the project root.

How-Tos
-------

### Searchable, pre-filtered List with Post-Search-Filter

Goal: Create a landingpage for products prefiltert (to show only products from a specific brand for example) that can be searched and filtert

* create the search filter you want to use
* Create a new Page with at least 2 spots
* Fill one spot with the Product Listfilter
* Fill the other spot with an article list using the filter TShopModuleArticlelistFilterSearchFallbackAll
* open the page in a browser - you should see the filter and all products
* use the filter to restrict the list to what you want to have preselected
* copy all URL Parameters that start with aPkgShopListfilter and past them into the filter config (Filter-Parameter) in your page
* when you reload the page without any parameters you should
    * see only articles matching your restriction
    * the filter list is reduced to filters that are not part of your prefilter (if this is not the case, make sure the view that renders the filter list contains

```php
while ($oFilterItem = $oFilterListItems->Next()) {

    // check if the filter is part of a static filter and exclude if that is the case
    if ($oListfilter->isStaticFilter($oFilterItem)) {
        continue;
    }
    echo $oFilterItem->Render($oFilterItem->fieldView, $oFilterItem->fieldViewClassType);
}
```
* if you want the list searchable, you must add a search form like the one used for the global search - but with your new page being the target page