<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopModuleArticlelistFilterAccessories extends TdbShopModuleArticleListFilter
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
    protected function GetListQueryBase(&$oListConfig)
    {
        $sQuery = 'SELECT DISTINCT 0 AS cms_search_weight, `shop_article`.*
                   FROM `shop_article`
              LEFT JOIN `shop_article_stats` ON `shop_article`.`id` = `shop_article_stats`.`shop_article_id`
              LEFT JOIN `shop_article_stock` ON `shop_article`.`id` = `shop_article_stock`.`shop_article_id`
             INNER JOIN `shop_article_shop_article2_mlt` ON `shop_article`.`id` = `shop_article_shop_article2_mlt`.`target_id`
                ';

        $sArticleRestriction = '';
        $oActiveArticle = TdbShop::GetActiveItem();
        if (!is_null($oActiveArticle)) {
            // check if the article has related articles - if it does not and it is a variant, try the parent
            $sRelationKey = $oActiveArticle->id;
            if ($oActiveArticle->IsVariant()) {
                $oRelations = $oActiveArticle->GetFieldShopArticle2List();
                if (0 == $oRelations->Length()) {
                    $sRelationKey = $oActiveArticle->fieldVariantParentId;
                }
            }

            $sArticleRestriction = " (`shop_article_shop_article2_mlt`.`source_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sRelationKey)."')";
            $sQuery .= ' WHERE '.$sArticleRestriction;
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
        $aParams = parent::_GetCacheParameters();
        $oShop = TdbShop::GetInstance();

        $oActiveArticle = $oShop->GetActiveItem();
        if (!is_null($oActiveArticle)) {
            $aParams['articleId'] = $oActiveArticle->id;
        }

        return $aParams;
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
}
