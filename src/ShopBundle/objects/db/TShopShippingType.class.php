<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\DataAccess\ShopShippingTypeDataAccessInterface;

class TShopShippingType extends TShopShippingTypeAutoParent
{
    /**
     * @var float|null
     */
    protected $dPrice;

    /**
     * holds a pointer to every entry in the basket that is affected by the shipping type.
     *
     * @var TShopBasketArticleList
     */
    protected $oAffectedBasketArticles;

    /**
     * return true if this shipping type is valid for the current user / basket.
     *
     * @return bool
     */
    public function IsAvailable()
    {
        $bIsValid = true;
        $bIsValid = ($bIsValid && $this->IsActive());
        $bIsValid = ($bIsValid && $this->isValidForCurrentPortal());
        $bIsValid = ($bIsValid && $this->IsValidForCurrentUser());
        $bIsValid = ($bIsValid && $this->IsValidForBasket());

        return $bIsValid;
    }

    /**
     * @return bool
     */
    public function endShippingTypeChain()
    {
        return true === $this->fieldEndShippingTypeChain;
    }

    /**
     * returns true if the group has no user or user group restriction.
     *
     * @return bool
     */
    public function IsPublic()
    {
        $bIsPublic = true;

        if (!$this->IsActive()) {
            $bIsPublic = false;
        }

        if ($bIsPublic) {
            $oGroups = $this->GetFieldDataExtranetGroupList();
            if ($oGroups->Length() > 0) {
                $bIsPublic = false;
            }
        }

        if ($bIsPublic) {
            $oUsers = $this->GetFieldDataExtranetUserList();
            if ($oUsers->Length() > 0) {
                $bIsPublic = false;
            }
        }

        return $bIsPublic;
    }

    /**
     * returns true if the shipping group is marked as active for the current time.
     *
     * @return bool
     */
    public function IsActive()
    {
        $bIsActive = false;
        $sToday = date('Y-m-d H:i:s');
        if ($this->fieldActive && $this->fieldActiveFrom <= $sToday && ('0000-00-00 00:00:00' == $this->fieldActiveTo || $this->fieldActiveTo >= $sToday)) {
            $bIsActive = true;
        }

        return $bIsActive;
    }

    /**
     * return true if the shipping group is allowed for the current user.
     *
     * @param bool $bCheckShippingCountry
     *
     * @return bool
     */
    public function IsValidForCurrentUser($bCheckShippingCountry = true)
    {
        $bIsValidForUser = false;
        $bIsValidGroup = false;
        $bIsValidShippingCountry = false;

        $oUser = TdbDataExtranetUser::GetInstance();

        if ($this->fieldRestrictToSignedInUsers || $oUser->IsLoggedIn() || $oUser->HasData()) {
            $typeDataAccess = $this->getShopShippingTypeDataAccess();
            if ($oUser->IsLoggedIn() || $oUser->HasData()) {
                // check user group
                $aUserGroups = $typeDataAccess->getPermittedUserGroupIds($this->id);
                if (!is_array($aUserGroups) || count($aUserGroups) < 1) {
                    $bIsValidGroup = true;
                } else {
                    $bIsValidGroup = $oUser->InUserGroups($aUserGroups);
                }

                // now check user id
                if ($bIsValidGroup) {
                    $aUserList = $typeDataAccess->getPermittedUserIds($this->id);
                    if (!is_array($aUserList) || count($aUserList) < 1) {
                        $bIsValidForUser = true;
                    } else {
                        $bIsValidForUser = in_array($oUser->id, $aUserList);
                    }
                }

                // check shipping country
                if ($bCheckShippingCountry) {
                    if ($bIsValidForUser && $bIsValidGroup) {
                        $oShippingAddress = $oUser->GetShippingAddress();
                        $iShippingCountryId = $oShippingAddress->fieldDataCountryId;
                        if (!$oShippingAddress->ContainsData()) {
                            $oBillingAddress = $oUser->GetBillingAddress();
                            $iShippingCountryId = $oBillingAddress->fieldDataCountryId;
                        }
                        $bIsValidShippingCountry = $this->IsValidForCountry($iShippingCountryId);
                    }
                } else {
                    $bIsValidShippingCountry = true;
                }
            }
        } elseif (!$this->fieldRestrictToSignedInUsers && !$oUser->IsLoggedIn()) {
            // not logged in, but set not restricted to signed in users
            $bIsValidForUser = true;
            $bIsValidGroup = true;
            $bIsValidShippingCountry = true;
        }

        return $bIsValidForUser && $bIsValidGroup && $bIsValidShippingCountry;
    }

