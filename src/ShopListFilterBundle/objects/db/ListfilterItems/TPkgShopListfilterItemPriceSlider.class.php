<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterItemPriceSlider extends TPkgShopListfilterItemNumeric
{
    protected $sItemFieldName = 'price';

    /**
     * return option as assoc array (name=>count).
     *
     * @return array
     */
    public function GetOptions()
    {
        $aOptions = $this->GetFromInternalCache('aOptions');
        if (is_null($aOptions)) {
            $aOptions = array();

            // we only care for the max and min value...
            $sIdSelect = $this->GetResultSetBaseQuery();

            $query = "SELECT MAX(`price`) AS maxprice, MIN(`price`) AS minprice
                    FROM `shop_article`
              INNER JOIN ({$sIdSelect}) AS Z ON `shop_article`.`id` = Z.`id`
                 ";

            if ($aValues = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                if ($aValues['minprice'] > 0) {
                    $aOptions[$aValues['minprice']] = 1;
                }
                if ($aValues['maxprice'] > 0) {
                    $aOptions[$aValues['maxprice']] = 1;
                }
            }
            $this->SetInternalCache('aOptions', $aOptions);
        }

        return $aOptions;
    }
}
