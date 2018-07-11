<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticleCatalogConf extends TShopArticleCatalogConfAutoParent
{
    // **************************************************************************

    /**
     * Returns the active order by for the given category.
     *
     * @param TdbShopCategory $oActiveCategory
     *
     * @return string
     */
    public function GetDefaultOrderBy(TdbShopCategory $oActiveCategory)
    {
        $sDefaultOrderBy = $this->fieldShopModuleArticlelistOrderbyId;

        $bDone = false;
        $sActiveCategoryId = $oActiveCategory->id;

        do {
            // is there another order by set for this category or any of its parents
            $query = "SELECT `shop_category`.`id`, `shop_category`.`url_path`, `shop_category`.`shop_category_id`,
                               `shop_article_catalog_conf_default_order`.`shop_module_articlelist_orderby_id`
                          FROM `shop_category`
                     LEFT JOIN `shop_article_catalog_conf_default_order_shop_category_mlt` ON `shop_category`.`id` = `shop_article_catalog_conf_default_order_shop_category_mlt`.`target_id`
                     LEFT JOIN `shop_article_catalog_conf_default_order` ON (`shop_article_catalog_conf_default_order_shop_category_mlt`.`source_id` = `shop_article_catalog_conf_default_order`.`id` AND `shop_article_catalog_conf_default_order`.`shop_article_catalog_conf_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."')
                         WHERE `shop_category`.`id` = '".MySqlLegacySupport::getInstance()->real_escape_string($sActiveCategoryId)."'
                       ";
            if ($aCategoryDetails = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
                if (isset($aCategoryDetails['shop_module_articlelist_orderby_id']) && !empty($aCategoryDetails['shop_module_articlelist_orderby_id'])) {
                    $bDone = true;
                    $sDefaultOrderBy = $aCategoryDetails['shop_module_articlelist_orderby_id'];
                }
                $sActiveCategoryId = $aCategoryDetails['shop_category_id'];
                if (empty($sActiveCategoryId)) {
                    $bDone = true;
                }
            } else {
                $bDone = true;
            }
        } while (!$bDone);

        return $sDefaultOrderBy;
    }
}
