=======================
Shop Module ArticleList
=======================


Description
===========

The module shows a product selection based on a per instance configurable filter definition. The Result is pageable and sortable.

View logic (and ajax paging)
============================
For ajax paging we need to get the articles without the module content. To support this, we introduced a method that renders just that part (it includes caching).
It will search for mappers using the view name under the mapper chain configuration of the module. The mapper configuration itself
for the views should be reduced to mappers that prepare data relevant for the list surrounding the article list.

The view used for the article list is currently hardcoded in the module class as a mapping from view name to article list snippet name. This will need to change.

Please note, that you need to map the template name used for the module to the view you want to use for the list of products rendered
within the list. You can do that by setting chameleon_system_shop.article_list.view_to_list_view_mapping in your config.yml.

Example

.. configuration-block::

    .. code-block:: yaml
        :linenos:

        parameters:
          chameleon_system_shop.article_list.view_to_list_view_mapping:
            rightNoticeList:           "/common/lists/listStandardShopArticle.html.twig"
            full:                      "/common/lists/listExtendedShopArticle.html.twig"
            standardEmptyOnNoArticles: "/common/lists/listScrollShopArticle.html.twig"
            standard:                  "/common/lists/listScrollShopArticle.html.twig"


Extending the module
====================
The module can be extended by providing state request extractors and result modifications.


Changing state variables
------------------------
If you want to change the behaviour of the list, you tend to need two things:

a) a way to add additional information into the lists state
b) a way to modify the lists results based on the lists state

Extracting state data from the request and adding it into the state can be accomplished via state request extractors and state elements.
Modifying the result set based on the state can be achieved using result modification services.

state request extractor
-----------------------

The state request extractor takes the request data sent to the module (any post/get data sent to spotName).

Example: spotName[foo]=bar would sent foo=bar to the extractor

You should register a new request extractor whenever any of the request data sent to the module should affect the modules state.

An example would be if you want to provide the user with the ability to select a page size. This would require a new state parameter which
would need to be injected into the state.

State request extractors must implement the StateRequestExtractorInterface and must be tagged with chameleon_system_shop.article_list_module.state_extractor.

state element
-------------
A state element implements StateElementInterface and must be tagged with chameleon_system_shop.article_list_module.state_element. Every state element
defines under what key the state element will be available in the state, a method to validate the input, and a method to normalize incoming data


result modifications
--------------------

If you want to change the result of the list you should provide a result modification service that extends ResultModificationInterface
tag the Service with chameleon_system_shop.result_modifier.

page size
---------
all allowed page sizes must be configured via chameleon_system_shop.state_factory.state_element_valid_page_sizes. Example (for a config.yml):

.. configuration-block::

    .. code-block:: yaml
        :linenos:

        parameters:
          chameleon_system_shop.state_factory.state_element_valid_page_sizes:
                - 5
                - 10
                - 15

Example
-------

You can check the pkgshoplistfilter bundle as an example where the lists state is extended and the result are modified based on this.

summary
-------
+------------------------+--------------------------------+-----------------------------------------------------------+
|type                    | implements                     | auto registered via tag                                   |
+------------------------+--------------------------------+-----------------------------------------------------------+
|State request extractor | StateRequestExtractorInterface | chameleon_system_shop.article_list_module.state_extractor |
+------------------------+--------------------------------+-----------------------------------------------------------+
|State element           | StateElementInterface          | chameleon_system_shop.article_list_module.state_element   |
+------------------------+--------------------------------+-----------------------------------------------------------+
|Result modification     | ResultModificationInterface    | chameleon_system_shop.result_modifier                     |
+------------------------+--------------------------------+-----------------------------------------------------------+

Twig Variables
--------------
- items - array with TdbShopArticle holding all products for the page to be displayed
- itemsMappedData array with the mapped data for every item. contents of each item depends on the mapper used
- results - ChameleonSystem\ShopBundle\objects\ArticleList\ResultData holds the result data. is made available to be processed by other mappers
- listPagerUrl - url string that can be used to generate the url for a specific page. Replace the _pageNumber_ with the page you would like to open
- listPageSizeChangeUrl - url string that can be used to generate a url that will switch to a different page size. Replace _pageSize_ with the page size you would like to change to.
- numberOfPages - total number of pages
- state - array with state values (includes default values). relevant keys are p (current page), s (sort id), ps (page size)
- stateObject - the original state object. you should not use it in your views - use the state array instead
- listTitle
- description_start
- description_end
- shop the shop object
- currency current currency object
- local active local object
- sModuleSpotName
- listConfiguration list configuration object
- activeSortId active sort id
- sortFormStateInputFields the state to pass along when changing the order
- sortFormAction action of the sort form
- sortFieldName sort field name
- sortList (id, name) - sort elements (array with sub arrays each with id and name)