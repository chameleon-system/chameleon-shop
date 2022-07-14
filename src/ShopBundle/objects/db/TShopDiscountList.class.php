<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopDiscountList extends TShopDiscountListAutoParent
{
    /**
     * return all discounts that are currently set to active.
     *
     * @param string $sFilter
     * @param string $sOrder
     *
     * @return TdbShopDiscountList
     */
    public static function GetActiveDiscountList($sFilter = null, $sOrder = '`shop_discount`.`position`')
    {
        static $aActiveDiscountList = array();
        $aKey = array('sFilter' => $sFilter, 'sOrder' => $sOrder);
        $sKey = TCacheManager::GetKey($aKey);
        if (!array_key_exists($sKey, $aActiveDiscountList)) {
            $now = MySqlLegacySupport::getInstance()->real_escape_string(date('Y-m-d H:i:s'));
            if (!empty($sOrder)) {
                $sOrder = "ORDER BY {$sOrder}";
            }
            if (is_null($sFilter)) {
                $sFilter = '';
            } else {
                $sFilter = " AND ({$sFilter})";
            }
            $query = "SELECT *
                    FROM `shop_discount`
                   WHERE `shop_discount`.`active` = '1'
                     AND (`shop_discount`.`active_from` <= '{$now}' AND (`shop_discount`.`active_to` >= '{$now}' || `shop_discount`.`active_to` = '0000-00-00 00:00:00'))
                         {$sFilter}
                         {$sOrder}
                 ";
            $aActiveDiscountList[$sKey] = parent::GetList($query);
            $aActiveDiscountList[$sKey]->bAllowItemCache = true;
        } else {
            $aActiveDiscountList[$sKey]->GoToStart();
        }

        return $aActiveDiscountList[$sKey];
    }

    /**
     * return all discounts matching the article
     * @param TdbShopArticle $oArticle
     * @param bool $bIncludeUserRestrictedDiscounts - set to true, if you want to include discounts restricted to the current user
     *
     * @return TIterator<TdbShopDiscount>
     */
    public static function GetActiveDiscountListForArticle(&$oArticle, $bIncludeUserRestrictedDiscounts = false)
    {
        $oDiscountList = new TIterator();
        if (false == $oArticle->fieldExcludeFromDiscounts) {
            $oDiscountListComplete = TdbShopDiscountList::GetActiveDiscountList();
            $oUser = null;
            if ($bIncludeUserRestrictedDiscounts) {
                $oUser = TdbDataExtranetUser::GetInstance();
            }
            while ($oDiscount = $oDiscountListComplete->Next()) {
                $bIsAbsoluteArticle = ('absolut' == $oDiscount->fieldValueType);
                $bShowDiscountOnArticleDetailPage = $oDiscount->fieldShowDiscountOnArticleDetailpage;

                $bAllowUseOfDiscount = (false == $bIsAbsoluteArticle && true == $bShowDiscountOnArticleDetailPage && true == $oDiscount->AllowDiscountForArticle($oArticle) && true == $oDiscount->AllowDiscountForUser($oUser) && false == $oDiscount->HasBasketRestrictions());
                if ($bAllowUseOfDiscount) {
                    $oDiscountList->AddItem($oDiscount);
                }
            }
        }

        return $oDiscountList;
    }
}
