<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopShippingGroupHandler extends TAdbShopShippingGroupHandler
{
    /**
     * return an instance of the correct class type for the filter identified by $id.
     *
     * @param int $id
     *
     * @return TdbShopShippingGroupHandler|null
     */
    public static function &GetInstance($id)
    {
        $oInstance = null;
        $query = "SELECT * FROM `shop_shipping_group_handler` WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($id)."'";
        if ($row = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $sClassName = $row['class'];
            $oInstance = new $sClassName();
            /** @var $oInstance TdbShopShippingGroupHandler */
            $oInstance->LoadFromRow($row);
        }

        return $oInstance;
    }
}
