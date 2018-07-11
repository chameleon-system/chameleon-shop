<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemIsNew extends TPkgShopListfilterItemBoolean
{
    /**
     * you need to set this to the field name you want to filter by.
     *
     * @var string
     */
    protected $sItemFieldName = 'is_new';

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
        if ('1' == $sValue || '0' == $sValue) {
            $sQuery = "`shop_article`.`{$this->sItemFieldName}` = '".MySqlLegacySupport::getInstance()->real_escape_string($sValue)."'";
        }

        return $sQuery;
    }
}
