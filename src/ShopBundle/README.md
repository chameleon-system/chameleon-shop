# Chameleon System ShopBundle

## Services

### chameleon_system_shop.shop_service

- service can be used to get the active shop object/config (or a config for a specific portal)
- use the service to get an instance of the active basket

## Checkout - Basket Steps

The page used for the checkout is defined in the shop table. Each checkout step allows you to overwrite this with a different page (you can select the page via backend by selecting the checkout step).

> **Note:**
>
> selecting a different page will affect the breadcrumb. So you should make sure, that the pages you select are sub-pages of the checkout page defined in your shop table.

## Routing

Product URLs contain the cmsident of the product as the last part of the URL. If you need to load the article using another field (such as the id or the article number) you can do so by replacing the class used in the chameleon_system_shop.shop_route_article_factory service. Simply define the service parameter chameleon_system_shop.shop_route_article_factory.class to a class that implements the ChameleonSystem\ShopBundle\Interfaces\ShopRouteArticleFactoryInterface

## Configuration

### Deactivate Shop-Related Dashboard Widgets

It is possible to disable the dashboard widgets related to the shop (found in `ChameleonSystem\ShopBundle\Dashboard\Widgets` and `ChameleonSystem\EcommerceStatsBundle\Bridge\Chameleon\Dashboard\Widgets`) by setting the parameter `chameleon_system_shop.enable_dashboard` to false.

## Addition Documentation

# Payment

## Payment Groups

