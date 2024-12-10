<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

/**
 * represents an article in the basket.
/**/
class TShopBasketArticleCore extends TdbShopArticle
{
    /**
     * @var float
     *            the amount of this item in the basket. it will also be saved to session
     */
    public $dAmount = 0;

    /**
     * @var float
     *            holds the original total price (amount*price)
     */
    public $dPriceTotal = 0;

    /**
     * @var float
     *            holds the price after deducting any discount assigned to the item
     */
    public $dPriceAfterDiscount = 0;

    /**
     * @var float
     *            holds the total price after deducting any discount assigned to the item
     */
    public $dPriceTotalAfterDiscount = 0;

    /**
     * holds the value of the article after all discounts have been applied WITHOUT discount vouchers.
     *
     * @var float
     */
    public $dPriceTotalAfterDiscountWithoutVouchers = 0;

    /**
     * @var float
     */
    public $dPriceAfterDiscountWithoutVouchers = 0;

    /**
     * total weight of the articles.
     *
     * @var float
     */
    public $dTotalWeight = 0;

    /**
     * total volume of the articles.
     *
     * @var float
     */
    public $dTotalVolume = 0;

    /**
     * @var string
     *             identifies the basket item based on the parameters checked via IsSameAs method
     *             So two basket items with the same sBasketItemKey ARE the same basket product.
     *             the properties is automatically set via the UpdateBasketItemKey method which needs to be
     *             called anytime any of the relevant properties change
     */
    public $sBasketItemKey = null;

    /**
     * @var TShopBasketArticleDiscountList
     */
    protected $oActingShopDiscountList = null;

    /**
     * @var TdbShopShippingType
     */
    protected $oActingShopBasketShippingType = null;

    /**
     * timestamp set to the last time the amount of the item changed
     * access via getter GetLastUpdatedTimestamp.
     *
     * @var int|null
     */
    private $iLastChangedTimestamp = null;

    /**
     * @var array
     */
    private $customData = array();

    /**
     * return timestamp when the item was last updated.
     *
     * @return int|null
     */
    public function GetLastUpdatedTimestamp()
    {
        return $this->iLastChangedTimestamp;
    }

    /**
     * return acting discount list.
     *
     * @return TShopBasketArticleDiscountList
     */
    public function GetActingDiscountList()
    {
        return $this->oActingShopDiscountList;
    }

    /**
     * remove acting discounts.
     *
     * @return void
     */
    public function ResetDiscounts()
    {
        $this->oActingShopDiscountList = null;
        $this->dPriceAfterDiscount = $this->dPrice;
        $this->dPriceTotalAfterDiscount = $this->dPriceTotal;
        $this->dPriceAfterDiscountWithoutVouchers = $this->dPriceAfterDiscount;
        $this->dPriceTotalAfterDiscountWithoutVouchers = $this->dPriceTotalAfterDiscount;
    }

    /**
     * apply a discount to the article (note that the method will not check if the discount
     * may be applied - it assumes you checked that using the function in TShopDiscount.
     *
     * @param TdbShopDiscount $oShopDiscount
     * @param double          $dMaxValueUsable - for absolute value discounts, this parameter defines how much may be at most applied to the article
     *
     * @return float - returns the remaining discount value to distribute (0 if the discount is a percent discount)
     */
    public function ApplyDiscount(TdbShopDiscount $oShopDiscount, $dMaxValueUsable = 0)
    {
        $dRemainingValue = 0;
        if (is_null($this->oActingShopDiscountList)) {
            $this->oActingShopDiscountList = new TShopBasketArticleDiscountList();
        }
        if ('absolut' == $oShopDiscount->fieldValueType) {
            $dUsedValue = $dMaxValueUsable;
            if ($this->dPriceTotalAfterDiscount < $dUsedValue) {
                $dUsedValue = $this->dPriceTotalAfterDiscount;
                $dRemainingValue = $dMaxValueUsable - $this->dPriceTotalAfterDiscount;
            }
            $oShopDiscount->dRealValueUsed = $dUsedValue;
        } else {
            $oShopDiscount->dRealValueUsed = round($this->dPriceTotal * ($oShopDiscount->fieldValue / 100), 2);
            if($oShopDiscount->dRealValueUsed > $this->dPriceTotalAfterDiscount) {
                $oShopDiscount->dRealValueUsed = $this->dPriceTotalAfterDiscount;
            }
        }
        $this->dPriceTotalAfterDiscount = $this->dPriceTotalAfterDiscount - $oShopDiscount->dRealValueUsed;
        $this->dPriceAfterDiscount = $this->dPriceTotalAfterDiscount / $this->dAmount;

        $this->dPriceAfterDiscountWithoutVouchers = $this->dPriceAfterDiscount;
        $this->dPriceTotalAfterDiscountWithoutVouchers = $this->dPriceTotalAfterDiscount;
        $this->oActingShopDiscountList->AddItem($oShopDiscount);

        return $dRemainingValue;
    }

