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
 * clear shop search log entries older than x days. x = CHAMELEON_SHOP_SEARCH_LOG_MAX_AGE_IN_DAYS.
/**/
class TCMSCronJob_CleanShopSearchLog extends TCMSCronJob
{
    protected function _ExecuteCron()
    {
        if (CHAMELEON_SHOP_SEARCH_LOG_MAX_AGE_IN_DAYS !== false && CHAMELEON_SHOP_SEARCH_LOG_MAX_AGE_IN_DAYS !== 0) {
            $iMaxAgeInSeconds = (CHAMELEON_SHOP_SEARCH_LOG_MAX_AGE_IN_DAYS * (60 * 60 * 24));
            $iMaxAge = time() - $iMaxAgeInSeconds;
            $sMaxAge = date('Y-m-d H:i:s', $iMaxAge);
            $query = "DELETE FROM `shop_search_log`
                           WHERE `search_date` <  '{$sMaxAge}'
                           ";
            MySqlLegacySupport::getInstance()->query($query);
        }
    }
}