    /**
     * @param string|null $sDataCountryId
     *
     * @return bool
     */
    public function IsValidForCountry($sDataCountryId)
    {
        $bIsValidForCountry = false;
        $aShippingCountryRestriction = $this->getShopShippingTypeDataAccess()->getPermittedCountryIds($this->id);
        if (count($aShippingCountryRestriction) > 0) {
            if (in_array($sDataCountryId, $aShippingCountryRestriction)) {
                $bIsValidForCountry = true;
            }
        } else {
            $bIsValidForCountry = true;
        }

        return $bIsValidForCountry;
    }

    /**
     * return true if the shipping group is allowed for the current portal.
     * If no active portal was found and group was restricted to portal return false.
     *
     * @return bool
     */
    public function isValidForCurrentPortal()
    {
        $aPortalIdList = $this->getShopShippingTypeDataAccess()->getPermittedPortalIds($this->id);
        if (!is_array($aPortalIdList) || count($aPortalIdList) < 1) {
            $bIsValidForPortal = true;
        } else {
            $oActivePortal = $this->getPortalDomainService()->getActivePortal();
            if (null === $oActivePortal) {
                $bIsValidForPortal = false;
            } else {
                $bIsValidForPortal = in_array($oActivePortal->id, $aPortalIdList);
            }
        }

        return $bIsValidForPortal;
    }

    /**
     * checks if the current shipping type is available for the current basket
     * affected articles in the basket will be marked with the shipping type.
     *
     * @return bool
     */
    public function IsValidForBasket()
    {
        $bValidForBasket = false;
        $oArticles = $this->GetAffectedBasketArticles();
        $bValidForBasket = ($oArticles->Length() > 0);

        return $bValidForBasket;
    }

    /**
     * loads the affected basket articles into $this->oAffectedBasketArticles
     * also returns list. will only load the articles if $this->oAffectedBasketArticles is null.
     *
     * @return TShopBasketArticleList
     */
    public function GetAffectedBasketArticles()
    {
        if (is_null($this->oAffectedBasketArticles)) {
            $this->oAffectedBasketArticles = null;
            $oBasket = TShopBasket::GetInstance();
            $this->oAffectedBasketArticles = $oBasket->GetArticlesAffectedByShippingType($this);
        }

        return $this->oAffectedBasketArticles;
    }