    /**
     * @return array
     */
    public function getCustomData()
    {
        return $this->customData;
    }

    /**
     * will set the custom data - if it is able to validate the data (you must overwrite the basket item and validate the data).
     *
     * @param array $customData
     *
     * @return void
     */
    public function setCustomData(array $customData)
    {
        $this->customData = $customData;
        $this->UpdateBasketItemKey();
    }

    /**
     * @return void
     */
    protected function PostLoadHook()
    {
        parent::PostLoadHook();
        $this->UpdateBasketItemKey();
        $this->UpdateItemInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function GetToNoticeListLink($bRemoveFromBasket = true, $bIncludePortalLink = false)
    {
        if (!$bRemoveFromBasket) {
            return parent::GetToNoticeListLink($bIncludePortalLink);
        }
        $shop = $this->getShopService()->getActiveShop();
        $parameters = array(
            'module_fnc['.$shop->GetBasketModuleSpotName().']' => 'TransferToNoticeList',
            MTShopBasketCore::URL_ITEM_ID => $this->id,
            MTShopBasketCore::URL_ITEM_AMOUNT => $this->dAmount,
            MTShopBasketCore::URL_MESSAGE_CONSUMER => MTShopBasketCore::MSG_CONSUMER_NAME,
        );

        return $this->getActivePageService()->getLinkToActivePageRelative($parameters);
    }

    /**
     * returns the link to remove this item from the basket.
     *
     * @return string
     */
    public function GetRemoveFromBasketLink()
    {
        $activeShop = $this->getShopService()->getActiveShop();
        $aParameters = array('module_fnc['.$activeShop->GetBasketModuleSpotName().']' => 'RemoveFromBasketViaBasketItemKey', MTShopBasketCore::URL_ITEM_BASKET_KEY => $this->sBasketItemKey, MTShopBasketCore::URL_MESSAGE_CONSUMER => MTShopBasketCore::MSG_CONSUMER_NAME);

        return $this->getActivePageService()->getLinkToActivePageRelative($aParameters);
    }

    /**
     * @return bool
     *
     * @param TShopBasketArticle $oItem
     *                                  returns true if the passed item is the same as this instance. Relevant for this comparision is the article id and
     *                                  some other parameters
     */
    public function IsSameAs($oItem)
    {
        $bIsSame = false;
        if ($this->sBasketItemKey == $oItem->sBasketItemKey) {
            $bIsSame = true;
        }

        return $bIsSame;
    }

    /**
     * update the price total of the object.
     *
     * @return void
     */
    protected function UpdateItemInfo()
    {
        $this->dPriceTotal = $this->dAmount * $this->dPrice;
        $this->dTotalWeight = $this->dAmount * $this->fieldSizeWeight;
        $this->dTotalVolume = $this->dAmount * $this->dVolume;
        // discount must always be calculated by basket and not set by the article #64328
        // $this->dPriceAfterDiscount = $this->dPrice;
        // $this->dPriceTotalAfterDiscount = $this->dPriceTotal;
        // $this->dPriceTotalAfterDiscountWithoutVouchers = $this->dPriceTotalAfterDiscount;
    }

    /**
     * Changes the amount to the passed amount. note that the amount will not be added, but instead will be overwritten
     * Note: The method will check if the stock exceeds the available stock and auto change the value if it does.
     * no error message will be send when this occures - we include this as a safeguard only - the error handling should be managed in MTShopBasket.
     *
     * @param float $dNewAmount
     *
     * @return void
     */
    public function ChangeAmount($dNewAmount)
    {
        $dOldAmount = $this->dAmount;
        $this->dAmount = $dNewAmount;
        if ($this->TotalStockAvailable() < $this->dAmount) {
            $this->dAmount = $this->TotalStockAvailable();
        }
        if ($dOldAmount != $this->dAmount) {
            $this->iLastChangedTimestamp = time();
        } // amount was changed - update timestamp
        $this->UpdateItemInfo();
    }

    /**
     * saves a pointer to the acting shop basket discount for this item.
     * Also updates the dPriceTotalAfterDiscount value.
     *
     * @param TdbShopDiscount $oActingShopDiscount
     *
     * @psalm-suppress UndefinedThisPropertyAssignment
     *
     * @FIXME `oActingShopDiscount` is set dynamically here and does not seem to be used anywhere. Also, `SetActingDiscount` does not seem to be called anywhere.
     *
     * @return void
     */
    public function SetActingDiscount(TdbShopDiscount $oActingShopDiscount)
    {
        $this->oActingShopDiscount = $oActingShopDiscount;
    }

    /**
     * set the acting shipping type for the basket item.
     *
     * @param TdbShopShippingType $oShippingType
     *
     * @return void
     */
    public function SetActingShippingType(TdbShopShippingType $oShippingType)
    {
        $this->oActingShopBasketShippingType = $oShippingType;
    }

    /**
     * return pointer to the acting shipping type.
     *
     * @return TdbShopShippingType
     */
    public function GetActingShippingType()
    {
        return $this->oActingShopBasketShippingType;
    }

    /**
     * updates the sBasketItemKey. needs to be called anytime any of the relevant properties
     * for IsSameAs change.
     *
     * @return void
     */
    protected function UpdateBasketItemKey()
    {
        $aParts = array('id' => $this->id);
        $this->sBasketItemKey = md5(serialize($aParts));
    }

    /**
     * clear the shipping marker from the basket item (needed for recalculation.
     *
     * @return void
     */
    public function ResetShippingMarker()
    {
        $this->oActingShopBasketShippingType = null;
    }

    /**
     * reloads the article data from database if called.
     *
     * @return void
     */
    public function RefreshDataFromDatabase()
    {
        $this->ClearInternalCache();
        $sActiveLanguageId = self::getLanguageService()->getActiveLanguageId();
        if (null !== $sActiveLanguageId) {
            $this->SetLanguage($sActiveLanguageId);
        }
        $bEnableObjectCaching = $this->GetEnableObjectCaching();
        $this->SetEnableObjectCaching(false);
        $this->Load($this->id);
        $this->SetEnableObjectCaching($bEnableObjectCaching);
    }

    public function __sleep()
    {
        $aSleep = array('table', 'id', 'iLanguageId', 'dAmount', 'dPriceTotal',
            'dPriceAfterDiscount', 'dPriceTotalAfterDiscount', 'dPriceAfterDiscountWithoutVouchers', 'dPriceTotalAfterDiscountWithoutVouchers',
            'dTotalWeight', 'dTotalVolume', 'sBasketItemKey',
            "\0TShopBasketArticleCore\0iLastChangedTimestamp",
            "\0TShopBasketArticleCore\0customData", ); // the \0CLASSNAME\0PROPERTY is needed for private vars if the class is extended by some other class
        return $aSleep;
    }

    /**
     * @return void
     */
    protected function PostWakeupHook()
    {
        $this->RefreshDataFromDatabase();
    }

    /**
     * DISABLE the method for basket articles since we need the original price here!
     *
     * @return void
     */
    public function SetPriceBasedOnActiveDiscounts()
    {
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
