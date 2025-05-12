Chameleon System ShopArticlePreorderBundle
===============================================

Overview
--------
The ShopArticlePreorderBundle enables customers to sign up for preorders on out-of-stock shop articles. It
displays a preorder form on the detail page, captures customer emails, stores preorder records, and
sends notification emails when stock becomes available.

Key Features
------------
- Display preorder option on article detail pages when stock is zero and preorder is enabled.
- Customer email capture with validation and CMS messages for success or errors.
- Persistence of preorder records (`pkg_shop_article_preorder`) with storefront and portal context.
- Automatic notification: when product stock is updated from zero to positive, registered customers receive emails.
- Pluggable central handler (`PreorderArticle` method) for custom signup logic.

Installation
------------
This bundle is part of the `chameleon-system/chameleon-shop` package.
No additional Composer installation is required.
To register manually (without Symfony Flex), add to `app/AppKernel.php`:
```php
public function registerBundles()
{
    $bundles = [
        // ...
        new ChameleonSystem\ShopArticlePreorderBundle\ChameleonSystemShopArticlePreorderBundle(),
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
1. **Place the Preorder Module**  
   - In the **Table Editor** (`cms_tpl_module_instance`), create a new record:
     - **Module Type**: select **Article Preorder**
     - **Spot Name**: e.g. `article_preorder`
     - **Theme** and **Portal** as appropriate.

2. **Enable Preorder on Article**  
   - For each article record, set **Show Preorder On Zero Stock** flag.
   - The module will only display the preorder form when the article’s available stock is less than 1.

3. **Customer Signup**  
   - The form posts to the central handler method `PreorderArticle`, which reads `user_email`, validates it, and
     saves a preorder entry with `shop_article_id`, `preorder_user_email`, and `cms_portal_id`.
   - Success and error messages are added via `TCMSMessageManager` with message codes:
     - `PKG-SHOP-ARTICLE-PREORDER` / `SUCCESS-SIGNUP-PREORDER-ARTICLE`
     - `PKG-SHOP-ARTICLE-PREORDER` / `ERROR-E-MAIL-INVALID-INPUT`

4. **Notification on Re-Stock**  
   - `UpdateProductStockListener` listens to `chameleon_system_shop.update_product_stock` events.
   - When stock rises from zero to positive, it fetches all preorder records for the article and calls `SendMail()` on each.

Configuration
-------------
No additional bundle-specific configuration is required. Event listener and handler services are defined in
`Resources/config/services.xml` and auto-loaded.

Extensibility
-------------
- **Customize signup logic**: extend `MTPkgShopArticlePreorder_ShopCentralHandlerCore` or override `PreorderArticle()`.
- **Override email templates**: customize `SendMail()` in `TPkgShopArticlePreorder` or template snippets in your theme.
- **Custom stock event**: replace or decorate `UpdateProductStockListener` for alternate notification flows.

License
-------
This bundle is released under the same license as the Chameleon System. See the LICENSE file in the project root.
