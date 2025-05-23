<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\ProductInventory;

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use ChameleonSystem\ShopBundle\ShopEvents;
use Psr\Log\LoggerInterface;

class TableEditor extends \TCMSTableEditor
{
    /**
     * {@inheritdoc}
     */
    protected function PostSaveHook($oFields, $oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        /**
         * @var \TdbShopArticleStock $shopArticleStock
         */
        $shopArticleStock = $this->oTable;
        /**
         * @var \TdbShopArticleStock $preChangeData
         */
        $preChangeData = $this->oTablePreChangeData;

        $totalNewStock = $this->getProductInventoryService()->getAvailableStock($shopArticleStock->fieldShopArticleId);

        $changedAmount = $preChangeData->fieldAmount - $shopArticleStock->fieldAmount;
        $totalOldStock = $totalNewStock + $changedAmount;

        $this->getEventDispatcher()->dispatch(
            new UpdateProductStockEvent(
                $shopArticleStock->fieldShopArticleId,
                $totalNewStock,
                $totalOldStock
            ),
            ShopEvents::UPDATE_PRODUCT_STOCK
        );
    }

    private function getLogger(): LoggerInterface
    {
        return ServiceLocator::get('logger');
    }

    private function getProductInventoryService(): ProductInventoryServiceInterface
    {
        return ServiceLocator::get('chameleon_system_shop.product_inventory_service_provider');
    }
}
