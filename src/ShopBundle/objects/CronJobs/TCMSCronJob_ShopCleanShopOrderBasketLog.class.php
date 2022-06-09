<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * clear all rawdata from shop_order_basket that are older than x-days
 * delete all entries from the table that are older than n-days (n>x)
 * compress session objects in shop_order where entries older than n-days.
/**/
class TCMSCronJob_ShopCleanShopOrderBasketLog extends TCMSCronJob
{
    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        $iMaxAgeInSeconds = (SHOP_ORDER_BASKET_MAX_LOG_AGE_IN_DAYS * (60 * 60 * 24));
        $iMaxAge = time() - $iMaxAgeInSeconds;
        $query = "UPDATE `shop_order_basket`
                   SET `rawdata_basket`= '',
                       `rawdata_user`= '',
                       `rawdata_session`= ''
                       WHERE `lastmodified` < {$iMaxAge}";
        MySqlLegacySupport::getInstance()->query($query);
        $iMaxDoubleAge = $iMaxAge - $iMaxAgeInSeconds;
        $query = "DELETE FROM `shop_order_basket` WHERE `lastmodified` < {$iMaxDoubleAge}";
        MySqlLegacySupport::getInstance()->query($query);
    }
}
