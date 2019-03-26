<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopSearchKeywordArticleList extends TAdbShopSearchKeywordArticleList
{
    /**
     * return list for a set of keywords.
     *
     * @param int   $iShopId
     * @param array $aKeywordList
     * @param int   $iLanguageId
     *
     * @return TdbShopSearchKeywordArticleList
     */
    public static function &GetListForShopKeywords($iShopId, $aKeywordList, $iLanguageId = null)
    {
        if (null === $iLanguageId) {
            $iLanguageId = self::getMyLanguageService()->getActiveLanguageId();
        }
        $aKeywordList = TTools::MysqlRealEscapeArray($aKeywordList);
        $query = self::GetDefaultQuery($iLanguageId, "`shop_search_keyword_article`.`shop_id`= '".MySqlLegacySupport::getInstance()->real_escape_string($iShopId)."' AND `shop_search_keyword_article`.`name` IN ('".implode("','", $aKeywordList)."') AND (`shop_search_keyword_article`.`cms_language_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iLanguageId)."' OR `shop_search_keyword_article`.`cms_language_id` = '') ");

        return TdbShopSearchKeywordArticleList::GetList($query, $iLanguageId);
    }
}
