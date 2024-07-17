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
use Doctrine\DBAL\Exception;

class TableEditor extends \TCMSTableEditor
{
    /**
     * {@inheritdoc}
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
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

        $totalNewStock = $this->getAvailableStock($shopArticleStock->fieldShopArticleId);

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

    public function getAvailableStock($shopArticleId): int
    {
        /** @var int[]|false $stock */
        try {
            $stock = $this->getDatabaseConnection()->fetchOne(
                'SELECT SUM(`amount`) AS total_amount FROM `shop_article_stock` WHERE `shop_article_id` = :id GROUP BY `shop_article_id`',
                ['id' => $shopArticleId]
            );
        } catch (Exception $e) {
            return 0;
        }
        if (false === $stock) {
            return 0;
        }
        return (int) $stock;
    }
}
