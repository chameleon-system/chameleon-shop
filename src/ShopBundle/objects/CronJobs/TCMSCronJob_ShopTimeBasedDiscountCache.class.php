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
 * the job is used to clear the cache for discounts that are activated/deactivate based on the time field (active from/to)
 * note: in order to be able to note the change, we need to know the discounts "real" active state - or rather,
 * we need to know, if the discount has been processed for the current time window so we avoid clearing the cache on each call.
 *
 * /**/
class TCMSCronJob_ShopTimeBasedDiscountCache extends TdbCmsCronjobs
{
    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedToday = $connection->quote(date('Y-m-d H:i:s'));

        $query = "
        SELECT `shop_discount`.*
          FROM `shop_discount`
         WHERE `shop_discount`.`active` = '1'
           AND (
                (`cache_clear_last_executed` < `active_from`
                    AND (`active_from` <= {$quotedToday} AND (`active_to` != '0000-00-00 00:00:00' OR `active_to` >= {$quotedToday}))
                )
                OR
                (`cache_clear_last_executed` < `active_to`
                    AND (`active_to` != '0000-00-00 00:00:00' AND `active_to` <= {$quotedToday})
                )
            )
    ";

        $oDiscountList = TdbShopDiscountList::GetList($query);

        while ($oDiscount = $oDiscountList->Next()) {
            $oDiscount->ClearCacheOnAllAffectedArticles();
        }
    }
}
