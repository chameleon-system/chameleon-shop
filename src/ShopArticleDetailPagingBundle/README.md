# Chameleon System ShopArticleDetailPagingBundle
===============================================

Overview
--------
The ShopArticleDetailPagingBundle adds convenient paging controls to shop article detail pages. It provides
"previous", "next", and "back to list" links based on an article list module's state, without requiring session data.

Key Features
------------
- Previous and next article navigation links
- Back-to-list link preserving original list context
- Stateless and cache-friendly: no session storage needed
- Detects list state via URL parameters (`_ref`, `url`)
- Integrates with shop article list JSON API (`getAsJson`)
- Easily extensible via service overrides

Installation
------------
This bundle is included in the `chameleon-system/chameleon-shop` package.
To register manually (without Symfony Flex), add to `app/AppKernel.php`:
```php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\ShopArticleDetailPagingBundle\ChameleonSystemShopArticleDetailPagingBundle(),
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

The module provides the following links:

- previous article
- next article
- back to list

It uses the referrer to determine the calling list. There are two assumptions it makes about the referrer which it needs
to operate:

1. The link contains the spot name which holds the list on the calling page in the parameter "ref".
2. The module in the spot provided implements the method "getProducts" which returns the items matching the state passed
   via URL plus the next and previous page URL in the following form.

```markdown
array('next'=>#,'previous'=>#,items =>array("id1"=>URL, "id2"=>URL...))
```

This approach was chosen because

- it allows the list and detail pages to cache because

    - it has no need to hold any data in the user's session
    - it only adds the spot to the detail URLs (but cache is spot-specific anyway)

- responsibilities stay where they belong

    - the list knows how to return items for a given state
    - the detail pager knows how to provide the state and what to do with the response relative to the current detail page

**IMPORTANT:** We do assume that the referrer holds the correct state of the item. When the list is paged (or filtered) via
Ajax, it must be taken care to ensure that the referrer is set correctly. This however should be addressed anyway as the
browser back button would otherwise reset the list. Solutions to this problem exist (e.g. [https://github.com/browserstate/history.js](https://github.com/browserstate/history.js)).

Once the next/back links are created, the service will pass the list's state URL as a parameter to the module - the
referrer is used only to render the correct links on the first call of the paging module.

Configuration
-------------
No additional configuration is required. The module reads parameters (`_ref`, `url`) and invokes the list module's
JSON API via services defined in `Resources/config/services.xml`.

Extensibility
-------------
- Override `DetailPagingService`, `ArticleListApi`, or any supporting service by redefining service IDs.  
- Implement `ContentFromUrlLoaderServiceInterface` to customize how the list JSON is fetched.  
- Subclass `RequestToListUrlConverterInterface` to change the parameter names or parsing logic.

License
-------
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.