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
 * show all articles from the active manufacturer.
 * /**/
class TShopModuleArticlelistFilterArticleOfActiveManufacturer extends TdbShopModuleArticleListFilter
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

        $oActiveManufacturer = TdbShop::GetActiveManufacturer();
        if (!is_null($oActiveManufacturer)) {
            $quotedManufacturerId = $connection->quote($oActiveManufacturer->id);

            $sQuery = "SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*
                     FROM `shop_article`
                LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
                LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
                    WHERE `shop_article`.`shop_manufacturer_id`  = {$quotedManufacturerId}";
        } else {
            $sQuery = parent::GetListQueryBase($oListConfig);
        }

        return $sQuery;
    }
    /**
     * return any cache relevant parameters to the list class here.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $aParams = [];
        $activeCategory = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveCategory();
        if (!is_null($activeCategory)) {
            $aParams['activecategoryid'] = $activeCategory->id;
        }
        $oActiveManufacturer = TdbShop::GetActiveManufacturer();
        if ($oActiveManufacturer) {
            $aParams['activemanufacturerid'] = $oActiveManufacturer->id;
        }

        return $aParams;
    }
}
