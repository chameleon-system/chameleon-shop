<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemIsReduced extends TPkgShopListfilterItemBoolean
{
    /**
     * you need to set this to the field name you want to filter by.
     *
     * @var string
     */
    protected $sItemFieldName = 'price_reference';

    /**
     * return option as assoc array (name=>count).
     *
     * @return array
     */
    public function GetOptions()
    {
        $aOptions = $this->GetFromInternalCache('aOptions');
        if (is_null($aOptions)) {
            $aOptions = $this->oItemListFilteredByOtherItems->GetItemUniqueValueListForField('id', [$this, 'ArticleIsReduced']);
            $this->OrderOptions($aOptions);
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }

    /**
     * @param string $sFieldName
     * @param string $sValue
     * @param array<string, string> $aRow
     *
     * @return string
     *
     * @psalm-return '0'|'1'
     */
    public function ArticleIsReduced($sFieldName, $sValue, $aRow)
    {
        if ($aRow['price_reference'] > $aRow['price']) {
            return '1';
        } else {
            return '0';
        }
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
        if ('1' == $sValue) {
            $sQuery = '`shop_article`.`price_reference` > 0 AND `shop_article`.`price_reference` != `shop_article`.`price` ';
        } elseif ('0' == $sValue) {
            $sQuery = '`shop_article`.`price_reference` = 0 OR `shop_article`.`price_reference` = `shop_article`.`price` ';
        }

        return $sQuery;
    }
}
