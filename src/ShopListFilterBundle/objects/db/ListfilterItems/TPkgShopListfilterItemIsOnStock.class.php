<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemIsOnStock extends TPkgShopListfilterItemBoolean
{
    /**
     * you need to set this to the field name you want to filter by.
     *
     * @var string
     */
    protected $sItemFieldName = 'amount';

    /**
     * return option as assoc array (name=>count).
     *
     * @return array
     */
    public function GetOptions()
    {
        $aOptions = $this->GetFromInternalCache('aOptions');
        if (is_null($aOptions)) {
            if (PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM) {
                $oObj = clone $this->oItemListFilteredByOtherItems;
                $iCount = $this->oItemListFilteredByOtherItems->Length();
                $databaseConnection = $this->getDatabaseConnection();
                $quotedItemFieldName = $databaseConnection->quoteIdentifier($this->sItemFieldName);
                $oObj->AddFilterString(" `shop_article_stock`.$quotedItemFieldName > 0");
                $iWithStockCount = $oObj->Length();
                $aOptions[0] = $iCount - $iWithStockCount;
                $aOptions[1] = $iWithStockCount;
            } else {
                $aOptions[0] = 1;
                $aOptions[1] = 1;
            }
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }

    /**
     * return the query restriction for active filter. returns false if there
     * is no active restriction for this item.
     *
     * @return string
     */
    public function GetQueryRestrictionForActiveFilter()
    {
        $sQuery = '';
        $sValue = $this->GetActiveValue();
        if ('1' === $sValue) {
            $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
            $quotedField = $connection->quoteIdentifier($this->sItemFieldName);
            $sQuery = "`shop_article_stock`.$quotedField > 0";
        }

        return $sQuery;
    }
}
