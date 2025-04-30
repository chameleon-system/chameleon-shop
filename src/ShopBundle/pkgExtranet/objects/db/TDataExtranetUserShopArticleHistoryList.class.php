<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TDataExtranetUserShopArticleHistoryList extends TAdbDataExtranetUserShopArticleHistoryList
{
    /**
     * @static
     *
     * @param int $iNumberOfRecords
     * @param string $sExtranetUserId
     *
     * @return void
     *
     * @psalm-param positive-int $iNumberOfRecords
     */
    public static function ReducedListForUser($iNumberOfRecords, $sExtranetUserId)
    {
        $oList = TdbDataExtranetUserShopArticleHistoryList::GetListForDataExtranetUserId($sExtranetUserId);
        $oList->ChangeOrderBy(['`data_extranet_user_shop_article_history`.`datecreated`' => 'DESC', '`data_extranet_user_shop_article_history`.`cmsident`' => 'DESC']);
        $iRows = $oList->Length();

        $iRemove = $iRows - $iNumberOfRecords;
        if ($iRemove > 0) {
            $oList->GoToEnd();
            while ($iRemove > 0 && ($oItem = $oList->Previous())) {
                --$iRemove;
                $oItem->Delete();
            }
        }
    }
}
