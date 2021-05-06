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
 * cleanup old search results.
/**/
class TCMSCronJob_ShopSearchCacheGarbageCollector extends TdbCmsCronjobs
{
    const MAX_CACHE_AGE_IN_SECONDS = 3600;

    protected function _ExecuteCron()
    {
        $now = time();
        $query = "SELECT * FROM `shop_search_cache` WHERE ({$now} - UNIX_TIMESTAMP(`last_used_date`)) > ".self::MAX_CACHE_AGE_IN_SECONDS;
        $tres = MySqlLegacySupport::getInstance()->query($query);
        while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($tres)) {
            $query = "DELETE FROM `shop_search_cache_item` WHERE `shop_search_cache_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['id'])."' ";
            MySqlLegacySupport::getInstance()->query($query);
            $query = "DELETE FROM `shop_search_cache` WHERE `id` = '".MySqlLegacySupport::getInstance()->real_escape_string($aRow['id'])."' ";
            MySqlLegacySupport::getInstance()->query($query);
        }
    }
}
