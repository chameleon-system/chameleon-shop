# Chameleon System CmsNavigationPkgShopBundle
=============================================
Overview
--------
The CmsNavigationPkgShopBundle extends the core CMS sub-navigation to integrate with the Chameleon Shop module. It renders a category-based sub-navigation tree for shop pages, automatically detecting the active shop category and displaying its child categories.

Key Features
------------
- Automatic detection of active shop category and its root category
- Renders a subtree of shop categories as navigation links
- Caching support with cache triggers for shop category changes
- Seamless integration with the Twig-based ViewRenderer and mapper system

Installation
------------
This bundle is included in the `chameleon-system/chameleon-shop` package.  
No additional Composer installation is needed.
To register manually (or without without Flex), add to `app/AppKernel.php`:
```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\CmsNavigationPkgShopBundle\ChameleonSystemCmsNavigationPkgShopBundle(),
    ];
    return $bundles;
}
```
Clear cache:
```bash
php bin/console cache:clear
```

Usage
-----
1. **Place the Sub-Navigation Module**
   - In the **Table Editor** (`cms_tpl_module_instance`), create a new record:
     - **Module Type**: select **SubNavigation PkgShop**
     - **Spot Name**: e.g. `sidebar_navigation`
     - **Theme** and **Portal** as needed
   - Attach this module to your shop pages to display category navigation.

2. **Mapper Logic**
   The module mapper (`MTPkgCmsSubNavigation_PkgShop`) retrieves the active root and current category via the `chameleon_system_shop.shop_service`, builds a single-node Razor tree, and outputs it as `aTree` for the Twig template.

3. **Twig Rendering**
   In your theme, include the snippet corresponding to the module’s path (e.g. `snippets-cms/sub_navigation/pkgShop.html.twig`), iterating over `aTree` and rendering links.

Configuration
-------------
No additional configuration is required. Ensure your shop categories are set up in the **Category Tree** (`shop_category`), and the **Shop Service** is active in your portal.

Extensibility
-------------
- **Custom Twig Templates**: Copy `vendor/chameleon-system/chameleon-shop/src/CmsNavigationPkgShopBundle/Resources/views/snippets-cms/sub_navigation/pkgShop.html.twig` into your theme folder (`src/themes/<yourTheme>/snippets-cms/sub_navigation/pkgShop.html.twig`) to override markup.
- **Extend Mapper**: Create a subclass of `MTPkgCmsSubNavigation_PkgShop` to adjust node selection or add metadata; register it as a service tagged with `chameleon_system.mapper` and update your module instance to use it.
- **Cache Triggers**: Use the `$oCacheTriggerManager` in the mapper to add additional triggers (e.g., on product or category updates).

License
-------
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.