    /**
     * @return ShopShippingTypeDataAccessInterface
     */
    protected function getShopShippingTypeDataAccess()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_shipping_type_data_access');
    }

    /**
     * checks if a basket article should be affected by this shipping type
     * this can only happen if the article is not affected by any other shipping type.
     *
     * @return bool
     */
    public function ArticleAffected(TShopBasketArticle $oArticle)
    {
        $shopShippingTypeDataAccess = $this->getShopShippingTypeDataAccess();
        $bAffected = false;
        $oArticleShippingType = $oArticle->GetActingShippingType();
        // if the article is already marked with this shipping type, then we keep it
        if (!is_null($oArticleShippingType) && $oArticleShippingType->id == $this->id) {
            $bAffected = true;
        } elseif (is_null($oArticleShippingType)) {
            // article has no shipping type yet...
            // check article groups
            $bArticleGroupValid = false;
            $aGroupRestriction = $shopShippingTypeDataAccess->getPermittedArticleGroupIds($this->id);
            if (!is_array($aGroupRestriction) || count($aGroupRestriction) < 1) {
                $bArticleGroupValid = true;
            } else {
                $bArticleGroupValid = $oArticle->IsInArticleGroups($aGroupRestriction);
            }

            // check product categories
            $bArticleCategoryValid = false;
            if ($bArticleGroupValid) {
                $aCategoryRestriction = $shopShippingTypeDataAccess->getPermittedCategoryIds($this->id);
                if (!is_array($aCategoryRestriction) || count($aCategoryRestriction) < 1) {
                    $bArticleCategoryValid = true;
                } else {
                    $bArticleCategoryValid = $oArticle->IsInCategory($aCategoryRestriction);
                }
            }

            // check articles
            $bArticleValid = false;
            if ($bArticleGroupValid && $bArticleCategoryValid) {
                $aArticleRestriction = $shopShippingTypeDataAccess->getPermittedArticleIds($this->id);
                if (!is_array($aArticleRestriction) || count($aArticleRestriction) < 1) {
                    $bArticleValid = true;
                } else {
                    if (in_array($oArticle->id, $aArticleRestriction)) {
                        $bArticleValid = true;
                    }
                }
            }

            $bAffected = ($bArticleGroupValid && $bArticleCategoryValid && $bArticleValid);
        }

        return $bAffected;
    }

    /**
     * returns true if the list of articles is valid for the current shipping type (i.e. the number
     * of articles, weight, volume, etc).
     *
     * @return bool
     */
    public function ArticleListValidForShippingType(TShopBasketArticleList $oArticleList)
    {
        $dNumberOfArticles = $oArticleList->dNumberOfItems;
        $dArticleWeight = $oArticleList->dTotalWeight;
        $dArticleVolume = $oArticleList->dTotalVolume;

        if (true === $this->fieldValueBasedOnEntireBasket) {
            $oBasket = TShopBasket::GetInstance();
            $dArticleValue = $oBasket->dCostArticlesTotalAfterDiscounts;
        } else {
            $dArticleValue = 0;
            while ($tmpItem = $oArticleList->next()) {
                $dArticleValue += $tmpItem->dPriceTotalAfterDiscount;
            }
        }

        if ($this->fieldApplyToAllProducts) {
            $oBasket = TShopBasket::GetInstance();
            $dNumberOfArticles = $oBasket->dTotalNumberOfArticles;
            $dArticleWeight = $oBasket->dTotalWeight;
            $dArticleVolume = $oBasket->dTotalVolume;
        }

        $bValueOk = ($this->fieldRestrictToValueFrom <= $dArticleValue && (0 == $this->fieldRestrictToValueTo || $this->fieldRestrictToValueTo >= $dArticleValue));

        $bNumberOfArticlesOk = ($this->fieldRestrictToArticlesFrom <= $dNumberOfArticles && (0 == $this->fieldRestrictToArticlesTo || $this->fieldRestrictToArticlesTo >= $dNumberOfArticles));

        $bArticleWeightOk = ($this->fieldRestrictToWeightFrom <= $dArticleWeight && (0 == $this->fieldRestrictToWeightTo || $this->fieldRestrictToWeightTo >= $dArticleWeight));

        $bArticleVolumeOk = ($this->fieldRestrictToVolumeFrom <= $dArticleVolume && (0 == $this->fieldRestrictToVolumeTo || $this->fieldRestrictToVolumeTo >= $dArticleVolume));

        $bIsValid = ($bValueOk && $bNumberOfArticlesOk && $bArticleWeightOk && $bArticleVolumeOk);

        return $bIsValid;
    }

    /**
     * return shipping type cost.
     *
     * @return float
     */
    public function GetPrice()
    {
        if (is_null($this->dPrice)) {
            $this->dPrice = 0;

            // price based on basket
            if ($this->fieldValueBasedOnEntireBasket) {
                $oBasket = TShopBasket::GetInstance();
                $iTotalNumberOfArticlesInBasketThatAreExcludedFromShippingCostCalculation = 0;
                $iTotalDiscountedPriceOfArticlesInBasketThatAreExcludedFromShippingCostCalculation = 0;
                $oItemList = $oBasket->GetBasketContents();
                $oItemList->GoToStart();
                while ($oItem = $oItemList->Next()) {
                    if ($oItem->fieldExcludeFromShippingCostCalculation) {
                        $iTotalNumberOfArticlesInBasketThatAreExcludedFromShippingCostCalculation += $oItem->dAmount;
                        $iTotalDiscountedPriceOfArticlesInBasketThatAreExcludedFromShippingCostCalculation += $oItem->dPriceTotalAfterDiscount;
                    }
                }
                $oItemList->GoToStart();

                if ('absolut' == $this->fieldValueType) {
                    $this->dPrice = $this->fieldValue;
                    if ($this->fieldAddValueForEachArticle
                        || ($iTotalNumberOfArticlesInBasketThatAreExcludedFromShippingCostCalculation > 0
                         && $oBasket->dTotalNumberOfArticles == $iTotalNumberOfArticlesInBasketThatAreExcludedFromShippingCostCalculation
                        )
                    ) {
                        $this->dPrice = ($oBasket->dTotalNumberOfArticles - $iTotalNumberOfArticlesInBasketThatAreExcludedFromShippingCostCalculation) * $this->dPrice;
                    }
                } else {
                    $this->dPrice = round($this->ApplyPriceModifiers(($oBasket->dCostArticlesTotalAfterDiscounts - $iTotalDiscountedPriceOfArticlesInBasketThatAreExcludedFromShippingCostCalculation) * ($this->fieldValue / 100)), 2);
                }
            } else {
                // price based on current list
                /** @var $oArticleList TShopBasketArticleList */
                $oArticleList = $this->GetAffectedBasketArticles();
                if (!is_null($oArticleList)) {
                    $iTotalNumberOfArticlesInListThatAreExcludedFromShippingCostCalculation = 0;
                    $iTotalDiscountedPriceOfArticlesInListThatAreExcludedFromShippingCostCalculation = 0;
                    $oArticleList->GoToStart();
                    while ($oArticle = $oArticleList->Next()) {
                        if ($oArticle->fieldExcludeFromShippingCostCalculation) {
                            $iTotalNumberOfArticlesInListThatAreExcludedFromShippingCostCalculation += $oArticle->dAmount;
                            $iTotalDiscountedPriceOfArticlesInListThatAreExcludedFromShippingCostCalculation += $oArticle->dPriceTotalAfterDiscount;
                        }
                    }
                    $oArticleList->GoToStart();

                    if ('absolut' == $this->fieldValueType) {
                        $this->dPrice = $this->fieldValue;
                        if ($this->fieldAddValueForEachArticle
                            || ($iTotalNumberOfArticlesInListThatAreExcludedFromShippingCostCalculation > 0
                             && $oArticleList->dNumberOfItems == $iTotalNumberOfArticlesInListThatAreExcludedFromShippingCostCalculation
                            )
                        ) {
                            $this->dPrice = ($oArticleList->dNumberOfItems - $iTotalNumberOfArticlesInListThatAreExcludedFromShippingCostCalculation) * $this->dPrice;
                        }
                    } else {
                        $this->dPrice = round($this->ApplyPriceModifiers(($oArticleList->dProductPrice - $iTotalDiscountedPriceOfArticlesInListThatAreExcludedFromShippingCostCalculation) * ($this->fieldValue / 100)), 2);
                    }
                }
            }
        }

        return $this->dPrice;
    }

    /**
     * @param TShopBasketArticleList $oBasketArticleList
     *
     * @return float|int
     */
    public function GetPriceForBasketArticleList($oBasketArticleList)
    {
        if (is_null($this->dPrice)) {
            $this->dPrice = 0;

            $dTotalNumberOfArticles = 0;
            $oBasketArticleList->GoToStart();
            while ($oBasketArticle = $oBasketArticleList->Next()) {
                /* @var $oBasketArticle TShopBasketArticle* */
                $dTotalNumberOfArticles += $oBasketArticle->dAmount;
            }
            $dCostArticlesTotal = 0;
            $oBasketArticleList->GoToStart();
            while ($oBasketArticle = $oBasketArticleList->Next()) {
                /* @var $oBasketArticle TShopBasketArticle* */
                $dCostArticlesTotal += $oBasketArticle->dPriceTotal;
            }

            // price based on basket
            if ($this->fieldValueBasedOnEntireBasket) {
                if ('absolut' == $this->fieldValueType) {
                    $this->dPrice = $this->fieldValue;
                    if ($this->fieldAddValueForEachArticle) {
                        $this->dPrice = $dTotalNumberOfArticles * $this->dPrice;
                    }
                } else {
                    $this->dPrice = $this->ApplyPriceModifiers($dCostArticlesTotal * ($this->fieldValue / 100));
                }
            } else {
                // price based on current list
                if (!is_null($oBasketArticleList)) {
                    if ('absolut' == $this->fieldValueType) {
                        $this->dPrice = $this->fieldValue;
                        if ($this->fieldAddValueForEachArticle) {
                            $this->dPrice = $dTotalNumberOfArticles * $this->dPrice;
                        }
                    } else {
                        $this->dPrice = $this->ApplyPriceModifiers($dCostArticlesTotal * ($this->fieldValue / 100));
                    }
                }
            }
        }

        return $this->dPrice;
    }

    /**
     * Applies additional price modifiers as defined in the shipping type
     * record.
     *
     * @param float $dPrice
     *
     * @return float $dPrice
     */
    protected function ApplyPriceModifiers($dPrice)
    {
        if ($this->fieldValueMin > 0 && $dPrice < $this->fieldValueMin) {
            $dPrice = $this->fieldValueMin;
        }
        if ($this->fieldValueMax > 0 && $dPrice > $this->fieldValueMax) {
            $dPrice = $this->fieldValueMax;
        }
        if ($this->fieldValueAdditional > 0) {
            $dPrice = $dPrice + $this->fieldValueAdditional;
        }

        return $dPrice;
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
