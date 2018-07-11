Chameleon System ShopArticleDetailPagingBundle
==============================================

The module provides the following links:

- previous article
- next article
- back to list

It uses the referrer to determine the calling list. There are two assumptions it makes about the referrer which it needs
to operate:

# The link contains the spot name which holds the list on the calling page in the parameter "ref".
# The module in the spot provided implements the method "getProducts" which returns the items matching the state passed
  via URL plus the next and previous page URL in the following form.

```
array('next'=>#,'previous'=>#,items =>array("id1"=>URL, "id2"=>URL...))
```

This approach was chosen because

* it allows the list and detail pages to cache because

   * it has no need to hold any data in the user's session
   * it only adds the spot to the detail URLs (but cache is spot-specific anyway)

* responsibilities stay where they belong

   * the list knows how to return items for a given state
   * the detail pager knows how to provide the state and what to do with the response relative to the current detail page

IMPORTANT: We do assume that the referrer holds the correct state of the item. When the list is paged (or filtered) via
Ajax, it must be taken care to ensure that the referrer is set correctly. This however should be addressed anyway as the
browser back button would otherwise reset the list. Solutions to this problem exist (e.g. https://github.com/browserstate/history.js).

Once the next/back links are created, the service will pass the list's state URL as a parameter to the module - the
referrer is used only to render the correct links on the first call of the paging module.
