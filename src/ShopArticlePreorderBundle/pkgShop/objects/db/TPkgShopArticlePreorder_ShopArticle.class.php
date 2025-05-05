<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopArticlePreorder_ShopArticle extends TPkgShopArticlePreorder_ShopArticleAutoParent
{
    /**
     * returns true if the article is buyable, false if it is not.
     *
     * @return bool
     */
    public function IsBuyable()
    {
        // ------------------------------------------------------------------------
        $bIsBuyable = parent::IsBuyable();
        if (true === $bIsBuyable) {
            if ($this->fieldShowPreorderOnZeroStock && $this->getAvailableStock() < 1) {
                $bIsBuyable = false;
            }
        }

        /*
        $oStockMessage = $this->GetFieldShopStockMessage();
        $bPreorder = false;
        if ($oStockMessage) $bPreorder = $this->fieldShowPreorderOnZeroStock;
        $bOnlyPreOrder = ($bPreorder && $this->fieldStock > 0) || !$bPreorder;
        if($bIsBuyable && $bOnlyPreOrder === false) $bIsBuyable = null;
        */
        return $bIsBuyable;
    }

    // ------------------------------------------------------------------------
}
