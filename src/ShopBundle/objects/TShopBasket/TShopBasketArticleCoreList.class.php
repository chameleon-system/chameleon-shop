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
 * The TIterator based article list overwrites the standard add item method in such a way that if
 * the same article is added again, it simply increases the amout of that item. same goes for the
 * remove function.
 *
 * @extends TIterator<TShopBasketArticle>
 */
class TShopBasketArticleCoreList extends TIterator
{
    const VIEW_PATH = 'pkgShop/views/TShopBasket/TShopBasketArticleList';

    /**
     * the sume of all amount fields in the basket (ie total number of articles).
     *
     * @var float
     */
    public $dNumberOfItems = 0;

    /**
     * the original product price of all articles in the list (before discounts, vouchers, etc).
     *
     * @var float
     */
    public $dProductPrice = 0;

    /**
     * total weight of all articles in the list.
     *
     * @var float
     */
    public $dTotalWeight = 0;

    /**
     * total volume of all articles in the list.
     *
     * @var float
     */
    public $dTotalVolume = 0;

    /**
     * list of observing objects.
     *
     * @var array
     */
    private $aObservers = array();

    /**
     * register an observer with the user.
     *
     * @param string                    $sObserverName
     * @param IDataExtranetUserObserver $oObserver
     *
     * @return void
     */
    public function ObserverRegister($sObserverName, &$oObserver)
    {
        if (!array_key_exists($sObserverName, $this->aObservers)) {
            $this->aObservers[$sObserverName] = &$oObserver;
        }
    }

    /**
     * remove an observer from the list.
     *
     * @param string $sObserverName
     *
     * @return void
     */
    public function ObserverUnregister($sObserverName)
    {
        if (array_key_exists($sObserverName, $this->aObservers)) {
            unset($this->aObservers[$sObserverName]);
        }
    }

    /**
     * check list for dead articles.
     *
     * @return void
     */
    public function Refresh()
    {
        $iPt = $this->getItemPointer();
        $this->GoToStart();
        while ($oTmp = $this->Next()) {
            if (false === $oTmp->sqlData || ($oTmp->dAmount <= 0)) {
                $this->RemoveArticle($oTmp);
            } else {
                // refresh stock from db
                $oTmp->RefreshDataFromDatabase();
            }
        }
        if ($iPt > $this->Length()) {
            $iPt = $this->Length();
        }
        $this->setItemPointer($iPt);
        $this->UpdateListData();
    }

    /**
     * returns array with pointers to all basket articles that match the shipping type.
     *
     * @param TdbShopShippingType $oShippingType
     *
     * @return TShopBasketArticleList
     */
    public function &GetArticlesAffectedByShippingType(TdbShopShippingType &$oShippingType)
    {
        $oArticles = new TShopBasketArticleList();
        /** @var $oArticles TShopBasketArticleList */
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            if ($oShippingType->ArticleAffected($oItem)) {
                $oArticles->AddItem($oItem);
            }
        }
        if ($oArticles->Length() > 0 && $oShippingType->fieldApplyToAllProducts) {
            // this shipping type should match ALL articles... so we now work with all articles
            $this->GoToStart();
            $oArticles = new TShopBasketArticleList();
            /** @var $oArticles TShopBasketArticleList */
            while ($oItem = &$this->Next()) {
                $oArticles->AddItem($oItem);
            }
        }
        // if at least some items matched, we now need to check if the sume of the items matches the shipping type requirement
        if ($oShippingType->ArticleListValidForShippingType($oArticles)) {
            // we keep the list. we now need to mark all items in the list with that shipping type
            $oArticles->GoToStart();
            while ($oItem = &$oArticles->Next()) {
                $this->SetShippingTypeForArticle($oItem, $oShippingType);
            }
            $oArticles->GoToStart();
        } else {
            // does not match... so reset the list
            $oArticles = new TShopBasketArticleList();
            /** @var $oArticles TShopBasketArticleList */
        }

        $this->setItemPointer($iPointer);

