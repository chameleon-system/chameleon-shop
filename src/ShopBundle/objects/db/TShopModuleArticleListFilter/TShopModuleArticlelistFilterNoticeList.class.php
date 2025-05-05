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

class TShopModuleArticlelistFilterNoticeList extends TdbShopModuleArticleListFilter
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
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
        $oExtranetUser = $this->getExtranetUserProvider()->getActiveUser();

        if ($oExtranetUser->IsLoggedIn()) {
            $quotedUserId = $connection->quote($oExtranetUser->id);
            $sQuery = "
            SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*, NOTICELISTTABLE.`date_added` AS item_added_to_list_date
              FROM `shop_article`
         LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
         LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
        INNER JOIN `shop_user_notice_list` AS NOTICELISTTABLE ON `shop_article`.`id` = NOTICELISTTABLE.`shop_article_id`
             WHERE NOTICELISTTABLE.`data_extranet_user_id` = {$quotedUserId}
        ";
        } else {
            $aNoticeList = $oExtranetUser->GetNoticeListArticles();
            $aItemIds = array_keys($aNoticeList);

            if (count($aItemIds) > 0) {
                // create tmp table for items
                $sTmpTableName = $this->CreateTempNotice($aNoticeList);
                $quotedTmpTableName = $connection->quoteIdentifier($sTmpTableName);

                $sQuery = "
                SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*, NOTICELISTTABLE.`date_added` AS item_added_to_list_date
                  FROM `shop_article`
             LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
             LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
            INNER JOIN {$quotedTmpTableName} AS NOTICELISTTABLE ON `shop_article`.`id` = NOTICELISTTABLE.`shop_article_id`
                 WHERE 1
            ";
            } else {
                $sQuery = parent::GetListQueryBase($oListConfig);
            }
        }

        return $sQuery;
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
            $sQuery .= ' NOTICELISTTABLE.`date_added` DESC';
        }

        return $sQuery;
    }

    /**
     * Crete temp notice list table for guest users and
     * returns temp table name.
     *
     * @param array $aNoticeList
     *
     * @return string
     */
    protected function CreateTempNotice($aNoticeList)
    {
        /** @var Doctrine\DBAL\Connection $connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $sTmpTableName = '_tmp'.session_id().'noticelist';
        $quotedTmpTableName = $connection->quoteIdentifier($sTmpTableName);

        $query = "
        CREATE TEMPORARY TABLE {$quotedTmpTableName} (
            `date_added` datetime NOT NULL,
            `shop_article_id` char(36) NOT NULL,
            `amount` decimal(10,2) NOT NULL default '1.00',
            KEY `shop_article_id` (`shop_article_id`),
            KEY `date_added` (`date_added`)
        ) ENGINE=MEMORY DEFAULT CHARSET=utf8
    ";
        $connection->executeStatement($query);

        foreach ($aNoticeList as $oNoteItem) {
            /* @var $oNoteItem TdbShopUserNoticeList */
            $connection->insert($sTmpTableName, [
                'date_added' => $oNoteItem->fieldDateAdded,
                'shop_article_id' => $oNoteItem->fieldShopArticleId,
                'amount' => $oNoteItem->fieldAmount,
            ]);
        }

        return $sTmpTableName;
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
     * return true if you want to include article variants in the result set
     * false if you only want parent-articles.
     *
     * @return bool
     */
    protected function AllowArticleVariants()
    {
        return true;
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
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
