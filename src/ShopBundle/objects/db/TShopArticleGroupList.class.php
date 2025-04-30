<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopArticleGroupList extends TAdbShopArticleGroupList
{
    /**
     * return article group list for an article.
     *
     * @param int $iArticleId
     *
     * @return TdbShopArticleGroupList
     */
    public static function GetArticleGroups($iArticleId)
    {
        /* @var $connection \Doctrine\DBAL\Connection */
        $connection = ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $quotedArticleId = $connection->quote($iArticleId);

        $query = "
        SELECT `shop_article_group`.*
          FROM `shop_article_group`
    INNER JOIN `shop_article_article_group_mlt`
            ON `shop_article_group`.`id` = `shop_article_article_group_mlt`.`target_id`
         WHERE `shop_article_article_group_mlt`.`source_id` = {$quotedArticleId}
      ORDER BY `shop_article_group`.`name`
    ";

        return TdbShopArticleGroupList::GetList($query);
    }

    /**
     * returns the max vat from the group list.
     *
     * @return TdbShopVat|null
     */
    public function GetMaxVat()
    {
        $iPointer = $this->getItemPointer();
        $oMaxVatItem = null;
        $this->GoToStart();
        while ($oItem = $this->Next()) {
            $oCurrentVat = $oItem->GetVat();
            if (!is_null($oCurrentVat)) {
                if (is_null($oMaxVatItem)) {
                    $oMaxVatItem = $oCurrentVat;
                } elseif ($oMaxVatItem->fieldVatPercent < $oCurrentVat->fieldVatPercent) {
                    $oMaxVatItem = $oCurrentVat;
                }
            }
        }
        $this->setItemPointer($iPointer);

        return $oMaxVatItem;
    }
}
