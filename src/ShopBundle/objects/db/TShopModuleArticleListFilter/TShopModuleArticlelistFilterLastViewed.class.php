<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

class TShopModuleArticlelistFilterLastViewed extends TdbShopModuleArticleListFilter
{
    /**
     * return the base of the query. overwrite this method for each filter to add custom filtering
     * the method should include a query of the form select shop_article.*,... FROM shop_article ... WHERE ...
     * NOTE: do not add order info or limit the query (overwrite GetListQueryOrderBy and GetListQueryLimit instead)
     * NOTE 2: the query will automatically be restricted to all active articles.
     * NOTE 3: you query must include a where statement.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryBase($oListConfig)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sQuery = '';
        $oExtranetUser = $this->getExtranetUserProvider()->getActiveUser();

        if ($oExtranetUser->IsLoggedIn()) {
            // we can link with the real user table
            $quotedUserId = $connection->quote($oExtranetUser->id);

            $sQuery = "SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*, HISTLIST.`datecreated` AS item_added_to_list_date
                 FROM `shop_article`
            LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
            LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
           INNER JOIN `data_extranet_user_shop_article_history` AS HISTLIST ON `shop_article`.`id` = HISTLIST.`shop_article_id`
                WHERE HISTLIST.`data_extranet_user_id` = {$quotedUserId}";
        } else {
            $aHistoryList = $oExtranetUser->GetArticleViewHistory();

            if (count($aHistoryList) > 0) {
                $quotedTmpTableName = $connection->quoteIdentifier('_tmp'.session_id().'histlist');
                $this->CreateTempHistory($aHistoryList);

                // restricting the query to the items affected has a dramatic performance effect - I have no idea why... but using the join alone is much slower
                // strangely, this only applies to the temporary memory table below - not to the same query above being used against data_extranet_user_shop_article_history...
                $aKeys = array_map(static function ($item) use ($connection) {
                    return $connection->quote($item->fieldShopArticleId);
                }, array_values($aHistoryList));

                $sQuery = "SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*, HISTLIST.`datecreated` AS item_added_to_list_date
                   FROM `shop_article`
              LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
              LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
             INNER JOIN {$quotedTmpTableName} AS HISTLIST ON `shop_article`.`id` = HISTLIST.`shop_article_id`
                  WHERE `shop_article`.`id` IN (".implode(',', $aKeys).")";
            } else {
                $sQuery = "SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*, '0000-00-00 00:00:00' AS item_added_to_list_date
                   FROM `shop_article`
              LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
              LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
             INNER JOIN `data_extranet_user_shop_article_history` AS HISTLIST ON `shop_article`.`id` = HISTLIST.`shop_article_id`
                  WHERE 1=2";
            }
        }

        return $sQuery;
    }

    /**
     * @param array $aHistoryList
     *
     * @return void
     */
    protected function CreateTempHistory($aHistoryList)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        // create tmp table for items
        $tmpTableName = '_tmp'.session_id().'histlist';
        $quotedTmpTableName = $connection->quoteIdentifier($tmpTableName);

        $query = "
        CREATE TEMPORARY TABLE {$quotedTmpTableName} (
            `datecreated` datetime NOT NULL,
            `shop_article_id` char(36) NOT NULL,
            KEY `shop_article_id` (`shop_article_id`),
            KEY `datecreated` (`datecreated`)
        ) ENGINE=MEMORY DEFAULT CHARSET=utf8
    ";
        $connection->executeStatement($query);

        foreach ($aHistoryList as $oItem) {
            /** @var $oItem TdbDataExtranetUserShopArticleHistory */
            $connection->executeStatement(
                "INSERT INTO {$quotedTmpTableName} (`datecreated`, `shop_article_id`) VALUES (:datecreated, :shop_article_id)",
                [
                    'datecreated' => $oItem->fieldDatecreated,
                    'shop_article_id' => $oItem->fieldShopArticleId,
                ]
            );
        }
    }

    /**
     * returns the order by part of the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryOrderBy($oListConfig)
    {
        $sQuery = '';
        if ($this->bUsedBaseQuery) {
            $sQuery = parent::GetListQueryOrderBy($oListConfig);
        } else {
            $sQuery .= ' HISTLIST.`datecreated` DESC';
        }

        return $sQuery;
    }

    /**
     * define the group by for the query.
     *
     * @param TdbShopModuleArticleList $oListConfig
     *
     * @return string
     */
    protected function GetListQueryGroupBy($oListConfig)
    {
        return '';
    }

    /**
     * overwrite this if you want to prevent the list from caching this filter result.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
