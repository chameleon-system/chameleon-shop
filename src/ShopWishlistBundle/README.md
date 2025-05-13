Chameleon System ShopWishlistBundle
===================================

Overview
--------
The ShopWishlistBundle provides functionality for customers to create and manage wishlists of shop articles. Users can add or remove items, update wishlist details, share their wishlist via email, and view public wishlists shared by others.

Features
--------
- User-specific wishlists tied to Extranet user accounts
- Add or remove articles programmatically or via built-in modules
- Update wishlist name, description, and privacy settings (public/private)
- Share wishlist via email invitations with optional comments
- Frontend modules:
  - **Wishlist**: manage the current user's wishlist (add/remove, send, update)
  - **Public Wishlist**: search and display a public wishlist by ID
  - **Basket Integration**: add wishlist items directly to the shopping basket
- Automatic cleanup of orphaned wishlist items via cronjob

Installation
------------
This bundle is included by default. To register manually, add to your AppKernel or bundles.php:
```php
new ChameleonSystem\\ShopWishlistBundle\\ChameleonSystemShopWishlistBundle(),
```

Activate the cleanup cronjob **“Wishlist Cleanup”** in the CMS cronjob list to automatically remove orphaned wishlist items.

Configuration
-------------
- **Email Templates**: Customize the wishlist share email by overriding the view in your theme at:
  `pkgShopWishlist/views/db/TPkgShopWishlist/vSendForm.html.twig` (or your project path).
- **Module Templates**: Override frontend module templates under:
  `pkgShopWishlist/views/Modules/MTPkgShopWishlist`.

System Pages
------------
- **Wishlist** (URL alias: `wishlist`): Displays the current user's wishlist with management and share form.
- **Wishlist Public** (URL alias: `wishlist-public`): Allows guests to view a public wishlist by passing `[MTPkgShopWishlistPublicCore[id]=<wishlistId>]` as a query parameter.

Usage
-----
### Add/Remove Articles
Add the Wishlist module to a page:

Or programmatically in PHP:

```php
$user = TdbDataExtranetUser::GetInstance();
$newCount = $user->AddArticleIdToWishlist($articleId, $quantity);
$user->RemoveArticleFromWishlist($wishlistItemId);
```

### View & Update Wishlist
Render the `PkgShopWishlist` module on a page, or link to the system page:

```twig
<a href="{{ shop.GetLinkToSystemPage('wishlist') }}">My Wishlist</a>
```

### Public Wishlist
Add the Public Wishlist module to a page `PkgShopWishlistPublic`.

URL example: `/wishlist-public?MTPkgShopWishlistPublicCore[id]=<wishlistId>`

### Share Wishlist
Within the Wishlist module, fill out the share form and submit. Or programmatically:

```php
$wishlist = $user->GetWishlist();
$wishlist->SendPerMail($toEmail, $toName, $comment);
```

API Reference
-------------
```php
// Extranet user methods
TdbDataExtranetUser::AddArticleIdToWishlist(string $articleId, float $amount = 1): float
TdbDataExtranetUser::RemoveArticleFromWishlist(string $wishlistItemId): void
TdbDataExtranetUser::GetWishlist(bool $createIfNotExists = false): ?TdbPkgShopWishlist

// Wishlist methods
TdbPkgShopWishlist::AddArticle(string $articleId, float $amount = 1, string $comment = null): float
TdbPkgShopWishlist::GetLink(string $mode = '', array $params = []): string
TdbPkgShopWishlist::GetPublicLink(array $params = []): string
TdbPkgShopWishlist::SendPerMail(string $toEmail, string $toName, string $comment): bool

// Cronjob
TCMSCronJob_CleanWishlist::_ExecuteCron(): void
```

License
-------
This bundle is released under the MIT License. See the main `LICENSE` file for details.
