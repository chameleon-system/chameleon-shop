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

class TShopModuleArticlelistFilterOtherCustomersBought extends TdbShopModuleArticleListFilter
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
        $sQuery = 'SELECT 0 AS cms_search_weight, `shop_article`.*
                   FROM `shop_article`
              LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
              LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
                  WHERE 1=0
                ';

        $sArticleRestriction = '';
        $oActiveArticle = TdbShop::GetActiveItem();
        if (!is_null($oActiveArticle)) {
            $sArticleRestriction = " (`shop_order_item`.`shop_article_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oActiveArticle->id)."')";
        }
        if (!empty($sArticleRestriction)) {
            // now fetch all orders that bought this article except our own orders

            $sCounterQuery = "SELECT DISTINCT `shop_order`.`id`
                     FROM `shop_order_item`
               INNER JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
                    WHERE {$sArticleRestriction}
                  ";

            $oUser = $this->getExtranetUserProvider()->getActiveUser();
            if ($oUser->IsLoggedIn()) {
                $sCounterQuery .= " AND `shop_order`.`data_extranet_user_id` != '".MySqlLegacySupport::getInstance()->real_escape_string($oUser->id)."'";
            }

            // fetch at most 100 orders
            $sCounterQuery .= 'LIMIT 0,100';
            $aOrderIds = array();
            $tres = MySqlLegacySupport::getInstance()->query($sCounterQuery);
            while ($aRow = MySqlLegacySupport::getInstance()->fetch_row($tres)) {
                $aOrderIds[] = MySqlLegacySupport::getInstance()->real_escape_string($aRow[0]);
            }

            if (count($aOrderIds) > 0) {
                $sQuery = "SELECT 0 AS cms_search_weight, `shop_article`.*, SUM(`shop_order_item`.`order_amount`) AS shop_order_item_number_of_times_bought
                       FROM `shop_article`
                  LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
                  LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
                 INNER JOIN `shop_order_item` ON `shop_article`.`id` = `shop_order_item`.`shop_article_id`
                      WHERE `shop_order_item`.`shop_order_id` IN ('".implode("', '", $aOrderIds)."')
                        AND `shop_order_item`.`shop_article_id` != '".MySqlLegacySupport::getInstance()->real_escape_string($oActiveArticle->id)."'
                    ";
            }
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
        $sGroupBy = 'GROUP BY `shop_article`.`id`';

        return $sGroupBy;
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
