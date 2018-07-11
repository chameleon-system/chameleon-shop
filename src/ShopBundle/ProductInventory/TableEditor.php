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

use TdbShopArticle;
use TdbShopArticleStock;

class TableEditor extends \TCMSTableEditor
{
    /**
     * @var string
     */
    private $newStockValue;

    /**
     * {@inheritdoc}
     */
    protected function PrepareDataForSave($postData)
    {
        $postData = parent::PrepareDataForSave($postData);
        /**
         * In UpdateStock() it is assumed that the value was not changed before while the table editor would save it
         * before calling the PostSaveHook() method - to achieve compatibility between the two approaches, we simply
         * restore the old amount value before saving.
         */
        $this->newStockValue = $postData['amount'];
        /**
         * @var TdbShopArticleStock $preChangeData
         */
        $preChangeData = $this->oTablePreChangeData;
        $postData['amount'] = $preChangeData->fieldAmount;

        return $postData;
    }

    /**
     * {@inheritdoc}
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        /**
         * @var TdbShopArticleStock $shopArticleStock
         */
        $shopArticleStock = $this->oTable;
        $article = TdbShopArticle::GetNewInstance($shopArticleStock->fieldShopArticleId);
        $article->UpdateStock(doubleval($this->newStockValue), false, false, true);
    }
}
