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

use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\ShopEvents;
use TdbShopArticleStock;

class TableEditor extends \TCMSTableEditor
{
    /**
     * {@inheritdoc}
     */
    protected function PostSaveHook($oFields, $oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        /**
         * @var TdbShopArticleStock $shopArticleStock
         */
        $shopArticleStock = $this->oTable;
        /**
         * @var TdbShopArticleStock $preChangeData
         */
        $preChangeData = $this->oTablePreChangeData;
        $this->getEventDispatcher()->dispatch(
            new UpdateProductStockEvent(
                $shopArticleStock->fieldShopArticleId,
                $shopArticleStock->fieldAmount,
                $preChangeData->fieldAmount
            ),
            ShopEvents::UPDATE_PRODUCT_STOCK
        );
    }
}
