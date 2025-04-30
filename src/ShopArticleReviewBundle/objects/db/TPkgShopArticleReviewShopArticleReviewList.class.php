<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopArticleReviewShopArticleReviewList extends TPkgShopArticleReviewShopArticleReviewListAutoParent
{
    /**
     * Get reviews for aarticel sorted by positive rate.
     *
     * @param string $iShopArticleId
     *
     * @return TdbShopArticleReviewList
     */
    public static function GetReviewsForArticleSortedByRate($iShopArticleId)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedArticleId = $connection->quote($iShopArticleId);

        $sQuery = "
        SELECT * 
          FROM `shop_article_review`
         WHERE `shop_article_review`.`shop_article_id` = {$quotedArticleId}
           AND `shop_article_review`.`publish` = '1'
         ORDER BY `shop_article_review`.`helpful_count` DESC, `shop_article_review`.`datecreated` DESC
    ";

        $oList = TdbShopArticleReviewList::GetList($sQuery);

        return $oList;
    }
}
