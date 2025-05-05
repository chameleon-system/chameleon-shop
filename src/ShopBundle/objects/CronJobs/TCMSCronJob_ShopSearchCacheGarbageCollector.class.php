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
 * /**/
class TCMSCronJob_ShopSearchCacheGarbageCollector extends TdbCmsCronjobs
{
    public const MAX_CACHE_AGE_IN_SECONDS = 3600;

    /**
     * @return void
     */
    protected function _ExecuteCron()
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $now = time();

        $query = "
        SELECT *
          FROM `shop_search_cache`
         WHERE ({$now} - UNIX_TIMESTAMP(`last_used_date`)) > ".self::MAX_CACHE_AGE_IN_SECONDS.'
    ';

        $statement = $connection->executeQuery($query);

        while ($aRow = $statement->fetchAssociative()) {
            $quotedCacheId = $connection->quote($aRow['id']);

            $deleteItemsQuery = "
            DELETE FROM `shop_search_cache_item`
             WHERE `shop_search_cache_id` = {$quotedCacheId}
        ";
            $connection->executeStatement($deleteItemsQuery);

            $deleteCacheQuery = "
            DELETE FROM `shop_search_cache`
             WHERE `id` = {$quotedCacheId}
        ";
            $connection->executeStatement($deleteCacheQuery);
        }
    }
}