        return $oArticles;
    }

    /**
     * return list matchin the vat group.
     *
     * @param TdbShopVat $oVat
     *
     * @return TShopBasketArticleList
     */
    public function &GetListMatchingVat(TdbShopVat &$oVat)
    {
        $oArticles = new TShopBasketArticleList();
        /** @var $oArticles TShopBasketArticleList */
        $iPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            $oItemVat = $oItem->GetVat();
            if (!is_null($oItemVat) && $oItemVat->id == $oVat->id) {
                $oArticles->AddItem($oItem);
            }
        }

        $this->setItemPointer($iPointer);

        return $oArticles;
    }

    /**
     * marks the item in the list with the shipping type.
     *
     * @param TShopBasketArticle  $oItem
     * @param TdbShopShippingType $oShippingType
     *
     * @return void
     */
    protected function SetShippingTypeForArticle(TShopBasketArticle &$oItem, TdbShopShippingType &$oShippingType)
    {
        $iCurrentPos = $this->getItemPointer();
        $this->GoToStart();
        $bFound = false;
        while (!$bFound && ($oExistingItem = &$this->Next())) {
            /** @var $oExistingItem TShopBasketArticle */
            if ($oExistingItem->IsSameAs($oItem)) {
                $bFound = true;
            }
        }
        if ($bFound) {
            $oExistingItem->SetActingShippingType($oShippingType);
        }
        $this->setItemPointer($iCurrentPos);
    }

    /**
     * add a TShopBasketArticle to the article list. if such an item is already in the basket, we just add the amount
     * instead of inserting a new entry.
     * returns true if the item was added, false if it was removed (happens if the amount falls below zero).
     *
     * @param TShopBasketArticle $oItem
     *
     * @return bool
     */
    public function AddItem(&$oItem)
    {
        $bWasAdded = true;
        $iCurrentPos = $this->getItemPointer();
        $this->GoToStart();
        $bFound = false;
        while (!$bFound && ($oExistingItem = &$this->Next())) {
            /** @var $oExistingItem TShopBasketArticle */
            if ($oExistingItem->IsSameAs($oItem)) {
                $bFound = true;
            }
        }
        if ($bFound) {
            $oExistingItem->ChangeAmount($oExistingItem->dAmount + $oItem->dAmount);
            // if the new amount is less than or equal to zero, we remove the item
            if ($oExistingItem->dAmount <= 0) {
                $this->RemoveArticle($oExistingItem);
                $bWasAdded = false;
            } else {
                $this->PostUpdateItemHook($oExistingItem);
            }
        } else {
            parent::AddItem($oItem);
            $this->PostUpdateItemHook($oItem);
        }
        $this->setItemPointer($iCurrentPos);
        $this->UpdateListData();

        return $bWasAdded;
    }

    /**
     * return true if the item is in the list (uses the IsSameAs method).
     *
     * @param TShopBasketArticle $oItem
     *
     * @return bool
     */
    public function IsInList($oItem)
    {
        $bIsInList = false;
        $iCurPos = $this->getItemPointer();
        $this->GoToStart();
        while (!$bIsInList && ($oTmpItem = &$this->Next())) {
            if ($oTmpItem->id == $oItem->id) {
                $bIsInList = true;
            }
        }
        $this->setItemPointer($iCurPos);

        return $bIsInList;
    }

    /**
     * changes the amount of the requested item. if the amount drops to zero, the item is removed
     * if the item does not exists, it is added
     * returns true if the item was added, false if it was removed (amount fell below zero).
     *
     * @param TShopBasketArticle $oItem
     *
     * @return bool
     */
    public function UpdateItemAmount(TShopBasketArticle &$oItem)
    {
        $bWasUpdated = false;
        if ($oItem->dAmount <= 0) {
            $this->RemoveArticle($oItem);
        } else {
            // see if we can find the item
            $iCurrentPos = $this->getItemPointer();
            $this->GoToStart();
            $bFound = false;
            $oExistingItem = null;
            while (!$bFound && ($oExistingItem = &$this->Next())) {
                /** @var $oExistingItem TShopBasketArticle */
                if ($oExistingItem->IsSameAs($oItem)) {
                    $bFound = true;
                }
            }
            if ($bFound) {
                $oExistingItem->ChangeAmount($oItem->dAmount);
                if ($oExistingItem->dAmount <= 0) {
                    $this->RemoveArticle($oExistingItem);
                } else {
                    $this->PostUpdateItemHook($oExistingItem);
                }
            } else {
                parent::AddItem($oItem);
                $this->PostUpdateItemHook($oItem);
            }

            $this->setItemPointer($iCurrentPos);
            $bWasUpdated = true;
        }
        $this->UpdateListData();

        return $bWasUpdated;
    }

    /**
     * overwrote the method so that the id could type hint properly.
     *
     * @return TShopBasketArticle|false
     */
    public function &next()
    {
        return parent::Next();
    }

    /**
     * used to update class data wenn the class state changes.
     *
     * @return void
     */
    protected function UpdateListData()
    {
        $this->dNumberOfItems = 0;
        $this->dProductPrice = 0;

        $this->dTotalWeight = 0;
        $this->dTotalVolume = 0;
        $tmpPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oItem = &$this->Next()) {
            if ($oItem->dAmount <= 0) {
                $this->RemoveArticle($oItem);
            } else {
                $this->dNumberOfItems += $oItem->dAmount;
                $this->dProductPrice += $oItem->dPriceTotal;

                $this->dTotalWeight += $oItem->dTotalWeight;
                $this->dTotalVolume += $oItem->dTotalVolume;
            }
        }
        $this->setItemPointer($tmpPointer);
    }

    /**
     * Searches for an Item matching the passed item, removes it from the list and returns it. if no item is found
     * the method will return null.
     *
     * @param TShopBasketArticle $oItem
     *
     * @return TShopBasketArticle|null
     */
    public function RemoveArticle(TShopBasketArticle &$oItem)
    {
        // find the item, then drop it
        $oItemToRemove = $this->FindItemWithProperty('sBasketItemKey', $oItem->sBasketItemKey);

        if (false === $oItemToRemove) {
            return null;
        }
        parent::RemoveItem('sBasketItemKey', $oItem->sBasketItemKey);
        $this->UpdateListData();
        $this->PostDeleteItemHook($oItemToRemove);

        return $oItemToRemove;
    }

    /**
     * @return float
     *
     * @param TdbShopVoucher $oVoucher
     *                                 returns the total basket value for alle articles that may be used for the voucher passed. the method takes
     *                                 active discounts into account
     */
    public function GetBasketSumForVoucher(TdbShopVoucher &$oVoucher)
    {
        // get the sum of all products (if the voucher is not sponsored, we exclude products with "ExcludeFromVouchers")
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();
        $oVoucherDef = &$oVoucher->GetFieldShopVoucherSeries();
        $dProductValue = 0;
        while ($oItem = &$this->Next()) {
            $bIncludeProduct = $oVoucher->AllowVoucherForArticle($oItem);
            if ($bIncludeProduct) {
                $dProductValue = $dProductValue + $oItem->dPriceTotalAfterDiscountWithoutVouchers;
            }
        }
        $this->setItemPointer($iCurrentPosition);

        return $dProductValue;
    }

    /**
     * distributes the voucher value over all items affected by teh voucher.
     * note: you should only use this for NONE sponsored vouchers (ie vouchers that have NOT been paid in real money).
     *
     * the methods changes the discounted and discounted total price of all items affected by the voucher by the value of the voucher
     * since the discount may have a fixed value we may not be able to distribute the value of the voucher on the individual articles without
     * resorting to fractings with more then 2 digits precision. The following algorithm is used to minimize the change of larger then 2 digit fractions
     *
     * a) distribute the voucher value relative to item price over all affected items while making sure that the discounted item price has at most 2 digit precision
     * b) whatever value of the voucher can not be distributed this way is then - if possible distributed without taking
     *    the weighted distribution into account (ie. distributed where ever that is possible while keeping a 2 digit
     *    precision for discounted item prices
     * c) if we still have some value remaining after that, we find the item for which the discounted item price has to be changed the least in order to
     *    bring the total discount price of the item to such a value, that the remaining voucher value can be absorbed. This item will then have a precision
     *    grater than 2 digits for its discounted item price
     *
     * All this means is: the voucher value will be distributed over all affected items in such a way that the total discount values of the items sums
     *  to the voucher value with a precision of 2 while most but not all discounted item prices will also be 2 digit precision
     *
     * @param TdbShopVoucher $oVoucher
     * @param float $dVoucherValue
     *
     * @return void
     */
    public function ReducePriceForItemsAffectedByNoneSponsoredVoucher(TdbShopVoucher &$oVoucher, $dVoucherValue)
    {
        $currentPosition = $this->getItemPointer();
        $this->GoToStart();
        $articlesAffected = array();
        $totalValueOfAffectedItems = 0;
        while ($basketItem = $this->Next()) {
            if (true === $oVoucher->AllowVoucherForArticle($basketItem)) {
                $articlesAffected[] = $basketItem;
                $totalValueOfAffectedItems = $totalValueOfAffectedItems + $basketItem->dPriceTotalAfterDiscountWithoutVouchers;
            }
        }
        $this->setItemPointer($currentPosition);

        $this->correctBasketItemDiscountPrices($articlesAffected, $dVoucherValue, $totalValueOfAffectedItems);
    }

    /**
     * @param TdbShopDiscount $discount
     * @param float           $unusedDiscountValue value difference between discount based on single article and discount based on all articles
     */
    public function reducePriceForItemsAffectedByDiscount(\TdbShopDiscount $discount, float $unusedDiscountValue): void
    {
        $currentPosition = $this->getItemPointer();
        $this->GoToStart();
        $articlesAffected = array();
        $totalValueOfAffectedItems = 0;
        while ($basketItem = $this->Next()) {
            if (true === $discount->AllowDiscountForArticle($basketItem)) {
                $articlesAffected[] = $basketItem;
                $totalValueOfAffectedItems = $totalValueOfAffectedItems + $basketItem->dPriceTotalAfterDiscountWithoutVouchers;
            }
        }
        $this->setItemPointer($currentPosition);
        if ($totalValueOfAffectedItems <= 0.001) {
            return;
        }
        $this->correctBasketItemDiscountPrices($articlesAffected, $unusedDiscountValue, $totalValueOfAffectedItems);
    }

    protected function correctBasketItemDiscountPrices(array $articlesAffected, float $unusedDiscountValue, float $totalValueOfAffectedItems): void
    {
        if (0 === count($articlesAffected)) {
            return;
        }
        $dRemainingDiscountUse = $unusedDiscountValue;
        foreach ($articlesAffected as  $affectedArticle) {
            // total value used for the article is calculated based on the relative value of the article to the other articles
            // (200*(49/448.95))/1 =
            $dValueToUse = ($unusedDiscountValue * ($affectedArticle->dPriceTotalAfterDiscountWithoutVouchers / $totalValueOfAffectedItems)) / $affectedArticle->dAmount;
            $dValueToUse = $dValueToUse * 100;
            //the reason for adding the 0.5 in the next step is float precision error in php, see http://php.net/float
            $dValueToUse = intval(floor($dValueToUse + 0.5)) / 100;
            $dRealUse = $dValueToUse * $affectedArticle->dAmount;
            //round has to be done because php doesn't handle values correctly and returns true for identical values for ($dRealUse > $dRemainingDiscountUse)
            $dRealUse = round($dRealUse, 10);
            $dRemainingDiscountUse = round($dRemainingDiscountUse, 10);
            if ($dRealUse > $dRemainingDiscountUse) {
                $dValueToUse = floor(($dRemainingDiscountUse / $affectedArticle->dAmount) * 100) / 100; // prevent over spending
                $dRealUse = $dValueToUse * $affectedArticle->dAmount;
            }
            $dRemainingDiscountUse = $dRemainingDiscountUse - $dRealUse;
            $affectedArticle->dPriceAfterDiscount = $affectedArticle->dPriceAfterDiscount - $dValueToUse;
            $affectedArticle->dPriceTotalAfterDiscount = $affectedArticle->dPriceAfterDiscount * $affectedArticle->dAmount;
            $affectedArticle->dPriceAfterDiscountWithoutVouchers = $affectedArticle->dPriceAfterDiscount;
            $affectedArticle->dPriceTotalAfterDiscountWithoutVouchers = $affectedArticle->dPriceTotalAfterDiscount;
        }
        if ($dRemainingDiscountUse <= 0.001) {
            return;
        }
        // what ever is left over in $missingDiscountValue at this point, is removed from the first article that has a discounted value larger 0
        reset($articlesAffected);
        foreach ($articlesAffected as $affectedArticle) {
            if ($dRemainingDiscountUse > 0) {
                $dMaxUse = $articlesAffected[0]->dPriceTotalAfterDiscount;
                if ($dMaxUse >= $dRemainingDiscountUse) {
                    $dUsePerItem = floor(($dRemainingDiscountUse / $affectedArticle->dAmount) * 100) / 100;
                    if ($dUsePerItem > 0) {
                        $dRemainingDiscountUse = $dRemainingDiscountUse - $dUsePerItem * $affectedArticle->dAmount;
                        $affectedArticle->dPriceAfterDiscount = $affectedArticle->dPriceAfterDiscount - $dUsePerItem;
                        $affectedArticle->dPriceTotalAfterDiscount = $affectedArticle->dPriceAfterDiscount * $affectedArticle->dAmount;
                        $affectedArticle->dPriceAfterDiscountWithoutVouchers = $affectedArticle->dPriceAfterDiscount;
                        $affectedArticle->dPriceTotalAfterDiscountWithoutVouchers = $affectedArticle->dPriceTotalAfterDiscount;
                    }
                }
            }
        }

        if ($dRemainingDiscountUse <= 0.001) {
            return;
        }
        // we may still have some value remaining since the individual price may not split evenly into the items (if we use an item price)
        // we solve this by: change the price of the affected item that most closely matches the final value into such a fraction, that the total matches

        reset($articlesAffected);
        $dSmallestChange = null;
        $indexOfArticleWithSmallestChange = null;
        foreach ($articlesAffected as $iAffectedArticleIndex => $affectedArticle) {
            $dRequiredPositionPrice = ($affectedArticle->dPriceTotalAfterDiscount - $dRemainingDiscountUse) / $affectedArticle->dAmount;
            $dDiff = abs($dRequiredPositionPrice - $affectedArticle->dPriceAfterDiscount);
            if (null === $dSmallestChange || $dDiff < $dSmallestChange) {
                $dSmallestChange = $dDiff;
                $indexOfArticleWithSmallestChange = $iAffectedArticleIndex;
            }
        }
        reset($articlesAffected);
        if (null !== $indexOfArticleWithSmallestChange) {
            $dRequiredPositionPrice = ($articlesAffected[$indexOfArticleWithSmallestChange]->dPriceTotalAfterDiscount - $dRemainingDiscountUse) / $articlesAffected[$indexOfArticleWithSmallestChange]->dAmount;
            $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceAfterDiscount = $dRequiredPositionPrice;
            $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceTotalAfterDiscount = $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceTotalAfterDiscount - $dRemainingDiscountUse;
            $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceAfterDiscountWithoutVouchers = $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceAfterDiscount;
            $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceTotalAfterDiscountWithoutVouchers = $articlesAffected[$indexOfArticleWithSmallestChange]->dPriceTotalAfterDiscount;
        }
    }

    /**
     * returns the total number of items affected by a voucher.
     *
     * @param TdbShopVoucher $oVoucher
     *
     * @return float
     */
    public function GetBasketQuantityForVoucher(TdbShopVoucher &$oVoucher)
    {
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();
        $iAmount = 0;
        while ($oItem = &$this->Next()) {
            $bIncludeProduct = $oVoucher->AllowVoucherForArticle($oItem);
            if ($bIncludeProduct) {
                $iAmount = $iAmount + $oItem->dAmount;
            }
        }
        $this->setItemPointer($iCurrentPosition);

        return $iAmount;
    }

    /**
     * returns the total basket value for alle articles that may be used for the discount passed. the method takes
     * active discounts into account.
     *
     * @param TdbShopDiscount $oDiscount
     *
     * @return float
     */
    public function GetBasketSumForDiscount(TdbShopDiscount &$oDiscount)
    {
        // get the sum of all products (we exclude products with "fieldExcludeFromDiscounts")
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();
        $dProductValue = 0;
        while ($oItem = &$this->Next()) {
            $bIncludeProduct = $oDiscount->AllowDiscountForArticle($oItem);
            if ($bIncludeProduct) {
                $dProductValue = $dProductValue + $oItem->dPriceTotal;
            }
        }
        $this->setItemPointer($iCurrentPosition);

        return $dProductValue;
    }

    /**
     * returns the total number of items affected by a discount.
     *
     * @param TdbShopDiscount $oDiscount
     *
     * @return float
     */
    public function GetBasketQuantityForDiscount(TdbShopDiscount &$oDiscount)
    {
        // get the sum of all products (we exclude products with "fieldExcludeFromDiscounts")
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();
        $iAmount = 0;
        while ($oItem = &$this->Next()) {
            $bIncludeProduct = $oDiscount->AllowDiscountForArticle($oItem);
            if ($bIncludeProduct) {
                $iAmount = $iAmount + $oItem->dAmount;
            }
        }
        $this->setItemPointer($iCurrentPosition);

        return $iAmount;
    }

    /**
     * used to display the basket article list.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'mini', $sViewType = 'Core', $aCallTimeVars = array())
    {
        $oView = new TViewParser();

        $oShop = TdbShop::GetInstance();
        $oView->AddVar('oShop', $oShop);
        $oView->AddVar('oArticleList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * apply a discount to the basket item list.
     *
     * @param TdbShopDiscount $oDiscount
     *
     * @return void
     */
    public function ApplyDiscount(TdbShopDiscount $oDiscount)
    {
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();

        $dTotalDiscountValue = 0;
        $bIsAbsoluteDiscount = ('absolut' == $oDiscount->fieldValueType);
        if ($bIsAbsoluteDiscount) {
            $dTotalDiscountValue = $oDiscount->fieldValue;
        }

        $totalDiscountValueItemCalculated = 0;
        while ($oItem = &$this->Next()) {
            if ((!$bIsAbsoluteDiscount || $dTotalDiscountValue > 0) && $oDiscount->AllowDiscountForArticle($oItem)) {
                $oDiscountToPass = clone $oDiscount;
                $oItem->ApplyDiscount($oDiscountToPass, $dTotalDiscountValue);
                $totalDiscountValueItemCalculated += $oDiscountToPass->dRealValueUsed;
            }
        }
        $totalDiscountValueOverAll = $oDiscount->GetValue();
        if ($totalDiscountValueOverAll !== $totalDiscountValueItemCalculated) {
            $missingDiscountValue = $totalDiscountValueOverAll - $totalDiscountValueItemCalculated;
            $this->reducePriceForItemsAffectedByDiscount($oDiscount, $missingDiscountValue);
        }
        $this->setItemPointer($iCurrentPosition);
    }

    /**
     * return the total discount value for alle articles.
     *
     * @return float
     */
    public function GetTotalDiscountValue()
    {
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();

        $dTotalDiscountValue = 0;

        while ($oItem = &$this->Next()) {
            $dTotalDiscountValue += ($oItem->dPriceTotal - $oItem->dPriceTotalAfterDiscount);
        }
        $this->setItemPointer($iCurrentPosition);

        return $dTotalDiscountValue;
    }

    /**
     * resets all discount info for alle articles.
     *
     * @return void
     */
    public function ResetAllDiscounts()
    {
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();

        while ($oItem = &$this->Next()) {
            $oItem->ResetDiscounts();
        }
        $this->setItemPointer($iCurrentPosition);
    }

    /**
     * drop the acting shipping type marker from all articles in the basket. this is needed
     * when we want to recalculate shipping costs.
     *
     * @return void
     */
    public function ResetAllShippingMarkers()
    {
        $iCurrentPosition = $this->getItemPointer();
        $this->GoToStart();

        while ($oItem = &$this->Next()) {
            $oItem->ResetShippingMarker();
        }
        $this->setItemPointer($iCurrentPosition);
    }

    /*
    * validate the contents of the basket (such as allowable stock)
    * @param string $sMessageManager - the manager to which error messages should be sent
    * @return boolean
    */
    /**
     * @return bool
     *
     * @param string $sMessageManager
     */
    public function ValidateBasketContents($sMessageManager)
    {
        $bIsValid = true;
        $oMessage = TCMSMessageManager::GetInstance();
        $iPt = $this->getItemPointer();
        $this->GoToStart();
        while ($oTmp = $this->Next()) {
            if ($oTmp->TotalStockAvailable() < $oTmp->dAmount) {
                $aErrorCodes = $oTmp->GetSQLWithTablePrefix();
                $aErrorCodes['dStockWanted'] = $oTmp->dAmount;
                if ($oTmp->TotalStockAvailable() > 0) {
                    $aErrorCodes['dStockAvailable'] = $oTmp->TotalStockAvailable();
                    $oMessage->AddMessage($sMessageManager, 'ERROR-ADD-TO-BASKET-NOT-ENOUGH-STOCK', $aErrorCodes);
                    $this->validateBasketContentBuyable($oTmp, $sMessageManager);
                } else {
                    $oMessage->AddMessage($sMessageManager, 'ERROR-ADD-TO-BASKET-NO-STOCK', $aErrorCodes);
                }
                $oTmp->dAmount = $oTmp->TotalStockAvailable();
                $this->UpdateItemAmount($oTmp);
                $bIsValid = false;
            } else {
                $bIsValid = $this->validateBasketContentBuyable($oTmp, $sMessageManager) && $bIsValid;
            }
        }
        if ($iPt > $this->Length()) {
            $iPt = $this->Length();
        }
        $this->setItemPointer($iPt);

        return $bIsValid;
    }

    /**
     * Checks if given basket article is buyable. If not remove it from basket with message.
     *
     * @param TShopBasketArticle $oBasketArticle
     * @param string             $sMessageManager
     *
     * @return bool
     */
    private function validateBasketContentBuyable(TShopBasketArticle $oBasketArticle, $sMessageManager)
    {
        $bValid = true;
        if (false === $oBasketArticle->IsBuyable()) {
            $oMessage = TCMSMessageManager::GetInstance();
            $aErrorCodes = $oBasketArticle->GetSQLWithTablePrefix();
            $oMessage->AddMessage($sMessageManager, 'ERROR-ADD-TO-BASKET-ARTICLE-NOT-BUYABLE', $aErrorCodes);
            if ($this->IsInList($oBasketArticle)) {
                $this->RemoveArticle($oBasketArticle);
            }
            $bValid = false;
        }

        return $bValid;
    }

    public function updateCustomData(string $basketIdentifier, array $customData): bool
    {
        $item = $this->FindItemWithProperty('sBasketItemKey', $basketIdentifier);
        if (false === $item) {
            return false;
        }
        $item->setCustomData($customData);

        $this->mergeIdenticalBasketItems();

        return true;
    }

    /**
     * called whenever an item in the basket item list is changed.
     *
     * @param TShopBasketArticle $oUpdatedItem
     *
     * @return void
     */
    protected function PostUpdateItemHook($oUpdatedItem)
    {
        reset($this->aObservers);
        foreach (array_keys($this->aObservers) as $sObserverName) {
            $this->aObservers[$sObserverName]->OnBasketItemUpdateEvent($oUpdatedItem);
        }
    }

    /**
     * called whenever an item is removed from the item list.
     *
     * @param TShopBasketArticle $oDeletedItem
     *
     * @return void
     */
    protected function PostDeleteItemHook($oDeletedItem)
    {
        reset($this->aObservers);
        foreach (array_keys($this->aObservers) as $sObserverName) {
            $this->aObservers[$sObserverName]->OnBasketItemDeleteEvent($oDeletedItem);
        }
    }

    /**
     * items with the same sBasketItemKey will be merged into one item.
     */
    private function mergeIdenticalBasketItems(): void
    {
        /** @var TShopBasketArticle[] $items */
        $items = [];
        $mergedItems = false;
        $this->setItemPointer(0);
        while (false !== ($item = $this->next())) {
            $key = $item->sBasketItemKey;
            if (isset($items[$key])) {
                $items[$key]->ChangeAmount($items[$key]->dAmount + $item->dAmount);
                $mergedItems = true;
                continue;
            }
            $items[$key] = $item;
        }
        $this->setItemPointer(0);

        if (false === $mergedItems) {
            return;
        }

        $this->_items = array_values($items);
    }
}
