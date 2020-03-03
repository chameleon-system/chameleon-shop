<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\LanguageServiceInterface;
use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Der Warenkorb enthält die vom Kunden zur Bestellung vorgemerkten Artikel.
 * Diese Komponente ist für die Berechnung der Versandkosten, Rabatte, Steuern,
 * sowie möglicher, durch Bezahlarten verursachter, Zusatzkosten zuständig.
 * Außerdem wird über den Warenkorb die tatsächliche Bestellung, inklusive etwaiger
 * Exporte durchgeführt.
 * Der Warenkorb kann optional anzeigen, ab welcher Summe die nächstgünstigere
 * Versandkostenschwelle erreicht würde. Diese Information kann im Miniwarenkorb
 * sowie im Hauptwarenkorb angezeigt werden.
/**/
class TShopBasketCore implements IDataExtranetUserObserver, IPkgCmsSessionPostWakeupListener
{
    const VIEW_PATH = 'pkgShop/views/TShopBasket';

    /**
     * @var float
     *            The calculated gross sum of all articles in the basket.
     *            This is the price calculated before discounts or vouchers have been applied
     */
    public $dCostArticlesTotal = 0;
    /**
     * @var float
     *            the total delivery costs (gross)
     */
    public $dCostShipping = 0;
    /**
     * @var float
     *            the total wrapping costs for the basket (gross)
     */
    public $dCostWrapping = 0;
    /**
     * @var float
     *            the total gross wrapping card costs
     */
    public $dCostWrappingCards = 0;
    /**
     * @var float
     *            total gross voucher value for the basket
     *            NOTE: includes ONLY sponsored vouchers
     */
    public $dCostVouchers = 0;
    /*
     * the total for all NONE sponsored vouchers (vouchers that act as discounts - so DO affect VAT).
     */
    public $dCostNoneSponsoredVouchers = 0;
    /**
     * @var float
     *            the total gross discount sum
     */
    public $dCostDiscounts = 0;

    /**
     * the total article costs after discounts have been applied
     * NOTE: vouchers not sponsored are included in this price - so this represents the REAL article price.
     *
     * @var float
     */
    public $dCostArticlesTotalAfterDiscounts = 0;

    public $dCostArticlesTotalAfterDiscountsWithoutNoneSponsoredVouchers = 0;
    /**
     * @var float
     *            the sum of all VAT costs
     */
    public $dCostVAT = 0;

    /**
     * @var float
     *            the sum of all VAT costs excluding shipping costs
     */
    public $dCostVATWithoutShipping = 0;

    /**
     * @var float
     *            the grand total for the basket
     */
    public $dCostTotal = 0;

    /**
     * @var float
     *            the grand total for the basket excluding shipping costs
     */
    public $dCostTotalWithoutShipping = 0;

    /**
     * @var float
     */
    public $dCostPaymentMethodSurcharge = 0;

    /**
     * the total number of articles in the basket.
     *
     * @var float
     */
    public $dTotalNumberOfArticles = 0;
    /**
     * the total number of unique articles in the basket.
     *
     * @var float
     */
    public $iTotalNumberOfUniqueArticles = 0;

    public $dTotalWeight = 0;
    public $dTotalVolume = 0;

    /**
     * all order steps completed are stored in this array (systemname=>true)
     * this way, each step can check if all previous steps have been properly completed.
     *
     * @var array
     */
    public $aCompletedOrderStepList = array();

    /**
     * the basket identifier is set when the basket is commited to db (in the order process) the first time
     * this usually happens after the user enters from the basket overview page into the order process.
     *
     * @var string
     */
    public $sBasketIdentifier = null;

    /**
     * @var TShopWrappingCard
     */
    protected $oWrappingCard = null;
    /**
     * @var TShopWrapping
     */
    protected $oWrapping = null;
    /**
     * use GetActiveVatList to access the property.
     *
     * @var TdbShopVatList
     */
    private $oActiveVatList = null;
    /**
     * holds the articles of the basket - NOT: you should NOT access this directly. instead use GetBasketArticles().
     *
     * @var TShopBasketArticleList
     */
    protected $oBasketArticles = null;
    /**
     * use GetActiveDiscounts to access the property.
     *
     * @var TShopBasketDiscountList
     */
    private $oActiveDiscounts = null;

    /**
     * use GetActiveShippingGroup to access this property.
     *
     * @var TdbShopShippingGroup
     */
    private $oActiveShippingGroup = null;

    /**
     * use GetActivePaymentMethod to access the property.
     *
     * @var TShopPaymentMethod
     */
    private $oActivePaymentMethod = null;

    /**
     * use GetActiveVouchers to access the property.
     *
     * @var TShopBasketVoucherList
     */
    private $oActiveVouchers = null;

    private $rawBasket = null;

    const SESSION_KEY_NAME = 'esono/pkgShop/activeBasket';

    /**
     * as soon as an order request is issued, we try to create an order id, and
     * store it in $_SESSION[self::SESSION_KEY_PROCESSING_BASKET]. This way, we can prevent a reload from
     * generating a second order.
     */
    const SESSION_KEY_PROCESSING_BASKET = 'basketobjectprocessingkey';

    /**
     * when creating an order we place the order id into this session var and keep it there
     * this is usefull if you, for example, want to show the order as a print version to the user.
     */
    const SESSION_KEY_LAST_CREATED_ORDER_ID = 'basketobjectlastorderid';

    /**
     * if set to true, the basket will recalculate its contents on destruction or reinitialization
     * use SetBasketRecalculationFlag and BasketRequiresRecalculation to access.
     *
     * @var bool
     */
    private $bMarkAsRecalculationNeeded = false;

    private $totalCostKnown = false;
    private $recalculationDepth = 0;

    public function isTotalCostKnown()
    {
        return $this->totalCostKnown;
    }

    /**
     * added to allow subclasses to extend the method.
     */
    public function __construct()
    {
    }

    /**
     * mark the basket as dirty - will be recalculated as soon as the basket is loaded from sessin.
     *
     * @param bool $bRecalculationNeeded
     */
    public function SetBasketRecalculationFlag($bRecalculationNeeded = true)
    {
        $this->bMarkAsRecalculationNeeded = $bRecalculationNeeded;
    }

    /**
     * return true if the basket needs to be recalculated.
     *
     * @return bool
     */
    public function BasketRequiresRecalculation()
    {
        return $this->bMarkAsRecalculationNeeded;
    }

