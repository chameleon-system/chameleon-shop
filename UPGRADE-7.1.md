UPGRADE FROM 7.0 to 7.1
=======================
# Deprecated Methods

* `\TShopArticle::UpdateStock` (it had too many jobs and was at the same time to unflexible)
  * you should use `\ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface` to update stock
  * and `\ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface` to update product stats 
* `\TShopArticle::StockWasUpdatedHook`
  * will no longer be called - use an event listener for `\ChameleonSystem\ShopBundle\ShopEvents::UPDATE_PRODUCT_STOCK` instead

# Changed Features
## Cronjobs
All Cron Jobs should now call the superclass constructor.

Before:
```php
 public function __construct()
    {
        parent::TCMSCronJob();
    }
```
Should Be:
```php
 public function __construct()
    {
        parent::__construct();
    }
```
