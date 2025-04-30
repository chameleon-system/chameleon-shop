<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticlelistFilterBestsellerActiveCategory extends TShopModuleArticlelistFilterBestseller
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
        $oActiveCategory = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();

        if (!is_null($oActiveCategory)) {
            $aCategories = $oActiveCategory->GetAllChildrenIds();
            $aCategories[] = $oActiveCategory->id;
            $aCategories = array_map([$connection, 'quote'], $aCategories);

            $sQuery = "SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*
                 FROM `shop_article_shop_category_mlt`
           INNER JOIN `shop_article` ON `shop_article_shop_category_mlt`.`source_id` = `shop_article`.`id`
            LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
            LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
                WHERE `shop_article_shop_category_mlt`.`target_id` IN (".implode(',', $aCategories).")
                  AND `shop_article_stats`.`stats_sales` > 0";

            $results = $connection->fetchAllAssociative($sQuery);
            $iNumRecs = count($results);

            if (($oListConfig->fieldNumberOfArticles > 0 && $iNumRecs < $oListConfig->fieldNumberOfArticles) || ($iNumRecs < 1)) {
                $sQuery = parent::GetListBaseQueryRestrictedToCategories($oListConfig, $aCategories);
                if ($iNumRecs > 0) {
                    $aList = array_map(fn($row) => $connection->quote($row['id']), $results);
                    if (count($aList) > 0) {
                        $sQuery .= ' OR `shop_article`.`id` IN ('.implode(',', $aList).')';
                    }
                }
            }
        } else {
            $sQuery = parent::GetListQueryBase($oListConfig);
        }

        return $sQuery;
    }
}
