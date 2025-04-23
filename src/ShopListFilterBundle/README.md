Chameleon System ShopListFilterBundle
=====================================

Provides result modifier and state extractor services to provide filtering for an existing article list (the first one marked in its configuration, that it can be post filtered).

Uses an API service (FilterApiInterface) to communicate the state between the list and the filter module.

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