Payments are grouped into payment groups. Every group defines payment methods that share an API. That means, there is generally one entry per meta payment provider [#]_, one for payment methods that require no handling (such as manual debit) and one for all payment providers that support only one payment handler (although they often get their own group as well).

This has one important implication: you will need to make sure, that the IPN URL matches the portal of the order. Otherwise the system will not be able to configure the payment handler with the correct parameters.

**Portal Support**

Note that the parameters you define for a payment handler group (such as API keys) can be portal specific. The logic here is, that a parameter that has been defined for the active portal will have precedence over a parameter that has no portal id.

**Environment Support**

Every parameter can be defined either for all environments, for sandbox or production. The environment to be used can be configured in the payment handler group configuration in the Chameleon backend ("System" tab). Note that this is the only place where the environment can be configured - older ways to do this will not work anymore.

## Initializing a Payment Handler

Always use the factory service `chameleon_system_shop.payment.handler_factory` to get a payment handler. This factory implements the interface `ChameleonSystem\\ShopBundle\\Payment\\PaymentHandler\\Interfaces\\ShopPaymentHandlerFactoryInterface` which defines a method `createPaymentHandler()` that creates a fully configured payment handler. The method requires the ID of the payment handler to initialize (ID field in the `shop_payment_handler` table) as well as a portal ID. You can optionally provide user-defined parameters.

## Configuration Details

The configuration process takes place in the service `chameleon_system_shop.payment.config_loader`. There are multiple sources for configuration:

- payment handler group configuration is loaded from the database
- an optional payment config provider is asked for its configuration data (see below for details). This configuration will be called "additional configuration" in this document.
- payment handler configuration is loaded from the database.

There are multiple "layers" of configuration which can override and extend each other, following this ruleset:

- A parameter either needs to conform to the current environment (e.g. a sandbox parameter is not used in live environment) or be in the pseudo-environment "common". This needs to be one of IPkgShopOrderPaymentConfig::ENVIRONMENT_*
- A parameter either needs to conform to the current portalId (e.g. a parameter for portal 1 is not used in portal 2) or have no portal setting.

A parameter which does not fulfill these requirements will be discarded.

For all fitting parameters the following **source** priority rules apply.

- A parameter in the additional configuration always overrides payment handler group configuration.
- A parameter in the payment handler configuration always overrides payment handler group and additional configuration.

Within the same source, the following **portal** priority rule applies.

- A parameter for the current portal overrides a parameter without portal restrictions.

Within the same portal, the following **environment** priority rule applies.

- A parameter for the current environment overrides a parameter which is defined as "common".

This allows very complex configurations that might get confusing for administrators. So better try keeping things as simple as possible.

## Config Providers

For every payment handler group, an optional payment config provider service can be specified to configure additional database-independent payment parameters (e.g. Symfony container parameters or external config files).

To implement a config provider, create a class that implements `ChameleonSystem\\ShopBundle\\Payment\\PaymentConfig\\Interfaces\\ShopPaymentConfigProviderInterface`. This interface defines a single method `getAdditionalConfiguration()` which should return an array of `ChameleonSystem\\ShopBundle\\Payment\\PaymentConfig\\ShopPaymentConfigRawValue` objects. These raw values contain all that is needed to apply the ruleset stated above in the `Configuration Details` section. One of the attributes in a raw value is `source` - always set ShopPaymentConfigRawValue::SOURCE_ADDITIONAL in the provider.

To register the provider, create a Symfony service and tag it with `chameleon_system_shop.payment_config_provider`. The tag also needs an attribute `system_name`; the value for this attribute must be exactly the same as the value in the `Systemname` field in the payment handler group configuration in the Chameleon backend.

Example:

```xml
<service id="chameleon_system_amazon_pay.config_provider" class="ChameleonSystem\\AmazonPayBundle\\Configuration\\ConfigProvider">
    <argument />
    <tag name="chameleon_system_shop.pay_config_provider" system_name="amazon" />
</service>
```

Please note:

- Only a single payment config provider can be set per payment handler group. The behaviour on multiple providers is undefined.
- The provider's `getAdditionalConfiguration()` method will be called at runtime, not at container compile time. If possible, prepare the configuration in the container compile process and simply hand it over when called by the system.
- The configuration might be cached for an arbitrary time. Do not expect that the config provider is called on every user request.

[#] meta payment providers are payment providers that support more than one payment method (e.g. PAYONE).

# Shop Module ArticleList

## Description

The module shows a product selection based on a per instance configurable filter definition. The Result is pageable and sortable.

## View logic (and ajax paging)

For ajax paging we need to get the articles without the module content. To support this, we introduced a method that renders just that part (it includes caching). It will search for mappers using the view name under the mapper chain configuration of the module. The mapper configuration itself for the views should be reduced to mappers that prepare data relevant for the list surrounding the article list.

The view used for the article list is currently hardcoded in the module class as a mapping from view name to article list snippet name. This will need to change.

Please note, that you need to map the template name used for the module to the view you want to use for the list of products rendered within the list. You can do that by setting `chameleon_system_shop.article_list.view_to_list_view_mapping` in your `config.yml`.

### Example

```yaml
parameters:
  chameleon_system_shop.article_list.view_to_list_view_mapping:
    rightNoticeList:           "/common/lists/listStandardShopArticle.html.twig"
    full:                      "/common/lists/listExtendedShopArticle.html.twig"
    standardEmptyOnNoArticles: "/common/lists/listScrollShopArticle.html.twig"
    standard:                  "/common/lists/listScrollShopArticle.html.twig"
```

## Extending the module

The module can be extended by providing state request extractors and result modifications.

### Changing state variables

If you want to change the behaviour of the list, you tend to need two things:

a) a way to add additional information into the lists state
b) a way to modify the lists results based on the lists state

#### state request extractor

The state request extractor takes the request data sent to the module (any post/get data sent to spotName).

Example: `spotName[foo]=bar` would sent `foo=bar` to the extractor

You should register a new request extractor whenever any of the request data sent to the module should affect the modules state.

State request extractors must implement the `StateRequestExtractorInterface` and must be tagged with `chameleon_system_shop.article_list_module.state_extractor`.

#### state element

A state element implements `StateElementInterface` and must be tagged with `chameleon_system_shop.article_list_module.state_element`. Every state element defines under what key the state element will be available in the state, a method to validate the input, and a method to normalize incoming data

#### result modifications

If you want to change the result of the list you should provide a result modification service that extends `ResultModificationInterface` tag the Service with `chameleon_system_shop.result_modifier`.

#### page size

All allowed page sizes must be configured via `chameleon_system_shop.state_factory.state_element_valid_page_sizes`. Example (for a `config.yml`):

```yaml
parameters:
  chameleon_system_shop.state_factory.state_element_valid_page_sizes:
    - 5
    - 10
    - 15
```

### Example

You can check the `pkgshoplistfilter` bundle as an example where the lists state is extended and the result are modified based on this.

### Summary

