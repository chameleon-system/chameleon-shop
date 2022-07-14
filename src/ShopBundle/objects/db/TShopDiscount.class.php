<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopDiscount extends TShopDiscountAutoParent
{
    /**
     * constants below define the status returned when checking if a discount can be
     * used in the current basket.
     */

    /**
     * discount may be used.
     */
    const ALLOW_USE = 0;

    /**
     * the discount is not active (respects both the boolean field and the date fields).
     */
    const USE_ERROR_DISCOUNT_INACTIVE = 1;

    /**
     * the basket value is below the minimum value specified for the voucher series.
     */
    const USE_ERROR_BASKET_VALUE_TO_LOW = 2;

    /**
     * the discount has been restricted to a set of customers, and the current customer is not in that list.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER = 64;

    /**
     * the discount has been restricted to a set of customer groups, none of which the current customer is in.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP = 128;

    /**
     * the discount may not be used for the shipping country selected by the user.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER_SHIPPING_COUNTRY = 256;

    /**
     * flags can be used to enable/disable checks when calling AllowUseOfDiscount.
     */
    const DISABLE_CHECK_CURRENT_BASKET_VALUE = 1;
    const DISABLE_CHECK_CURRENT_BASKET_QUANTITY = 2;
    const DISABLE_CHECK_USER = 4;
    const DISABLE_CHECK_SHIPPING_COUNTRY = 8;

    /**
     * the real value of the discount used for an article. this value
     * is set from the controlling TShopBasketArticle.
     *
     * @var float
     */
    public $dRealValueUsed = 0;

    /**
     * return true if the discount is active.
     *
     * @return bool
     */
    public function IsActive()
    {
        $sToday = date('Y-m-d H:i:s');
        $bIsActive = ($this->fieldActive && ($this->fieldActiveFrom <= $sToday && ($this->fieldActiveTo >= $sToday || '0000-00-00 00:00:00' == $this->fieldActiveTo)));

        return $bIsActive;
    }

    /**
     * The method checks if the discount can be used by the active user and the active basket.
     *
     * @param int $iCheckFlag - send a bitmask to enable/disable individual checks. example - disable basket value and quantity check: TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_VALUE | TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_QUANTITY
     *
     * @return int
     * @psalm-return TdbShopDiscount::*
     */
    public function AllowUseOfDiscount($iCheckFlag = 0)
    {
        $bAllowUse = TdbShopDiscount::ALLOW_USE;

        $oBasket = TShopBasket::GetInstance();
        $oUser = TdbDataExtranetUser::GetInstance();

        // check if the series is active
        if (!$this->IsActive()) {
            $bAllowUse = TdbShopDiscount::USE_ERROR_DISCOUNT_INACTIVE;
        }

        // Check basket value
        $bCheckBasketValue = (0 == ($iCheckFlag & TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_VALUE)); // if we boolean or the disable bit and get zero, then the bit is not set
        if ($bCheckBasketValue) {
            if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
                // note: we are interested in the total product value (- produts that can not be used for the discount) - discounts used
                $dRelevantValue = $oBasket->GetBasketSumForDiscount($this) - $oBasket->dCostDiscounts;
                $bBelowMinValue = (0 != $this->fieldRestrictToValueFrom && $dRelevantValue < $this->fieldRestrictToValueFrom);
                $bAboveMaxValue = (0 != $this->fieldRestrictToValueTo && $dRelevantValue > $this->fieldRestrictToValueTo);
                if ($bBelowMinValue || $bAboveMaxValue || $dRelevantValue <= 0) {
                    $bAllowUse = TdbShopDiscount::USE_ERROR_BASKET_VALUE_TO_LOW;
                }
            }
        }

        // Check Basket quantity
        $bCheckBasketQuantity = (0 == ($iCheckFlag & TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_QUANTITY)); // if we boolean or the disable bit and get zero, then the bit is not set
        if ($bCheckBasketQuantity) {
            if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
                // note: we are interested in the total product value (- produts that can not be used for the discount) - discounts used
                $iNumberOfItemsAffected = $oBasket->GetBasketQuantityForDiscount($this);

                $bBelowMinValue = (0 != $this->fieldRestrictToArticlesFrom && $iNumberOfItemsAffected < $this->fieldRestrictToArticlesFrom);
                $bAboveMaxValue = (0 != $this->fieldRestrictToArticlesTo && $iNumberOfItemsAffected > $this->fieldRestrictToArticlesTo);
                if ($bBelowMinValue || $bAboveMaxValue) {
                    $bAllowUse = TdbShopDiscount::USE_ERROR_BASKET_VALUE_TO_LOW;
                }
            }
        }

        // discount restricted to a certain quantity of articles
        if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
        }

        // Check User
        $bCheckBasketUser = (0 == ($iCheckFlag & TdbShopDiscount::DISABLE_CHECK_USER)); // if we boolean or the disable bit and get zero, then the bit is not set
        if ($bCheckBasketUser) {
            // if the discount is restricted to some user, make sure this user is in that list
            if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
                $aUserListRestricton = $this->GetFieldDataExtranetUserWithInverseEmptySelectionLogicIdList();
                if (null === $aUserListRestricton || (count($aUserListRestricton) > 0 && !in_array($oUser->id, $aUserListRestricton))) {
                    $bAllowUse = TdbShopDiscount::USE_ERROR_NOT_VALID_FOR_CUSTOMER;
                }
            }

            // if the discount is restricted to some user group, make sure this user is in that list
            if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
                $aUserGroupListRestricton = $this->GetFieldDataExtranetGroupWithInverseEmptySelectionLogicIdList();
                if (null === $aUserGroupListRestricton || (count($aUserGroupListRestricton) > 0 && !$oUser->InUserGroups($aUserGroupListRestricton))) {
                    $bAllowUse = TdbShopDiscount::USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP;
                }
            }
        }

        // if the discount is restricted to some shipping country, make sure this user is in that list
        $bCheckShippingCountry = (0 == ($iCheckFlag & TdbShopDiscount::DISABLE_CHECK_SHIPPING_COUNTRY)); // if we boolean or the disable bit and get zero, then the bit is not set
        if ($bCheckShippingCountry) {
            if (TdbShopDiscount::ALLOW_USE == $bAllowUse) {
                $aShippingCountryListRestricton = $this->GetMLTIdList('data_country', 'data_country_mlt');

                if (count($aShippingCountryListRestricton) > 0) {
                    $oShippingAddress = $oUser->GetShippingAddress();
                    if (!in_array($oShippingAddress->fieldDataCountryId, $aShippingCountryListRestricton)) {
                        $bAllowUse = TdbShopDiscount::USE_ERROR_NOT_VALID_FOR_CUSTOMER_SHIPPING_COUNTRY;
                    }
                }
            }
        }

        return $bAllowUse;
    }

    /**
     * Returns the value of the discount - takes the current basket and user into consideration.
     *
     * @return float
     */
    public function GetValue()
    {
        $dValue = $this->fieldValue;
        $oBasket = TShopBasket::GetInstance();
        $dBasketValueApplicableForDiscount = $oBasket->GetBasketSumForDiscount($this);
        $dValue = $this->GetValueForBasketValue($dBasketValueApplicableForDiscount);

        return $dValue;
    }

    /**
     * like GetValue - but the method returns the value of the voucher for the basket value passed (ie you can use this method to simulate a basket value).
     *
     * @param float $dBasketValueApplicableForDiscount
     *
     * @return float
     */
    public function GetValueForBasketValue($dBasketValueApplicableForDiscount)
    {
        $dValue = $this->fieldValue;
        $oBasket = TShopBasket::GetInstance();
        if ('prozent' == $this->fieldValueType) {
            $dValue = round($dBasketValueApplicableForDiscount * ($dValue / 100), 2);
        }
        // now if the discount is worth more than the current basket, we need to use only the part that we can
        if ($dBasketValueApplicableForDiscount < $dValue) {
            $dValue = $dBasketValueApplicableForDiscount;
        }

        return $dValue;
    }

    /**
     * return true if the discount may be used for the article.
     *
     * @param TdbShopArticle $oArticle
     *
     * @return bool
     */
    public function AllowDiscountForArticle(TdbShopArticle $oArticle)
    {
        $bMayBeUsed = true;
        if ($oArticle->fieldExcludeFromDiscounts) {
            $bMayBeUsed = false;
        }

        // check article restrictions
        if ($bMayBeUsed) {
            $aArticleRestrictions = $this->GetFieldShopArticleWithInverseEmptySelectionLogicIdList();
            if (null === $aArticleRestrictions || (count($aArticleRestrictions) > 0 && !in_array($oArticle->id, $aArticleRestrictions))) {
                $bMayBeUsed = false;
            }
        }

        // check category restrictions
        if ($bMayBeUsed) {
            $aCategoryRestrictions = $this->GetFieldShopCategoryWithInverseEmptySelectionLogicIdList();
            if (null === $aCategoryRestrictions || (count($aCategoryRestrictions) > 0 && !$oArticle->IsInCategory($aCategoryRestrictions))) {
                $bMayBeUsed = false;
            }
        }

        return $bMayBeUsed;
    }

    /*
     * return true, if the discount may be used for the user passed. if no user is passed,
     * it must not have any user restrictions in order for the discount to pass the test
     * @param TdbDataExtranetUser $oUser
     * @return boolean
    */
    /**
     * @param TdbDataExtranetUser|null $oUser
     *
     * @return bool
     */
    public function AllowDiscountForUser($oUser)
    {
        $bIsValid = $this->AllowDiscountForNonUsers();
        if (!$bIsValid && $oUser) {
            $bIsValid = true;
            $aExtranetGroup = $this->GetFieldDataExtranetGroupWithInverseEmptySelectionLogicIdList();
            if (null === $aExtranetGroup) {
                $bIsValid = false;
            }
            if (true === $bIsValid && count($aExtranetGroup) > 0) {
                $aUserGroups = $oUser->GetMLTIdList('data_extranet_group_mlt');
                $aIntersection = array_intersect($aExtranetGroup, $aUserGroups);
                if (!is_array($aIntersection) || 0 == count($aIntersection)) {
                    $bIsValid = false;
                }
            }

            if ($bIsValid) {
                $aExtranetUser = $this->GetFieldDataExtranetUserWithInverseEmptySelectionLogicIdList();
                if (null === $aExtranetUser || (count($aExtranetUser) > 0 && !in_array($oUser->id, $aExtranetUser))) {
                    $bIsValid = false;
                }
            }
            if ($bIsValid) {
                $aExtranetUserCountry = $this->GetMLTIdList('data_country_mlt');
                if (count($aExtranetUserCountry) > 0) {
                    $oShipping = $oUser->GetShippingAddress();
                    if (!$oShipping || !in_array($oShipping->fieldDataCountryId, $aExtranetUserCountry)) {
                        $bIsValid = false;
                    }
                }
            }
        }

        return $bIsValid;
    }

    /**
     * return true, if the discount may be used by users not currently signed in
     * (ie. the discount has no group, user or country restrictions).
     *
     * @return bool
     */
    public function AllowDiscountForNonUsers()
    {
        $bIsValid = true;
        $aExtranetGroup = $this->GetFieldDataExtranetGroupWithInverseEmptySelectionLogicIdList();
        if (null === $aExtranetGroup || count($aExtranetGroup) > 0) {
            $bIsValid = false;
        }

        if ($bIsValid) {
            $aExtranetUser = $this->GetFieldDataExtranetUserWithInverseEmptySelectionLogicIdList();
            if (null === $aExtranetUser || count($aExtranetUser) > 0) {
                $bIsValid = false;
            }
        }
        if ($bIsValid) {
            $aExtranetUserCountry = $this->GetMLTIdList('data_country_mlt');
            if (count($aExtranetUserCountry) > 0) {
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    /*
     * returns true, if the discount is restricted to some basket values/content
     * @return boolean
    */
    /**
     * @return bool
     */
    public function HasBasketRestrictions()
    {
        $bRestrictedToBasket = (($this->fieldRestrictToArticlesFrom > 0) || ($this->fieldRestrictToArticlesTo > 0) || ($this->fieldRestrictToValueFrom > 0) || ($this->fieldRestrictToValueTo > 0));

        return $bRestrictedToBasket;
    }

    /**
     * return true if the discount is restricted to the basket content (such as article or article category)
     * @return bool
     */
    public function HasBasketContentRestrictions()
    {
        $bHasArticleRestriction = $this->GetFromInternalCache('bHasArticleRestriction');
        if (is_null($bHasArticleRestriction)) {
            $aCategoryRestrictons = $this->GetMLTIdList('shop_category_mlt');
            $bHasArticleRestriction = (count($aCategoryRestrictons) > 0);
            if (!$bHasArticleRestriction) {
                $aArticleRestrictons = $this->GetMLTIdList('shop_article_mlt');
                $bHasArticleRestriction = (count($aArticleRestrictons) > 0);
            }
            $this->SetInternalCache('bHasArticleRestriction', $bHasArticleRestriction);
        }

        return $bHasArticleRestriction;
    }

    /**
     * trigger a clear cache on all related articles.
     *
     * @return void
     */
    public function ClearCacheOnAllAffectedArticles()
    {
        $iStartOperation = time();
        $aArticleRestrictions = $this->GetMLTIdList('shop_article_mlt');
        foreach ($aArticleRestrictions as $sArticelId) {
            TCacheManager::PerformeTableChange('shop_article', $sArticelId);
        }

        $aCategoryRestrictons = $this->GetMLTIdList('shop_category_mlt');
        $databaseConnection = $this->getDatabaseConnection();
        if (count($aCategoryRestrictons) > 0) {
            $quotedCategoryRestrictions = implode(',', array_map(array($databaseConnection, 'quote'), $aCategoryRestrictons));
            $query = "SELECT `shop_article`.`id`
                    FROM `shop_article`
               LEFT JOIN `shop_article_shop_category_mlt` ON `shop_article`.`id` = `shop_article_shop_category_mlt`.`source_id`
                   WHERE `shop_article`.`shop_category_id` IN ($quotedCategoryRestrictions)
                      OR `shop_article_shop_category_mlt`.`target_id` IN ($quotedCategoryRestrictions)
                GROUP BY `shop_article`.`id`
                 ";
            $tRes = MySqlLegacySupport::getInstance()->query($query);
            while ($aRes = MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                TCacheManager::PerformeTableChange('shop_article', $aRes['id']);
            }
        }

        if (0 === count($aArticleRestrictions) && 0 === count($aCategoryRestrictons)) {
            TCacheManager::PerformeTableChange('shop_article', null);
        }

        $databaseConnection->update($this->table, array(
            'cache_clear_last_executed' => date('Y-m-d H:i:s', $iStartOperation),
        ), array(
            'id' => $this->id,
        ));
    }
}