    /**
     * remove these in a later version - kept for compatibility reasons.
     *
     * @param string $sVar
     *
     * @return bool|null
     */
    public function __get($sVar)
    {
        if ('bMarkAsRecalculationNeeded' == $sVar) {
            trigger_error('DO NOT access bMarkAsRecalculationNeeded as property. use SetBasketRecalculationFlag() and BasketRequiresRecalculation() instead!', E_USER_WARNING);

            return $this->BasketRequiresRecalculation();
        } else {
            $trace = debug_backtrace();
            trigger_error('Undefined property via __get(): '.$sVar.' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);

            return null;
        }
    }

    /**
     * remove these in a later version - kept for compatibility reasons.
     *
     * @param  $sVar
     * @param  $sVal
     */
    public function __set($sVar, $sVal)
    {
        if ('bMarkAsRecalculationNeeded' == $sVar) {
            trigger_error('DO NOT access bMarkAsRecalculationNeeded as property. use SetBasketRecalculationFlag() and BasketRequiresRecalculation() instead!', E_USER_WARNING);

            return $this->SetBasketRecalculationFlag($sVal);
        } else {
            $trace = debug_backtrace();
            trigger_error('Undefined property via __set(): '.$sVar.' in '.$trace[0]['file'].' on line '.$trace[0]['line'], E_USER_NOTICE);
        }
    }

    public function __clone()
    {
        foreach ($this as $sPropName => $sPropVal) {
            if (is_object($this->$sPropName)) {
                $this->$sPropName = clone $this->$sPropName;
            }
        }
    }

    /**
     * set the active shipping group - return false, if an invalid group is selected.
     *
     * @param TdbShopShippingGroup $oShippingGroup
     *
     * @return bool
     */
    public function SetActiveShippingGroup($oShippingGroup)
    {
        $bGroupAssigned = false;
        if (!is_null($oShippingGroup)) {
            $oAvailableShippingGroups = $this->GetAvailableShippingGroups();
            if (!is_null($oAvailableShippingGroups) && $oAvailableShippingGroups->IsInList($oShippingGroup->id)) {
                $bGroupAssigned = true;
                $this->oActiveShippingGroup = $oShippingGroup;

                // if a payment method is selected, then we need to make sure it is available for the selected shipping group.
                // since the SetActivePaymentmethod performs the check we call it.
                if (!is_null($this->GetActivePaymentMethod())) {
                    $this->SetActivePaymentMethod($this->GetActivePaymentMethod());
                }
            }
        } else {
            $this->oActiveShippingGroup = null;
            $this->SetActivePaymentMethod(null);
        }

        return $bGroupAssigned;
    }

    /**
     * return the active shipping group - will set the shipping group to the default group, if none is set.
     *
     * @return TdbShopShippingGroup
     */
    public function &GetActiveShippingGroup()
    {
        if (is_null($this->oActiveShippingGroup)) {
            // fetch the one from the shop
            $oShopConfig = TdbShop::GetInstance();
            $oActiveShippingGroup = $oShopConfig->GetFieldShopShippingGroup();
            if (false == $this->SetActiveShippingGroup($oActiveShippingGroup)) {
                // unable to set - group not in allowed list
                $oList = $this->GetAvailableShippingGroups();
                $oList->GoToStart();
                if ($oList->Length() > 0) {
                    $oActiveShippingGroup = $oList->Current();
                    $this->SetActiveShippingGroup($oActiveShippingGroup);
                }
            }
        }

        return $this->oActiveShippingGroup;
    }

    /**
     * return the active shipping group - will NOT set the shipping group to the default group, if none is set.
     *
     * @return TdbShopShippingGroup|null
     */
    public function &GetActiveShippingGroupWithoutLoading()
    {
        return $this->oActiveShippingGroup;
    }

    /**
     * @return TdbShopShippingGroupList
     */
    public function &GetAvailableShippingGroups()
    {
        return TdbShopShippingGroupList::GetAvailableShippingGroups();
    }

    /**
     * return all payment methods for the active shipping group.
     *
     * @return TdbShopPaymentMethodList
     */
    public function GetAvailablePaymentMethods()
    {
        $oList = null;
        if ($this->GetActiveShippingGroup()) {
            $oList = $this->GetActiveShippingGroup()->GetValidPaymentMethods();
        }

        return $oList;
    }

    /**
     * return all payment methods for the active shipping group that are selectable by the user.
     *
     * @return TdbShopPaymentMethodList
     */
    public function GetValidPaymentMethodsSelectableByTheUser()
    {
        $oList = null;
        if ($this->GetActiveShippingGroup()) {
            $oList = $this->GetActiveShippingGroup()->GetValidPaymentMethodsSelectableByTheUser();
        }

        return $oList;
    }

    public function ResetAllShippingMarkers()
    {
        $this->GetBasketArticles()->ResetAllShippingMarkers();
    }

    /**
     * set the active payment method. return true on succes - false if the method is not allowed
     * Note: you hast set a shipping group first!
     *
     * @param TdbShopPaymentMethod $oShopPayment
     *
     * @return bool
     */
    public function SetActivePaymentMethod($oShopPayment)
    {
        // make sure the shop payment selected is valid for the shipping group selected
        $bMethodOk = false;
        if (!is_null($oShopPayment)) {
            $oList = $this->GetAvailablePaymentMethods();
            if (!is_null($oList) && $oList->IsInList($oShopPayment->id)) {
                $bMethodOk = true;
                $this->oActivePaymentMethod = $oShopPayment;
            } else {
                $this->oActivePaymentMethod = null;
            }
        }

        return $bMethodOk;
    }

    /**
     * return the active payment method.
     *
     * @return TdbShopPaymentMethod
     */
    public function &GetActivePaymentMethod()
    {
        return $this->oActivePaymentMethod;
    }

    /**
     * deletes contents of basket.
     */
    public function ClearBasket()
    {
        $this->UnlockBasket();
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
        $request->getSession()->remove(self::SESSION_KEY_NAME);

        $event = new \ChameleonSystem\ShopBundle\objects\TShopBasket\BasketItemEvent(
            TdbDataExtranetUser::GetInstance(),
            $this
        );
        $this->getEventDispatcher()->dispatch(\ChameleonSystem\ShopBundle\ShopEvents::BASKET_CLEAR, $event);
    }

    /**
     * @param bool $bReset
     * @param bool $bReloadFromSession
     *
     * @return TShopBasket
     *                     return the current basket object
     *
     * @deprecated use chameleon_system_shop.shop_service getActiveBasket and resetBasket instead
     */
    public static function GetInstance($bReset = false, $bReloadFromSession = false)
    {
        /** @var $shopService ShopServiceInterface */
        $shopService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
        if ($bReset) {
            $shopService->resetBasket();
        }

        return $shopService->getActiveBasket();
    }

    /**
     * return true if the basket item is stored in session.
     *
     * @return bool
     */
    public static function BasketInSession()
    {
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

        return $request->getSession()->has(self::SESSION_KEY_NAME);
    }

    /**
     * the method is called by the user object when the user logges out.
     */
    public function OnUserLogoutHook()
    {
        $this->SetActivePaymentMethod(null);
        $this->SetActiveShippingGroup(null);
        //$this->RecalculateBasket();
        $this->SetBasketRecalculationFlag();
    }

    /**
     * called by the extranet user when he loggs in.
     */
    public function OnUserLoginHook()
    {
        $this->SetBasketRecalculationFlag();
        //      $this->RecalculateBasket();
    }

    /**
     * hook is called by the extranet user object when the user data changes.
     */
    public function OnUserUpdatedHook()
    {
        $this->SetBasketRecalculationFlag();
        //      $this->RecalculateBasket();
    }

    /**
     * commits the basket to session, AND unregisters itself als an observer from
     * oUser - this method is called through the register_shutdown_function and should
     * not be called directly.
     *
     * @param bool $bForce - overwrite session with basket data even if this is not the correct basket instance
     *                     if you use this to replace the basket object, make sure to call TShopBasket::GetInstance(false,true) after
     *                     to set the instance based on this new session
     */
    public function CommitToSession($bForce = false)
    {
        // save copy to session
        if (TShopBasket::BasketInSession()) {
            $oTmp = TShopBasket::GetInstance();
            if (true === $bForce || $oTmp == $this) {
                $oUser = TdbDataExtranetUser::GetInstance();
                $oUser->ObserverUnregister('oUserBasket');

                /** @var Request $request */
                $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
                /** @var TPKgCmsSession $session */
                $session = $request->getSession();
                $session->set(self::SESSION_KEY_NAME, $this);
            }
        }
    }

    /**
     * exposes the GetBasketSumForVoucher method in TShopBasketArticleList.
     *
     * @param TShopVoucher $oVoucher
     *
     * @return float
     */
    public function GetBasketSumForVoucher(TShopVoucher &$oVoucher)
    {
        $value = $this->GetBasketArticles()->GetBasketSumForVoucher($oVoucher);
        if (true === $oVoucher->IsSponsored()) {
            $value += $this->dCostShipping;
        }

        return $value;
    }

    /**
     * applies the dVoucherValue evenly over all items affected by a voucher NOT sponsored (ie a voucher that has no real money value).
     *
     * @param $oVoucher
     * @param $dVoucherValue
     */
    public function ApplyNoneSponsoredVoucherValueToItems(TdbShopVoucher &$oVoucher, $dVoucherValue)
    {
        $this->GetBasketArticles()->ReducePriceForItemsAffectedByNoneSponsoredVoucher($oVoucher, $dVoucherValue);
    }

    /**
     * exposes the GetBasketQuantityForVoucher method in TShopBasketArticleList.
     *
     * @param TShopVoucher $oVoucher
     *
     * @return float
     */
    public function GetBasketQuantityForVoucher(TShopVoucher &$oVoucher)
    {
        return $this->GetBasketArticles()->GetBasketQuantityForVoucher($oVoucher);
    }

    /**
     * exposes the GetBasketSumForDiscount method in TShopBasketArticleList.
     *
     * @param TShopDiscount $oDiscount
     *
     * @return float
     */
    public function GetBasketSumForDiscount(TShopDiscount &$oDiscount)
    {
        return $this->GetBasketArticles()->GetBasketSumForDiscount($oDiscount);
    }

    /**
     * exposes the GetBasketQuantityForDiscount method in TShopBasketArticleList.
     *
     * @param TShopDiscount $oDiscount
     *
     * @return float
     */
    public function GetBasketQuantityForDiscount(TShopDiscount &$oDiscount)
    {
        return $this->GetBasketArticles()->GetBasketQuantityForDiscount($oDiscount);
    }

    /**
     * add a TShopBasketArticle to the article list. if such an item is already in the basket, we just add the amount
     * instead of inserting a new entry.
     * return true if the item was updated, false if it was removed (happens when the amount falls below zero).
     *
     * @param TShopBasketArticle $oItem
     *
     * @return bool
     */
    public function AddItem(TShopBasketArticle &$oItem)
    {
        $bWasAdded = false;
        if ($oItem->IsBuyable()) {
            $bWasAdded = $this->GetBasketArticles()->AddItem($oItem);
            $this->SetBasketRecalculationFlag();
        }

        return $bWasAdded;
    }

    /**
     * searches for the item passed. if found, we return the item found, else we return false.
     *
     * @param TShopBasketArticle $oItem
     *
     * @return TShopBasketArticle|bool
     */
    public function FindItem($oItem)
    {
        return $this->FindItemByBasketItemKey($oItem->sBasketItemKey);
    }

    /**
     * @param string $sBasketItemKey
     *
     * @return TShopBasketArticle|bool
     */
    public function FindItemByBasketItemKey($sBasketItemKey)
    {
        return $this->GetBasketArticles()->FindItemWithProperty('sBasketItemKey', $sBasketItemKey);
    }

    /**
     * update the amount of the item in the basket. if it does not exists, it will be added instead
     * if the resulting amount is less than or equal to zero, the item will be removed
     * return true if the item was updated, false if it was removed (happens when the amount falls below zero).
     *
     * @param TShopBasketArticle $oItem
     *
     * @return bool
     */
    public function UpdateItemAmount(TShopBasketArticle &$oItem)
    {
        $bWasUpdated = false;
        if ($oItem->IsBuyable() || 0 == $oItem->dAmount) {
            $bWasUpdated = $this->GetBasketArticles()->UpdateItemAmount($oItem);
            $this->SetBasketRecalculationFlag();
        }

        return $bWasUpdated;
    }

    /**
     * Searches for an Item matching the passed item, removes it from the list and returns it. if no item is found
     * the method will return null.
     *
     * @param string $sBasketItemKey
     *
     * @return TShopBasketArticle|false
     */
    public function RemoveItem($sBasketItemKey)
    {
        $oRemoveItem = $this->FindItemByBasketItemKey($sBasketItemKey);
        if ($oRemoveItem) {
            $iOldAmount = $oRemoveItem->dAmount;
            $oRemoveItem->ChangeAmount(0);
            $this->UpdateItemAmount($oRemoveItem);
            $oRemoveItem->ChangeAmount($iOldAmount);
        } else {
            $oRemoveItem = false;
        }

        return $oRemoveItem;
    }

    /**
     * Recalculates the current basket contents (all dCost items).
     */
    public function RecalculateBasket()
    {
        $this->totalCostKnown = true;
        ++$this->recalculationDepth;

        $this->SetBasketRecalculationFlag(false);
        $this->dCostArticlesTotal = 0;
        $this->dCostArticlesTotalAfterDiscounts = 0;
        $this->dCostArticlesTotalAfterDiscountsWithoutNoneSponsoredVouchers = 0;
        $this->dCostShipping = 0;
        $this->dCostWrapping = 0;
        $this->dCostWrappingCards = 0;
        $this->dCostVouchers = 0;
        $this->dCostDiscounts = 0;
        $this->dCostVAT = 0;
        $this->dCostTotal = 0;
        $this->dCostPaymentMethodSurcharge = 0;

        $this->GetBasketArticles()->Refresh();
        $this->ResetAllShippingMarkers();

        $this->iTotalNumberOfUniqueArticles = $this->GetBasketArticles()->Length();
        $this->dTotalNumberOfArticles = $this->GetBasketArticles()->dNumberOfItems;
        $this->dCostArticlesTotal = $this->GetBasketArticles()->dProductPrice;
        $this->dTotalWeight = $this->GetBasketArticles()->dTotalWeight;
        $this->dTotalVolume = $this->GetBasketArticles()->dTotalVolume;

        $this->RecalculateDiscounts();
        $this->dCostArticlesTotalAfterDiscounts = $this->dCostArticlesTotal - $this->dCostDiscounts;
        $this->dCostArticlesTotalAfterDiscountsWithoutNoneSponsoredVouchers = $this->dCostArticlesTotalAfterDiscounts;

        /*
         * Reload vouchers so that their PostLoadHook is executed. If the user changed the currency, it needs to be
         * adjusted in the vouchers, which is done automatically in the hook. This is a quick fix - the problem itself
         * needs to be addressed in a wider scope.
         */
        $this->reloadVouchers();

        // now calculate the voucher values that act as discounts (vouchers NOT sponsored)
        $this->RecalculateNoneSponsoredVouchers();
        $this->dCostArticlesTotalAfterDiscounts = $this->dCostArticlesTotalAfterDiscounts - $this->dCostNoneSponsoredVouchers;

        $this->RecalculateShipping();
        $this->CalculatePaymentMethodCosts();

        $this->RecalculateVAT();
        $this->dCostTotal = $this->dCostArticlesTotalAfterDiscounts + $this->dCostShipping + $this->dCostPaymentMethodSurcharge;
        $this->RecalculateVouchers();
        $this->dCostTotal = $this->dCostTotal - $this->dCostVouchers; // - $this->dCostDiscounts;
        $this->dCostTotalWithoutShipping = $this->dCostTotal - $this->dCostShipping;

        /**
         * since the payment method may have a basket value restriction, we need to check its validity again after
         * we have the final basket value. If the final basket value invalidates the payment, then we need to call the recalculate again
         * if this again results in an invalid payment method, then we throw an exception.
         */
        $this->totalCostKnown = false;

        if (null !== $this->GetActivePaymentMethod() && false === $this->HasValidPaymentMethod()) {
            if ($this->recalculationDepth > 1) {
                throw new ErrorException("there is no shipping group that has any payment methods available for the current basket due to basket value ({$this->dCostTotal}) constraints set in payment methods [{$this->GetActivePaymentMethod()->id}]", 0, E_USER_ERROR, __FILE__, __LINE__);
            }
            $this->SetActiveShippingGroup(null);
            $this->oActivePaymentMethod = null;
            $this->GetActiveShippingGroup();
            $this->RecalculateBasket();
        }
        --$this->recalculationDepth;
    }

    /**
     * Recalculate discounts.
     * Discounts are applied using the following algorithm:
     * 1.    for every active discount defined for the shop we
     *     a.    Fetch the discounted basket article total that is relevant for this discount (ie. Ignore articles that are excluded from the discount)
     *     b.    Check if the discount is permitted for this value. If not, remove it.
     *     c.    Calculate the discount value based on this value
     * 2.    Sum up the results of all discounts.
     */
    protected function RecalculateDiscounts()
    {
        $oDiscountList = TdbShopDiscountList::GetActiveDiscountList();
        $this->GetActiveDiscounts()->Destroy();
        $this->oActiveDiscounts = null; //because TdbShopDiscountList has no RemoveInvalidDiscounts()!
        while ($oDiscount = $oDiscountList->Next()) {
            $this->GetActiveDiscounts()->AddItem($oDiscount);
        }
        $this->GetBasketArticles()->ResetAllDiscounts();
        if ($this->GetActiveDiscounts()->Length() > 0) {
            $this->GetActiveDiscounts()->RemoveInvalidDiscounts();
            $this->GetActiveDiscounts()->GoToStart();
            while ($oDiscount = $this->GetActiveDiscounts()->Next()) {
                $this->GetBasketArticles()->ApplyDiscount($oDiscount);
            }
            $this->GetActiveDiscounts()->GoToStart();

            // get discount total
            $this->dCostDiscounts = $this->GetBasketArticles()->GetTotalDiscountValue();
        } else {
            $this->dCostDiscounts = 0;
        }
    }

    /**
     * fetches the shipping costs from the active shipping group.
     */
    protected function RecalculateShipping()
    {
        $this->dCostShipping = 0;

        // validate shipping group - reset if not valid
        if (null !== $this->GetActiveShippingGroup()) {
            if (false === $this->SetActiveShippingGroup($this->GetActiveShippingGroup())) {
                $this->SetActiveShippingGroup(null);
            }
        }

        // if the basket contains a voucher that is set to free_shipping, then we keep costs to zero
        if (is_null($this->GetActiveVouchers()) || !$this->GetActiveVouchers()->HasFreeShippingVoucher()) {
            if (!is_null($this->GetActiveShippingGroup())) {
                $this->dCostShipping = $this->GetActiveShippingGroup()->GetShippingCostsForBasket();
            }
        }
    }

    protected function CalculatePaymentMethodCosts()
    {
        $this->dCostPaymentMethodSurcharge = 0;
        if (!is_null($this->GetActivePaymentMethod())) {
            $this->dCostPaymentMethodSurcharge = $this->GetActivePaymentMethod()->GetPrice();
        }
    }

    /**
     * The total VAT is calculated using the following algorithm:
     * 1.    The system creates a TShopBasketVatGroup object for each unique VAT in the basket (articles, Shipping, etc) and places a pointer in it for each article that matches this vat percentage
     * 2.    Each Item gets a pointer to the Vat group
     * 3.    The system calculates the vat for each group. The resulting sum is the total vat.
     *
     * Note that each group takes the rebate into consideration
     */
    protected function RecalculateVAT()
    {
        $this->GetActiveVATList()->GoToStart();
        while ($oVat = $this->GetActiveVATList()->Next()) {
            $oVat->reset();
            $dGrossTotal = $this->GetBasketTotalForVatGroup($oVat, false);
            $oVat->addValue($dGrossTotal);
        }

        $this->dCostVATWithoutShipping = $this->GetActiveVATList()->GetTotalVatValue();
        $this->GetActiveVATList()->GoToStart();

        $oShopConf = TdbShop::GetInstance();
        if (true === $oShopConf->fieldShippingVatDependsOnBasketContents && $this->dCostVATWithoutShipping > 0) {
            if (is_null($this->GetActiveVouchers()) || !$this->GetActiveVouchers()->HasFreeShippingVoucher()) {
                // need to add the shipping costs to the max vat group
                $oVatMax = $this->GetLargestVATObject();
                if (!is_null($oVatMax)) {
                    $this->GetActiveVATList()->GoToStart();
                    $bDone = false;
                    while (!$bDone && ($oVat = $this->GetActiveVATList()->Next())) {
                        if ($oVatMax->id == $oVat->id && !is_null($this->GetActiveShippingGroup())) {
                            $dGrossTotal = $this->GetActiveShippingGroup()->GetShippingCostsForBasket();
                            $oVat->addValue($dGrossTotal);
                            $bDone = true;
                        }
                    }
                }
            }
        } else {
            $oActiveShippingGroup = $this->GetActiveShippingGroup();
            if (null !== $oActiveShippingGroup) {
                $oVatShippingGroup = $oActiveShippingGroup->GetVat();
                if (null !== $oVatShippingGroup) {
                    $this->GetActiveVATList()->GoToStart();
                    $bDone = false;
                    while (!$bDone && ($oVat = $this->GetActiveVATList()->Next())) {
                        if ($oVatShippingGroup->id === $oVat->id) {
                            $dGrossTotal = $oActiveShippingGroup->GetShippingCostsForBasket();
                            $oVat->addValue($dGrossTotal);
                            $bDone = true;
                        }
                    }
                }
            }
        }

        $this->dCostVAT = $this->GetActiveVATList()->GetTotalVatValue();
        $this->GetActiveVATList()->GoToStart();
    }

    /**
     * return copy of active vat group list.
     *
     * @return TdbShopVatList
     */
    public function &GetActiveVATList()
    {
        if (is_null($this->oActiveVatList)) {
            $this->oActiveVatList = TdbShopVatList::GetList();
            $this->oActiveVatList->bAllowItemCache = true;
            $this->oActiveVatList->GoToStart();
        }

        return $this->oActiveVatList;
    }

    /**
     * return the largest vat object from the active vat group.
     *
     * @return TdbShopVat
     */
    public function GetLargestVATObject()
    {
        $oActiveVatList = $this->GetActiveVATList();
        /** @var $oMaxItem TdbShopVat */
        $oMaxItem = null;
        if (is_object($oActiveVatList)) {
            $oMaxItem = $oActiveVatList->GetMaxItem();
        }

        return $oMaxItem;
    }

    /**
     * get the total gross value of all items (including shipping, payment methods, etc) for the vat
     * group.
     *
     * @param TdbShopVat $oVat
     *
     * @return float
     */
    protected function GetBasketTotalForVatGroup(TdbShopVat &$oVat, $bIncludePaymentAndShipping = true)
    {
        $dGrossVal = 0;
        $oMatchingArticles = $this->GetBasketArticles()->GetListMatchingVat($oVat);
        $dGrossVal += ($oMatchingArticles->dProductPrice - $oMatchingArticles->GetTotalDiscountValue());

        if ($bIncludePaymentAndShipping) {
            // add price for shipping
            if (!is_null($this->GetActiveShippingGroup())) {
                $oShippingVat = $this->GetActiveShippingGroup()->GetVat();
                if (!is_null($oShippingVat) && $oShippingVat->id == $oVat->id) {
                    $dGrossVal += $this->GetActiveShippingGroup()->GetShippingCostsForBasket();
                }
            }

            // add price for payment
            if (!is_null($this->GetActivePaymentMethod())) {
                $oPaymentVat = $this->GetActivePaymentMethod()->GetVat();
                if (!is_null($oPaymentVat) && $oPaymentVat->id == $oVat->id) {
                    $dGrossVal += $this->GetActivePaymentMethod()->GetPrice();
                }
            }
        }

        return $dGrossVal;
    }

    /**
     * calculates the value of NONE sponsored vouchers. The article prices for each item in the
     * basket affected by a voucher is reduced by the value calculated for the item.
     */
    protected function RecalculateNoneSponsoredVouchers()
    {
        $this->dCostNoneSponsoredVouchers = 0;

        $noneSponsoredVouchers = $this->getActiveNoneSponsoredVouchers();
        $noneSponsoredVouchers->RemoveInvalidVouchers(MTShopBasketCore::MSG_CONSUMER_NAME, $this);
        $this->dCostNoneSponsoredVouchers = $noneSponsoredVouchers->GetVoucherValue(false);

        if ($this->dCostNoneSponsoredVouchers > $this->dCostArticlesTotalAfterDiscounts) {
            $this->dCostNoneSponsoredVouchers = $this->dCostArticlesTotalAfterDiscounts;
        }
    }

    /**
     * Vouchers are applied using the following algorithm:
     * 1.    For every active voucher:
     *     a.    Fetch the discounted basket article total that is relevant for this voucher (ie. Ignore articles that are excluded from the voucher)
     *     b.    Check if the voucher is permited for this value. If not, remove it.
     *     c.    Calculate the vaucher value based on this value
     * 2.    Sum up the results of all vauchers.
     */
    protected function RecalculateVouchers()
    {
        if (!is_null($this->GetActiveVouchers())) {
            $this->dCostVouchers = 0;
            $this->GetActiveVouchers()->RemoveInvalidVouchers(MTShopBasketCore::MSG_CONSUMER_NAME, $this);
            $this->dCostVouchers = $this->GetActiveVouchers()->GetVoucherValue(true);
            if ($this->dCostVouchers > $this->dCostTotal) {
                $this->dCostVouchers = $this->dCostTotal;
            }
        } else {
            $this->dCostVouchers = 0;
        }
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
        return $this->GetBasketArticles()->GetArticlesAffectedByShippingType($oShippingType);
    }

    /**
     * create an order based on the current basket. returns the order created on success, false on failure.
     *
     * @param string $sMessageConsumer - who should recieve error messages
     * @param bool   $bForcePayment    - set to true if you want to force the selected payment EVEN if it is not allowed for the current user
     *
     * @return TdbShopOrder
     */
    public function CreateOrder($sMessageConsumer, $bForcePayment = false)
    {
        $bOrderCreated = false;
        $oOrder = false;
        $bBasketVouchersValidated = $this->CheckBasketVoucherAvailable($sMessageConsumer);
        $this->RecalculateBasket();
        $bSkipPaymentValidation = $bForcePayment; // skip the payment validation if the payment is to be forced
        $this->getLogger()->info('create order');
        if ($this->CreateOrderAllowCreation($sMessageConsumer, $bSkipPaymentValidation) && $bBasketVouchersValidated) {
            $this->getLogger()->info('create order: order creation permitted', array('basket' => $this, 'user' => TdbDataExtranetUser::GetInstance()));
            // lock basket to prevent a second request from creating the order again.
            $this->LockBasket();
            // now create order
            /** @var $oOrder TdbShopOrder */
            $oOrder = TdbShopOrder::GetNewInstance();
            // copy base data
            $oBasketCopy = clone $this;
            $oOrder->LoadFromBasket($oBasketCopy);

            // we are not logged in yet, but trying to create the order... so we need to set allow edit all
            $oOrder->AllowEditByAll(true);
            $oOrder->Save();
            //'order_ident'=>$oBasket->sBasketIdentifier
            if (!empty($oBasketCopy->sBasketIdentifier)) { // if the basket has no identifier, then we do not update the shop_order_basket record
                $query = "UPDATE `shop_order_basket` SET `shop_order_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($oOrder->id)."' WHERE `order_ident` = '".MySqlLegacySupport::getInstance()->real_escape_string($oBasketCopy->sBasketIdentifier)."'";
                MySqlLegacySupport::getInstance()->query($query);
            }

            $this->SaveOrderIdAsLastCreatedOrderInSession($oOrder);

            // now we need to add the articles
            $oOrder->SaveArticles($this->GetBasketArticles());

            // save VAT List
            $oOrder->SaveVATList($this->GetActiveVATList());

            // save shipping information
            $this->SaveShippingUserData($oOrder);

            // save payment info
            $this->SavePaymentUserData($oOrder);

            // now save the vouchers
            if (!is_null($this->GetActiveVouchers())) {
                $oOrder->SaveVouchers($this->GetActiveVouchers());
            }

            // save discounts
            if (!is_null($this->GetActiveDiscounts())) {
                $oOrder->SaveDiscounts($this->GetActiveDiscounts());
            }

            // call hook to save any custom data
            $oOrder->SaveCustomDataFromBasket($oBasketCopy);

            // all data has been saved - call the post create order all data saved hook - this allows
            // us to perform any actions we may need to perform BEFORE further processing (such as payment, export, ect)
            $bOrderCreated = $this->PostCreateOrderAllDataSavedHook($sMessageConsumer, $oOrder);

            $bPaymentOK = false;
            if ($bOrderCreated) {
                $bOrderCreated = false;
                // call optional external APIs
                $bPaymentExecutionAllowed = $oOrder->PrePaymentExecuteHook($sMessageConsumer);

                // now try payment...
                if ($bPaymentExecutionAllowed) {
                    $oPaymentHandler = $this->GetActivePaymentMethod()->GetFieldShopPaymentHandler();
                    $bPaymentOK = $oOrder->ExecutePayment($oPaymentHandler, $sMessageConsumer);

                    if (true === $bPaymentOK) {
                        $bOrderCreated = $this->OnPaymentSuccessHook($oOrder, $oPaymentHandler, $sMessageConsumer);
                        $oOrder->AllowEditByAll(false);
                    } else {
                        $sErrorMsg = $this->OnPaymentErrorHook($bPaymentOK, $oOrder, $oPaymentHandler);
                        $oMsgManager = TCMSMessageManager::GetInstance();
                        $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-PAYMENT-METHOD-REJECTED', array('errorMsg' => $sErrorMsg));
                        $bOrderCreated = false;
                    }
                } else {
                    // errors messages should be added in $oOrder->PrePaymentExecuteHook()
                    $this->getLogger()->info(
                        sprintf('Order cancelled because PrePaymentExecuteHook returned false. Order id: %s', $oOrder->id),
                        [$oOrder->sqlData]
                    );
                    $oOrder->SetStatusCanceled(true);
                    $bOrderCreated = false;
                }
                if (false !== $bOrderCreated) {
                    $bOrderCreated = $oOrder;
                }
            } else {
                $bOrderCreated = $oOrder;
            }
            $oOrder->CreateOrderInDatabaseCompleteHook(); // order has been processed... so mark as completed

            if (false === $bOrderCreated) {
                $this->UnlockBasket();
                if (!$bPaymentOK) {
                    $this->redirectToPaymentStep();
                }
            }
        }

        return $bOrderCreated;
    }

    /**
     * if CMS_PAYMENT_REDIRECT_ON_FAILURE is defined with correct order step system name do redirect
     * to defined step.
     * This step should contain payment selection.
     */
    protected function redirectToPaymentStep()
    {
        if (defined('CMS_PAYMENT_REDIRECT_ON_FAILURE') && CMS_PAYMENT_REDIRECT_ON_FAILURE != '') {
            $oOrderStep = TdbShopOrderStep::GetStep(CMS_PAYMENT_REDIRECT_ON_FAILURE);
            if ($oOrderStep) {
                $oOrderStep->JumpToStep($oOrderStep);
            }
        }
    }

    /**
     * save the shipping user data.
     *
     * @param TdbShopOrder $oOrder
     */
    protected function SaveShippingUserData(&$oOrder)
    {
        if ($this->HasValidShippingGroup()) {
            $oOrder->SaveShippingUserData($this->GetActiveShippingGroup());
        }
    }

    /**
     * save the payment user data.
     *
     * @param TdbShopOrder $oOrder
     */
    protected function SavePaymentUserData(&$oOrder)
    {
        if ($this->HasValidPaymentMethod()) {
            $oOrder->SavePaymentUserData($this->GetActivePaymentMethod());
        }
    }

    /**
     * method is called after all data from the basket has been saved to the order
     * and before any other processing (payment, export, etc) begins.
     * NOTE: if the method returns false, the order is NOT canceled - but other processing (payment, export etc) is STOPPED.
     *
     * @param string       $sMessageConsumer
     * @param TdbShopOrder $oOrder
     *
     * @return bool
     */
    protected function PostCreateOrderAllDataSavedHook($sMessageConsumer, TdbShopOrder $oOrder)
    {
        return true;
    }

    /**
     * store the order id in session - this allows us to load the last order created on the thank you page
     * via $this->GetLastCreatedOrder.
     *
     * @param TdbShopOrder $oOrder
     */
    protected function SaveOrderIdAsLastCreatedOrderInSession($oOrder)
    {
        $_SESSION[self::SESSION_KEY_LAST_CREATED_ORDER_ID] = $oOrder->id;
        $myOrderHistory = $this->myOrderDetailHandlerFactory();
        $myOrderHistory->addOrderIdToMyList($oOrder->id, ('' !== $oOrder->fieldDataExtranetUserId) ? $oOrder->fieldDataExtranetUserId : null);
    }

    /**
     * called if the payment process returend success to complete the order
     * return true, if the hook completed successfully.. false if not.
     * The Method is usually called via $this->CreateOrder(...) - hower, it may also be called to
     * complete the order process after the user returns from an external payment handler called bby the ExecutePayment method.
     *
     * @param TdbShopOrder          $oOrder
     * @param TdbShopPaymentHandler $oPaymentHandler
     *
     * @return bool
     */
    public function OnPaymentSuccessHook(TdbShopOrder $oOrder, $oPaymentHandler, $sMessageConsumer)
    {
        $bPostPaymentOk = $oPaymentHandler->PostExecutePaymentHook($oOrder, $sMessageConsumer);
        $oOrder->AllowEditByAll(true);
        $oPaymentHandler->SaveUserPaymentDataToOrder($oOrder->id);
        if (true === $bPostPaymentOk) {
            // and export order for wawi - if not already exported (as may happen, if the payment handler manages export
            if ($oOrder->fieldSystemOrderExportedDate <= '0000-00-00 00:00:00') {
                if ($oOrder->ExportOrderForWaWiHook($oPaymentHandler)) {
                    $oOrder->MarkOrderAsExportedToWaWi(true);
                }
            }

            // send notification only if not already send
            if (false == $oOrder->fieldSystemOrderNotificationSend) {
                $oOrder->SendOrderNotification(); // send notification after wawi export (should the wawi export return an external bill number, then we can include it in the notification mail)
            }
            // clear basket
            $this->ClearBasket();
        } else {
            $sErrorMsg = $this->OnPaymentErrorHook($bPostPaymentOk, $oOrder, $oPaymentHandler);
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-PAYMENT-METHOD-REJECTED', array('errorMsg' => $sErrorMsg));
            $bPostPaymentOk = false;
        }

        return $bPostPaymentOk;
    }

    /**
     * called when the payment handler returns something other than true
     * The method should return the error string to display.
     *
     * @param string                $bPaymentErrorCode - the error code
     * @param TdbShopOrder          $oOrder            - the order just created
     * @param TdbShopPaymentHandler $oPaymentHandler
     *
     * @return string
     */
    protected function OnPaymentErrorHook($bPaymentErrorCode, &$oOrder, &$oPaymentHandler)
    {
        // payment failed... delete order
        $oOrder->SetStatusCanceled(true); // cancele the order... but do NOT delete.
        $sErrorMsg = 'Unbekannter Fehler - bitte prüfen Sie Ihre Adressdaten und ggf. Kreditkarteninformationen. Vermeiden Sie Leerzeichen, Sonderzeichen und ungewöhnliche Angaben wie z.B: sehr lange Hausnummernangeben (Packstation-Informationen bitte ins Straßen-Feld eingeben)';

        return $sErrorMsg;
    }

    /**
     * returns the order object created last within the current session of the user.
     *
     * @param bool $bResetValue - set to true if you want to reset the session item
     *
     * @return TdbShopOrder
     */
    public static function &GetLastCreatedOrder($bResetValue = false)
    {
        $oOrder = null;
        if (array_key_exists(self::SESSION_KEY_LAST_CREATED_ORDER_ID, $_SESSION)) {
            $oOrder = TdbShopOrder::GetNewInstance();
            /** @var $oOrder TdbShopOrder */
            if ($oOrder->Load($_SESSION[self::SESSION_KEY_LAST_CREATED_ORDER_ID])) {
                // now we want to make sure that the current user is the owner of that order. this is
                // the case if: the user is logged in and the user id in the order matches, or the user is
                // not logged in and the order has no user id
                $oUser = TdbDataExtranetUser::GetInstance();
                $isOwner = ($oUser->IsLoggedIn() && $oOrder->fieldDataExtranetUserId == $oUser->id);
                $isOwner = ($isOwner || (!$oUser->IsLoggedIn() && $oOrder->fieldDataExtranetUserId < 1));
                if (!$isOwner) {
                    $oOrder = null;
                }
            } else {
                $oOrder = null;
            }
            if ($bResetValue) {
                unset($_SESSION[self::SESSION_KEY_LAST_CREATED_ORDER_ID]);
            }
        }

        return $oOrder;
    }

    protected function isReorderDueToDoubleClick()
    {
        $bIsReorder = false;
        $oLastOrder = TShopBasket::GetLastCreatedOrder();
        if (is_object($oLastOrder)) {
            $iLastOrderCreated = strtotime($oLastOrder->fieldDatecreated);
            if (((time() - $iLastOrderCreated)) < 10) {
                $bIsReorder = true;
            }
        }

        return $bIsReorder;
    }

    /**
     * return true if the basket may be saved as an order, false if we want to prevent saving the order.
     *
     * @param string $sMessageConsumer       - message manager who we want to send errors to
     * @param bool   $bSkipPaymentValidation - set to true if you want to skip the validation of the selected payment method
     *
     * @return bool
     */
    protected function CreateOrderAllowCreation($sMessageConsumer, $bSkipPaymentValidation = false)
    {
        $oMsgManager = TCMSMessageManager::GetInstance();
        $bAllowCreation = true;

        if ($bAllowCreation) {
            if ($this->LockIsActive()) {
                // the order has already been created...
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-CREATE-ON-ALREADY-CREATED-ORDER');
                $bAllowCreation = false;
                // kill basket, and reset key
                $oBasket = TShopBasket::GetInstance();
                $this->UnlockBasket();
                $oBasket->ClearBasket();
                unset($oBasket);
            }
        }

        if ($bAllowCreation) {
            // prevent order creation if the last order is less then 10 seconds ago - the system will prevent this anyway, but we need to
            // show the user a sensible error message in this case
            if (true === $this->isReorderDueToDoubleClick()) {
                $bAllowCreation = false;
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-CREATE-ON-ALREADY-CREATED-ORDER');
            }
        }

        if ($bAllowCreation) {
            // now check if there are article in the basket
            if ($this->dCostTotal < 0) {
                // negativ orders are not permitted
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-CREATE-NEGATIV-ORDER');
                $bAllowCreation = false;
            }
        }

        if ($bAllowCreation) {
            if ($this->iTotalNumberOfUniqueArticles < 1 || $this->dTotalNumberOfArticles <= 0) {
                // no articles in order
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-CREATE-EMPTY-ORDER');
                $bAllowCreation = false;
            }
        }

        if ($bAllowCreation) {
            // check to make sure we have a billing addres for the user
            $oUser = TdbDataExtranetUser::GetInstance();
            $oBillingAdr = $oUser->GetBillingAddress();
            if (is_null($oBillingAdr) || !$oBillingAdr->ContainsData()) {
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-USER-HAS-NO-BILLING-ADDRESS');
                $bAllowCreation = false;
            }
        }

        if ($bAllowCreation) {
            // check if shipping and payment have been selected
            if (false == $this->HasValidShippingGroup()) {
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-SHIPPING-GROUP-NOT-VALID');
                $bAllowCreation = false;
            }
        }

        if ($bAllowCreation) {
            if (!$bSkipPaymentValidation) {
                if (false == $this->HasValidPaymentMethod()) {
                    $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-METHOD-NOT-VALID');
                    $bAllowCreation = false;
                }
            }
        }

        return $bAllowCreation;
    }

    /**
     * return true if the basket as a valid shipping group selected.
     *
     * @return bool
     */
    protected function HasValidShippingGroup()
    {
        $bHasValidShippingGroup = false;
        $oShipping = $this->GetActiveShippingGroup();
        if (!is_null($oShipping) && $oShipping->IsAvailable()) {
            $bHasValidShippingGroup = true;
        }

        return $bHasValidShippingGroup;
    }

    /**
     * return true if the basket as a valid payment method selected.
     *
     * @return bool
     */
    protected function HasValidPaymentMethod()
    {
        $bHasValidPaymentMethod = false;
        $oPayment = $this->GetActivePaymentMethod();
        if (!is_null($oPayment) && $oPayment->IsAvailable()) {
            $bHasValidPaymentMethod = true;
        }

        return $bHasValidPaymentMethod;
    }

    /**
     * used to display the basket.
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
        $oView->AddVar('oBasket', $this);
        $oView->AddVar('oBasketArticles', $this->GetBasketArticles());
        $oView->AddVar('oActiveVatList', $this->GetActiveVATList());
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
     * returns a clone of $oBasketArticles of type TShopBasketArticleList for
     * for external processing of basket articles.
     *
     * @return TShopBasketArticleList
     */
    public function GetBasketContents()
    {
        $oClonedBasketArticles = clone $this->GetBasketArticles();

        return $oClonedBasketArticles;
    }

    /**
     * return the article lists - ALWAYS use this method to access oBasketArticles
     * this is for INTERNAL access - here we return a pointer NOT a copy. use GetBasketContents if you
     * need a copy (for external use).
     *
     * @return TShopBasketArticleList
     */
    protected function &GetBasketArticles()
    {
        if (is_null($this->oBasketArticles)) {
            $this->oBasketArticles = new TShopBasketArticleList();
        }
        // attach basket as observer - but on first call only
        $this->oBasketArticles->ObserverRegister('oBasketInstance', $this);

        return $this->oBasketArticles;
    }

    protected function setBasketArticles($oArticles)
    {
        $this->oBasketArticles = $oArticles;
    }

    protected function resetArticles()
    {
        $this->oBasketArticles = null;
    }

    /**
     * Attempts to add the voucher to the basket. Error and success messages are sent to $sMessageHandler.
     *
     * @param TdbShopVoucher $oVoucher
     * @param string         $sMessageHandler - send any messages to this message consumer
     *
     * @return bool
     */
    public function AddVoucher(TdbShopVoucher &$oVoucher, $sMessageHandler)
    {
        $bVoucherAdded = false;
        $oMessageManager = TCMSMessageManager::GetInstance();

        $oVoucherSeries = $oVoucher->GetFieldShopVoucherSeries();
        $aMessageData = $oVoucherSeries->GetObjectPropertiesAsArray();
        $aVoucherData = $oVoucher->GetObjectPropertiesAsArray();
        $aMessageData = array_merge($aMessageData, $aVoucherData);

        $cAllowUseOfVoucherResult = $oVoucher->AllowUseOfVoucher();
        if (TdbShopVoucher::ALLOW_USE != $cAllowUseOfVoucherResult) {
            if (TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP_MISSING_LOGIN == $cAllowUseOfVoucherResult ||
               TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER_MISSING_LOGIN == $cAllowUseOfVoucherResult) {
                $oExtranet = TdbDataExtranet::GetInstance();
                if (!is_null($oExtranet)) {
                    $aMessageData['sLinkLoginStart'] = '<a href="'.$oExtranet->GetLinkLoginPage().'">';
                    $aMessageData['sLinkLoginEnd'] = '</a>';
                }
            }
            $oMessageManager->AddMessage($sMessageHandler, 'VOUCHER-ERROR-'.$cAllowUseOfVoucherResult, $aMessageData);
        } else {
            $this->GetActiveVouchers()->AddItem($oVoucher);
            $oMessageManager->AddMessage($sMessageHandler, 'VOUCHER-ADDED', $aMessageData);
            $bVoucherAdded = true;
            $this->SetBasketRecalculationFlag();
        }

        return $bVoucherAdded;
    }

    /**
     * remove the voucher with the given basket key from the voucher list. Results will be sent to sMessageHandler.
     *
     * @param string $sBasketVoucherKey - The basket key of the voucher
     * @param string $sMessageHandler
     *
     * @return bool
     */
    public function RemoveVoucher($sBasketVoucherKey, $sMessageHandler)
    {
        $bRemoved = $this->GetActiveVouchers()->RemoveItem('sBasketVoucherKey', $sBasketVoucherKey);
        $oMessageManager = TCMSMessageManager::GetInstance();
        if ($bRemoved) {
            $oMessageManager->AddMessage($sMessageHandler, 'VOUCHER-REMOVED');
            $this->SetBasketRecalculationFlag();
        } else {
            $oMessageManager->AddMessage($sMessageHandler, 'VOUCHER-ERROR-NOT-FOUND');
        }

        return $bRemoved;
    }

    /**
     * return a COPY of the active voucher list - for EXTERNAL use.
     *
     * @return TShopBasketVoucherList
     */
    public function GetVoucherList()
    {
        if (!is_null($this->GetActiveVouchers())) {
            return clone $this->GetActiveVouchers();
        } else {
            return null;
        }
    }

    /**
     * GETTER: return a POINTER to the active voucher list - for INTERNAL use.
     *
     * @return TShopBasketVoucherList
     */
    protected function GetActiveVouchers()
    {
        if (is_null($this->oActiveVouchers)) {
            $this->oActiveVouchers = new TShopBasketVoucherList();
        }

        return $this->oActiveVouchers;
    }

    protected function getActiveNoneSponsoredVouchers(): TShopBasketVoucherList
    {
        $activeVouchers = $this->GetActiveVouchers();
        $noneSponsoredVouchers = new TShopBasketVoucherList();

        while (false !== ($voucher = $activeVouchers->next())) {
            if (true === $voucher->IsSponsored()) {
                continue;
            }

            $noneSponsoredVouchers->AddItem($voucher);
        }

        return $noneSponsoredVouchers;
    }

    protected function reloadVouchers()
    {
        $voucherList = $this->GetActiveVouchers();
        $voucherList->GoToStart();
        while ($voucher = $voucherList->Next()) {
            $voucher->Load($voucher->id);
        }
        $voucherList->GoToStart();
    }

    /**
     * return a COPY of the active discount list for EXTERNAL use.
     *
     * @return TShopBasketDiscountList
     */
    public function GetDiscountList()
    {
        return clone $this->GetActiveDiscounts();
    }

    /**
     * GETTER for the active discount list - for INTERNAL use.
     *
     * @return TShopBasketDiscountList
     */
    protected function GetActiveDiscounts()
    {
        if (is_null($this->oActiveDiscounts)) {
            $this->oActiveDiscounts = new TShopBasketDiscountList();
        }

        return $this->oActiveDiscounts;
    }

    /**
     * stores or updates the basket object, user data and session info in shop_order_basket
     * a unique basket identifier is created the first time you call this method for a basket
     * note: the method is normally called by the order steps - you should not need to call it from
     * a different context.
     *
     * @param bool   $bCreateNewIdent - set to true, if you want to create a new identifier (every request adds a count postfix)
     * @param string $stepSystemName  - save the stepname to basket
     *
     * @return string
     */
    public function CommitCopyToDatabase($bCreateNewIdent = false, $stepSystemName = '')
    {
        $time = time();
        $user = $this->getExtranetUserProvider()->getActiveUser();
        $request = $this->getRequest();
        if (null === $request) {
            $sessionCopy = array();
            $sessionId = '';
        } else {
            $session = $request->getSession();
            $sessionCopy = $session->all();
            $sessionId = $session->getId();
        }
        $databaseConnection = $this->getDatabaseConnection();

        if (null === $this->sBasketIdentifier) {
            $insertOk = false;
            $maxTries = 20;

            do {
                try {
                    --$maxTries;
                    $orderIdent = self::GenerateBasketOrderIdent().'000';
                    /*
                     * Check if the entry is in the order table. Values get removed from shop_order_basket periodically,
                     * and in this method we will not write to shop_order, so we need this check to ensure a unique
                     * value both in shop_order and in shop_order_basket (a unique value in shop_order_basket will be
                     * enforced by the unique constraint on the order_ident column when inserting below).
                     *
                     * It should not be possible to write a duplicate order_ident value into shop_order between
                     * this check and the completion of the order because that means there would have been a duplicate
                     * value in shop_order_basket which is prevented by this method (that requires that no order avoids
                     * the call to this method of course).
                     */
                    $query = 'SELECT `id` FROM `shop_order` WHERE `order_ident` = :ident';
                    $res = $databaseConnection->fetchColumn($query, array(
                        'ident' => $orderIdent,
                    ));
                    if (false !== $res) {
                        continue;
                    }

                    $databaseConnection->beginTransaction();
                    $query = 'INSERT INTO `shop_order_basket`
                                  SET `id` = :id,
                                      `order_ident` = :order_ident,
                                      `session_id` = :session_id,
                                      `datecreated` = :datecreated,
                                      `lastmodified` = :lastmodified,
                                      `rawdata_basket` = :rawdata_basket,
                                      `rawdata_user` = :rawdata_user,
                                      `rawdata_session` = :rawdata_session,
                                      `update_stepname` = :update_stepname
                         ';
                    $databaseConnection->executeQuery($query, array(
                        'id' => TTools::GetUUID(),
                        'order_ident' => $orderIdent,
                        'session_id' => $sessionId,
                        'datecreated' => $time,
                        'lastmodified' => $time,
                        'rawdata_basket' => base64_encode(serialize($this)),
                        'rawdata_user' => base64_encode(serialize($user)),
                        'rawdata_session' => base64_encode(serialize($sessionCopy)),
                        'update_stepname' => $stepSystemName,
                    ));
                    $databaseConnection->commit();
                    $this->sBasketIdentifier = $orderIdent;
                    $insertOk = true;
                } catch (Exception $e) {
                    if ($databaseConnection->isTransactionActive()) {
                        try {
                            $databaseConnection->rollBack();
                        } catch (DBALException $e) {
                            // ignore (we will try again and maybe exceed the maximum try count)
                        }
                    }
                }
            } while (!$insertOk && $maxTries > 0);
        } else {
            $orderIdent = $this->sBasketIdentifier;
            if ($bCreateNewIdent) {
                $orderIdent = substr($this->sBasketIdentifier, 0, 17);
                $count = ((int) substr($this->sBasketIdentifier, 17)) + 1;
                $orderIdent .= sprintf('%03s', $count);
            }
            $query = 'UPDATE `shop_order_basket`
                      SET `lastmodified` = :lastmodified,
                         `rawdata_basket` = :rawdata_basket,
                         `rawdata_user` = :rawdata_user,
                         `rawdata_session` = :rawdata_session,
                         `order_ident` = :order_ident,
                         `update_stepname` = :update_stepname
                      WHERE `order_ident` = :old_order_ident
                 ';
            $databaseConnection->executeQuery($query, array(
                'order_ident' => $orderIdent,
                'lastmodified' => $time,
                'rawdata_basket' => base64_encode(serialize($this)),
                'rawdata_user' => base64_encode(serialize($user)),
                'rawdata_session' => base64_encode(serialize($sessionCopy)),
                'update_stepname' => $stepSystemName,
                'old_order_ident' => $this->sBasketIdentifier,
            ));
            $this->sBasketIdentifier = $orderIdent;
        }

        return $this->sBasketIdentifier;
    }

    /**
     * generates a vachouer code by $iLength.
     *
     * @param int $iLength
     *
     * @return string
     */
    protected static function GenerateBasketOrderIdent()
    {
        $iLength = 17;
        $aPasswordChars = array_merge(range('0', '9'), range('a', 'z'));
        mt_srand((float) microtime() * 1000000);
        for ($i = 1; $i <= (count($aPasswordChars) * 2); ++$i) {
            $swap = mt_rand(0, count($aPasswordChars) - 1);
            $tmp = $aPasswordChars[$swap];
            $aPasswordChars[$swap] = $aPasswordChars[0];
            $aPasswordChars[0] = $tmp;
        }

        return strtoupper(substr(implode('', $aPasswordChars), 0, $iLength));
    }

    /*
    * validate the contents of the basket (such as allowable stock)
    * @param string $sMessageManager - the manager to which error messages should be sent
    * @return boolean
    */
    public function ValidateBasketContents($sMessageManager = null)
    {
        /*
         * do not validate basket if returning from an interrupted payment call since the order
         * creation has already begun (items saved, stock reduced). revalidating may remove items from the basket due to zero stock
         * which is of course not intended since the stock has been reserved via the creation process of the order
         *
         */
        if (TdbShopPaymentHandler::ExecutePaymentInterrupted()) {
            return true;
        }
        if (is_null($sMessageManager)) {
            $sMessageManager = MTShopBasketCore::MSG_CONSUMER_NAME;
        }
        $this->GetBasketArticles()->Refresh();
        $bIsValid = $this->GetBasketArticles()->ValidateBasketContents($sMessageManager);
        /**
         * validate the vouchers as they were stored in the session and
         * maybe could be used by an other order (when vouchers with the same code exist).
         */
        $bBasketVouchersValidated = $this->CheckBasketVoucherAvailable($sMessageManager);
        if (!$bIsValid) {
            $this->RecalculateBasket();
        }

        return $bIsValid;
    }

    /**
     * return the next possible discount for the current user/basket value (ignores basket contents -
     * discounts restricted to product categories or products are ignored.
     *
     * @return TdbShopDiscount;
     */
    public function GetNextAvailableDiscount()
    {
        $oNextDiscount = null;
        $oUser = TdbDataExtranetUser::GetInstance();
        //      $oDiscountList = TdbShopDiscountList::GetActiveDiscountList("`shop_discount`.`restrict_to_value_from` > ".MySqlLegacySupport::getInstance()->real_escape_string($this->dCostArticlesTotal),"`shop_discount`.`restrict_to_value_from` ASC");
        $oDiscountList = $this->GetActiveDiscounts();
        $oDiscountList->GoToStart();
        $aDiscountId = array();
        while ($oDiscount = $oDiscountList->Next()) {
            $aDiscountId[] = "(`shop_discount`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($oDiscount->id)."' AND `shop_discount`.`restrict_to_value_from` >= '".MySqlLegacySupport::getInstance()->real_escape_string($oDiscount->sqlData['restrict_to_value_from'])."' AND `shop_discount`.`restrict_to_articles_from` >= '".MySqlLegacySupport::getInstance()->real_escape_string($oDiscount->sqlData['restrict_to_articles_from'])."')";
        }
        $oDiscountList->GoToStart();
        $sRestriction = null;
        if (count($aDiscountId) > 0) {
            $sRestriction = implode(' AND ', $aDiscountId);
        }
        $oDiscountList = TdbShopDiscountList::GetActiveDiscountList($sRestriction, '`shop_discount`.`restrict_to_value_from` ASC');

        $oActiveDiscounts = $this->GetActiveDiscounts();
        while (is_null($oNextDiscount) && ($oDiscount = $oDiscountList->Next())) {
            // exclude discounts that already act on the basket

            if (TdbShopDiscount::ALLOW_USE == $oDiscount->AllowUseOfDiscount(TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_QUANTITY | TdbShopDiscount::DISABLE_CHECK_CURRENT_BASKET_VALUE)) {
                $oNextDiscount = $oDiscount;
            }
        }
        $oDiscountList->GoToStart();

        return $oNextDiscount;
    }

    /**
     * Check all vouchers in basket and check if voucher was not in use by other order.
     * If one voucher was used in other order try to get another one with in the same series and voucher code.
     *
     * @param string $sMessageConsumer
     *
     * @return bool
     */
    protected function CheckBasketVoucherAvailable($sMessageConsumer)
    {
        $bBasketVoucherAvailable = true;
        $oVoucherList = $this->GetVoucherList();
        $oMessageManager = TCMSMessageManager::GetInstance();
        if ($oVoucherList) {
            if ($oVoucherList->Length() > 0) {
                $oVoucherList->GoToStart();
                while ($oBasketVoucher = $oVoucherList->Next()) {
                    $sVoucherCode = $oBasketVoucher->fieldCode;
                    $sBasketVoucherKey = $oBasketVoucher->sBasketVoucherKey;
                    $sBasketVoucherSeriesId = $oBasketVoucher->fieldShopVoucherSeriesId;
                    $oVoucher = TdbShopVoucher::GetNewInstance();
                    $bVoucherLoaded = $oVoucher->Load($oBasketVoucher->id);
                    if (($bVoucherLoaded && $oVoucher->fieldIsUsedUp) || !$bVoucherLoaded) {
                        $oNextAvailableVoucher = $this->GetNextAvailableVoucher($sVoucherCode, $sBasketVoucherSeriesId, $sMessageConsumer);

                        /**
                         * next voucher with same code is available, so auto add that one.
                         * as we do not want to inform the customer that this happened,
                         * we create a temporary message consumer for RemoveVoucher and AddVoucher messages and clear them afterwards.
                         */
                        if (!is_null($oNextAvailableVoucher)) {
                            $this->RemoveVoucher($sBasketVoucherKey, $sMessageConsumer.'-TO-BE-REMOVED');
                            $this->AddVoucher($oNextAvailableVoucher, $sMessageConsumer.'-TO-BE-REMOVED');
                            $oMessageManager->ClearMessages($sMessageConsumer.'-TO-BE-REMOVED');
                        } else {
                            $this->RemoveVoucher($sBasketVoucherKey, $sMessageConsumer);
                            $bBasketVoucherAvailable = false;
                            $this->bMarkAsRecalculationNeeded = true;
                            $oMsgManager = TCMSMessageManager::GetInstance();
                            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-VOUCHER-NOT-LONGER-AVAILABLE', array('sVoucherCode' => $sVoucherCode));
                        }
                    }
                }
            }
        }

        return $bBasketVoucherAvailable;
    }

    /**
     * Get next available voucher for whith given vocuehr code and series id.
     * Was used after deleting vouchers used in other order.
     *
     * @param string $sVoucherCode
     * @param string $sSeriesId
     * @param string $sMessageConsumer
     *
     * @return TdbShopVoucher
     */
    protected function GetNextAvailableVoucher($sVoucherCode, $sSeriesId, $sMessageConsumer)
    {
        $oNextAvailableVoucher = null;
        $oVoucher = TdbShopVoucher::GetNewInstance();
        if ($oVoucher->LoadFromFields(array('code' => $sVoucherCode, 'is_used_up' => '0', 'shop_voucher_series_id' => $sSeriesId))) {
            $oNextAvailableVoucher = $oVoucher;
        }

        return $oNextAvailableVoucher;
    }

    /**
     * once a basket is locked, it can not be created a second time. the lock should
     * be obtained when the create basket is called and reset if the create fails
     * It will also be reset by the clear basekt method - and if a payment request fails.
     *
     * We need the locking mechanism to prevent a user from executing the same basket more than once
     * by, for example, double clicking the order button
     */
    public function LockBasket()
    {
        $_SESSION[self::SESSION_KEY_PROCESSING_BASKET] = '1';
    }

    /**
     * remove the basekt lock. once the lock is removed, it is possible to create an an order again.
     */
    public function UnlockBasket()
    {
        if (array_key_exists(self::SESSION_KEY_PROCESSING_BASKET, $_SESSION)) {
            unset($_SESSION[self::SESSION_KEY_PROCESSING_BASKET]);
        }
    }

    /**
     * returns true, if the basket is currently locked.
     *
     * @return bool
     */
    protected function LockIsActive()
    {
        return array_key_exists(self::SESSION_KEY_PROCESSING_BASKET, $_SESSION) && '1' == $_SESSION[self::SESSION_KEY_PROCESSING_BASKET];
    }

    /**
     * the hook is triggered when the basket item list contained in the basket changed an article.
     *
     * @param TShopBasketArticle $oBasketItemChanged
     */
    public function OnBasketItemUpdateEvent($oBasketItemChanged)
    {
        $event = new \ChameleonSystem\ShopBundle\objects\TShopBasket\BasketItemEvent(
            TdbDataExtranetUser::GetInstance(),
            $this,
            $oBasketItemChanged
        );
        $this->getEventDispatcher()->dispatch(\ChameleonSystem\ShopBundle\ShopEvents::BASKET_UPDATE_ITEM, $event);
    }

    /**
     * the hook is triggered when the basket item list contained in the basket deletes an article.
     *
     * @param TShopBasketArticle $oBasketItemRemoved
     */
    public function OnBasketItemDeleteEvent($oBasketItemRemoved)
    {
        $event = new \ChameleonSystem\ShopBundle\objects\TShopBasket\BasketItemEvent(
            TdbDataExtranetUser::GetInstance(),
            $this,
            $oBasketItemRemoved
        );
        $this->getEventDispatcher()->dispatch(\ChameleonSystem\ShopBundle\ShopEvents::BASKET_DELETE_ITEM, $event);
    }

    public function custom_wakeup()
    {
        $requestInfoService = $this->getRequestInfoService();
        if ($requestInfoService->isBackendMode()) {
            return;
        }

        $this->ValidateBasketContents();
        $this->checkLanguageChangesAfterWakeup();

        $oUser = TdbDataExtranetUser::GetInstance();
        $oUser->ObserverRegister('oUserBasket', $this);
    }

    private function checkLanguageChangesAfterWakeup()
    {
        if (false === ACTIVE_TRANSLATION) {
            return;
        }

        /** @var LanguageServiceInterface $languageService */
        $languageService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.language_service');
        $activeLanguage = $languageService->getActiveLanguage();

        $this->GetBasketArticles()->GoToStart();
        while ($oArticle = $this->GetBasketArticles()->Next()) {
            if (null !== $activeLanguage && $oArticle->GetLanguage() != $activeLanguage->id) {
                $this->bMarkAsRecalculationNeeded = true;
                break;
            }
        }
        $this->GetBasketArticles()->GoToStart();

        // On language change reload active payment method
        $oActivePaymentMethod = $this->GetActivePaymentMethod();
        if (null !== $activeLanguage && !is_null($oActivePaymentMethod) && $oActivePaymentMethod->GetLanguage() != $activeLanguage->id) {
            $oNewPaymentMethod = TdbShopPaymentMethod::GetNewInstance();
            if ($oNewPaymentMethod->Load($oActivePaymentMethod->id)) {
                $oPaymentHandler = $oNewPaymentMethod->GetFieldShopPaymentHandler();
                $oldPaymentHandler = $oActivePaymentMethod->GetFieldShopPaymentHandler();
                $oPaymentHandler->SetPaymentUserData($oldPaymentHandler->GetUserPaymentDataWithoutLoading());
                $this->SetActivePaymentMethod($oNewPaymentMethod);
            }
        }
        // On language change reload active shipping group
        $oActiveShippingGroup = $this->GetActiveShippingGroupWithoutLoading();
        if (null !== $activeLanguage && !is_null($oActiveShippingGroup) && $oActiveShippingGroup->GetLanguage() != $activeLanguage->id) {
            $oNewShippingGroup = TdbShopShippingGroup::GetNewInstance();
            if ($oNewShippingGroup->Load($oActiveShippingGroup->id)) {
                $this->SetActiveShippingGroup($oNewShippingGroup);
            }
        }
    }

    /**
     * @param IPkgCmsEvent $oEvent
     *
     * @return IPkgCmsEvent
     *                      the method is called when an event is triggered
     */
    public function sessionWakeupHook()
    {
        $this->custom_wakeup();
    }

    /**
     * @return IPkgShopViewMyOrderDetails
     */
    public function myOrderDetailHandlerFactory()
    {
        /** @var Request $request */
        $request = \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();

        return new TPkgShopViewMyOrderDetails(
            new TPkgShopViewMyOrderDetailsDbAdapter(\ChameleonSystem\CoreBundle\ServiceLocator::get(
                'database_connection'
            )),
            new TPkgShopViewMyOrderDetailsSessionAdapter($request->getSession())
        );
    }

    /**
     * @return IPkgCmsCoreLog
     *
     * @deprecated since 6.3.0 - use getLogger() instead
     */
    protected function getOrderLogger()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.log.order');
    }

    protected function getLogger(): LoggerInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order');
    }

    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('event_dispatcher');
    }

    /**
     * @return Connection
     */
    private function getDatabaseConnection()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
    }

    /**
     * @return Request|null
     */
    private function getRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    private function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }

    /**
     * @return \ChameleonSystem\CoreBundle\Service\RequestInfoServiceInterface
     */
    private function getRequestInfoService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.request_info_service');
    }
}