| type                   | implements                    | auto registered via tag                                  |
|------------------------|-------------------------------|----------------------------------------------------------|
| State request extractor | `StateRequestExtractorInterface` | `chameleon_system_shop.article_list_module.state_extractor` |
| State element          | `StateElementInterface`       | `chameleon_system_shop.article_list_module.state_element`   |
| Result modification    | `ResultModificationInterface` | `chameleon_system_shop.result_modifier`                    |

## Twig Variables

- items - array with `TdbShopArticle` holding all products for the page to be displayed
- itemsMappedData array with the mapped data for every item. contents of each item depends on the mapper used
- results - `ChameleonSystem\\ShopBundle\\objects\\ArticleList\\ResultData` holds the result data. is made available to be processed by other mappers
- listPagerUrl - url string that can be used to generate the url for a specific page. Replace the `_pageNumber_` with the page you would like to open
- listPageSizeChangeUrl - url string that can be used to generate a url that will switch to a different page size. Replace the `_pageSize_` with the page size you would like to change to.
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

## Configuration Parameters

- **chameleon_system_shop.enable_dashboard** (boolean): Disable shop-related dashboard widgets when set to false (default: true).
- **chameleon_system_shop.state_factory.state_element_valid_page_sizes** (collection): Valid page sizes for the ArticleList module state (see services.xml).
- **chameleon_system_shop.article_list.view_to_list_view_mapping** (map): Twig template mapping for ArticleList views (see services.xml).
- **chameleon_system_shop.product_controller.class** (string): Class name for the product route controller (default: TPkgShopRouteControllerArticle).
- **chameleon_system_shop.shop_route_article_factory.class** (string): Class name for the ShopRouteArticleFactory service.
- **chameleon_system_shop.shop_variant_type.data_model** (string): Variant type data model class.
- **chameleon_system_shop.shop_variant_type_value.data_model** (string): Variant type value data model class.

## Available Services

- **chameleon_system_shop.shop_service**: Main ShopService, provides active shop and basket.
- **chameleon_system_shop.product_stats_service**: ProductStatisticsService, tracks and retrieves product stats.
- **chameleon_system_shop.product_inventory_service**: ProductInventoryService (with cache proxy) for stock management.
- **chameleon_system_shop.product_variant_service**: ProductVariantService for variant lookup and naming.
- **chameleon_system_shop.payment.handler_factory**: Factory to create configured payment handlers.
- **chameleon_system_shop.payment.config_loader**: Loads and merges payment configuration.
- **chameleon_system_shop.shop_route_article_factory**: Builds product route objects by CMS identifier.
- **chameleon_system_shop.product_controller**: Symfony controller for product pages.
- **chameleon_system_shop.basket_step_controller**: Controller for checkout/basket steps routing.
- **chameleon_system_shop.category_route_collection_generator**: Generates category routes for routing.
- **chameleon_system_shop.basket_steps_route_collection_generator**: Generates basket step routes.
- **chameleon_system_shop.search_suggest_controller**: Controller for AJAX search suggestions endpoint.
  *Plus numerous mappers, result modifiers, and state extractors registered in services.xml.*

## Events

The ShopBundle dispatches events defined in `ChameleonSystem\ShopBundle\ShopEvents`:
- **ARTICLE_LIST_FILTER_EXECUTED** (`chameleon_system_shop.article_list.result_generated`)
- **BASKET_UPDATE_ITEM** (`chameleon_system_shop.basket_update_item`)
- **BASKET_DELETE_ITEM** (`chameleon_system_shop.basket_delete_item`)
- **BASKET_CLEAR** (`chameleon_system_shop.basket_clear`)
- **UPDATE_PRODUCT_STOCK** (`chameleon_system_shop.update_product_stock`)
- **ORDER_SAVED** (`chameleon_system_shop.order_saved_in_database`)
- **ORDER_SEND_TO_INVENTORY_MANAGEMENT** (`chameleon_system_shop.order_send_to_inventory_management`)
- **ORDER_POST_INSERT** (`chameleon_system_shop.order_post_insert`)
- **ORDER_PRE_EXECUTED_PAYMENT** (`chameleon_system_shop.order_pre_executed_payment`)
- **ORDER_EXECUTED_PAYMENT** (`chameleon_system_shop.order_executed_payment`)
- **ORDER_PRE_DELETE** (`chameleon_system_shop.order_pre_delete`)