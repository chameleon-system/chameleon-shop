UPGRADE FROM 7.1 to 8.0
=======================

### List Of Removed Or Changed Code

- `\TShopArticle::UpdateStock` (it had too many jobs and was at the same time to unflexible)
  - you should use `\ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface` to update stock
  - and `\ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface` to update product stats
- `\TShopArticle::StockWasUpdatedHook` will no longer be called - use an event listener for `\ChameleonSystem\ShopBundle\ShopEvents::UPDATE_PRODUCT_STOCK` instead
- `MTPkgExternalTracker_MTShopArticleCatalogCore` removed
- `TCMSWizardStepShopTellAFriend` removed
- `TShop::GetActiveItemVariant` removed (search for `GetActiveItemVariant` because it may be called via TdbShop)
- `TShopVariantDisplayHandler::GetActiveVariantTypeSelection` removed
- `TShopVariantDisplayHandler::GetArticleMatchingCurrentSelection` removed
- `TShopStepUserDataCore` removed
- `TShop::GetInstance()`, `TShop::GetActiveCategory`, `TShop::GetActiveItem()`, `TShop::GetActiveRootCategory` removed
  search and replace:
  - `TdbShop::GetInstance()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop()`
  - `TShop::GetInstance()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop()`
  - `TdbShop::GetInstance(` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId(`
  - `TdbShop::GetActiveCategory()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory()`
  - `TShop::GetActiveCategory()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory()`
  - `TdbShop::GetActiveItem()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct()`
  - `TShop::GetActiveItem()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct()`
  - `hop->GetActiveItem()` -> manually replace with `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveProduct()`
  - `TdbShop::GetActiveRootCategory()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveRootCategory()`
  - `TShop::GetActiveRootCategory()` -> `\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveRootCategory()`
  - finaly search for `->GetActiveRootCategory()` to find any other calles maybe based on TShop service instances 