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
    public static function GetListForShopKeywords($iShopId, $aKeywordList, $iLanguageId = null)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        if (null === $iLanguageId) {
            $iLanguageId = self::getMyLanguageService()->getActiveLanguageId();
        }

        $quotedShopId = $connection->quote($iShopId);
        $quotedLanguageId = $connection->quote($iLanguageId);

        $quotedKeywordList = array_map(function ($keyword) use ($connection) {
            return $connection->quote($keyword);
        }, $aKeywordList);

        $queryFilter = "
        `shop_search_keyword_article`.`shop_id` = {$quotedShopId}
        AND `shop_search_keyword_article`.`name` IN (" . implode(',', $quotedKeywordList) . ")
        AND (`shop_search_keyword_article`.`cms_language_id` = {$quotedLanguageId} OR `shop_search_keyword_article`.`cms_language_id` = '')
    ";

        $query = self::GetDefaultQuery($iLanguageId, $queryFilter);

        return TdbShopSearchKeywordArticleList::GetList($query, $iLanguageId);
    }
}
