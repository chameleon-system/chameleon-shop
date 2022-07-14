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
 * @extends TCMSInterfaceManagerBaseExportCSV<TdbShopOrderItem>
 */
class TShopInterfaceExportArticleSummary extends TCMSInterfaceManagerBaseExportCSV
{
    /**
     * OVERWRITE THIS TO FETCH THE DATA. MUST RETURN A TCMSRecordList.
     *
     * @return TdbShopArticleList
     */
    protected function GetDataList()
    {
        $query = 'select shop_article.*,
                         shop_article_stats.stats_sales,
                         shop_article_stats.stats_detail_views,
                         shop_article_stats.stats_review_average,
                         shop_article_stats.stats_review_count
                   FROM shop_article
              LEFT JOIN shop_article_stats ON shop_article.id = shop_article_stats.shop_article_id';

        return TdbShopArticleList::GetList($query);
    }

    /**
     * OVERWRITE THIS IF YOU NEED TO ADD ANY OTHER DATA TO THE ROW OBJECT.
     *
     * @param TdbShopOrderItem $oDataObjct
     *
     * @return array
     */
    protected function GetExportRowFromDataObject(&$oDataObjct)
    {
        $aRow = parent::GetExportRowFromDataObject($oDataObjct);
        $oLocale = &TCMSLocal::GetActive();
        $aRow['stats_review_average'] = $oLocale->FormatNumber($aRow['stats_review_average'], 2);

        return $aRow;
    }

    protected function GetFieldMapping()
    {
        $aFields = array(
            'articlenumber' => 'VARCHAR( 255 ) NOT NULL',
            'name' => 'VARCHAR( 255 ) NOT NULL',
            'stats_sales' => 'VARCHAR( 255 ) NOT NULL',
            'stats_detail_views' => 'VARCHAR( 255 ) NOT NULL',
            'stats_review_count' => 'VARCHAR( 255 ) NOT NULL',
            'stats_review_average' => 'VARCHAR( 255 ) NOT NULL',
        );

        return $aFields;
    }
}
