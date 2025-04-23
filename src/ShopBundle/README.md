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

- [Payment](./Resources/doc/payment.md)
- [Shop Module ArticleList](./Resources/doc/modules/ArticleList.md)