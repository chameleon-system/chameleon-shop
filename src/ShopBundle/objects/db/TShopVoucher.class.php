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
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

/**
 * Im System können Gutscheine hinterlegt werden, die einen absoluten oder prozentualen Wert als Nachlass gewähren. Prozentuale Nachlässe beziehen sich immer auf die Bruttosumme der Produkte, bevor Rabatte oder Ähnliches angerechnet werden. Zusätzlich kann bei einem Gutschein ein Gratisartikel hinterlegt werden. Wird ein solcher Gutschein mit Gratisartikel eingelöst, legt das System den Artikel mit einem Wert von 0€ in den Warenkorb. Zusätzlich erscheint der Hinweis über welchen Gutschein der Artikel in den Warenkorb gelegt wurde. Neben dem Hinweis erscheint zusätzlich ein Link über den der Gutschein wieder aus dem Warenkorb genommen werden kann.
 *
 * Geldwerte Gutscheine erscheinen unterhalb der Produktbruttosumme mit Namen und Wert. Zusätzlich wird neben jedem Gutschein ein Link angezeigt über den der Gutschein wieder aus dem Warenkorb genommen werden kann.
 * Gutscheine werden generell in Gutscheinserien angelegt. Alle Einstellungen beziehen sich auf die Gutscheinserie, und damit auf alle Gutscheine in dieser Gutscheinserie. Für eine Gutscheinserie können automatisch Gutscheine generiert werden. Hier kann entweder ein fester Gutscheincode angegeben werden, oder vom System für jeden Gutschein automatisch generiert werden.
 *
 * Innerhalb einer Gutscheinserie darf ein Gutscheincode mehr als einmal vorkommen, ein Gutscheincode darf aber nie in mehr als einer Gutscheinserie verwendet werden. Bei jedem Gutschein wird ein Erstellungsdatum hinterlegt.
 *
 * Der Gutscheinwert selbst wird immer aus der Gutscheinserie genommen (verändert man diesen Wert, verändert sich auch automatisch der Gutscheinwert). Bei jeder Verwendung eines Gutscheins, wird das Datum, der Benutzer, sowie der Verbrauchswert beim Gutschein in der Gutscheinverwendungsliste hinterlegt. Sobald der Gutschein komplett verbraucht ist, wird das Verbrauchsdatum hinterlegt und der Gutschein als verbraucht markiert.
 * Die Gutscheine einer Gutscheinserie können als CSV Exportiert werden. Für jeden Gutschein werden folgende Daten exportiert:
 * • Code
 * • Datum
 * • Verbraucht (Ja/Nein)
 * • Verbrauchsdatum
 * • Restwert
 *
 * Gutscheine können für alle Benutzer verwendbar sein, oder auf bestimmte Benutzergruppen oder Benutzer eingeschränkt werden.
 *
 * Gutscheine können einen Gutscheinsponsor haben der für den Gutschein gezahlt hat. Gutscheinsponsoren können zusätzlich zur Gutscheininformation auf der Webseite mit Name und Bild angezeigt werden.
 *
 * Gutscheine (die nicht „gesponsert“ und damit nicht tatsächlich bezahlt sind) dürfen nicht auf Artikel greifen, für die die Buchpreisbindung gilt. Die für den Gutschein relevante Mindestbestellsumme, sowie der Warenkorbwert der nicht unter null fallen darf bezieht sich immer auf die Summe der Artikel die keiner Buchpreisbindung unterliegen.
 *
 * Folgende zusätzliche Einstellungen und Einschränkungen sind möglich:
 * • Über ein Start- und Enddatum kann ein Gutschein auf eine Zeitspanne eingeschränkt werden.
 *
 * • Es kann ein Mindestbestellwert definiert werden, der überschritten werden muss, bevor der Gutschein akzeptiert wird.
 *
 * • Ob der Gutschein mit einem anderen Gutschein der gleichen Gutscheinserie verwendet werden kann
 * • Ob der Gutschein mit anderen Gutscheinen (egal von welcher Gutscheinserie) verwendet werden kann.
 *
 * • Ob ein Kunde Gutscheine dieser Gutscheinserie generell nur einmal verwenden darf (also auch nicht bei zwei getrennten Bestellungen)
 *
 * • Ob der Gutschein nur bei der ersten Bestellung des Benutzers verwendet werden kann.
 *
 * • Ob der Gutschein die Versandkosten aufheben soll.
 *
 * • Es ist möglich den Gutschein als einen „gesponserten“ Gutschein zu Markieren. Zusätzlich kann der Name des Sponsors, ein Bild, und ein Logo hinterlegt werden. „Gesponserte“ Gutscheine ignorieren die Buchpreisbindungseinstellungen der Warenkorbartikel im Warenkorb.
 * Die für Gutscheine relevanten Berechnungsregeln werden im Detail unter 3.a.i beschrieben.
/**/
class TShopVoucher extends TShopVoucherAutoParent
{
    /**
     * constants below define the status returned when checking if a voucher can be
     * used in the current basket.
     */

    /**
     * voucher may be used.
     */
    const ALLOW_USE = 0;

    /**
     * the voucher series to which this voucher belongs is currently inactive (or the active date is out of range).
     */
    const USE_ERROR_SERIES_INACTIVE = 1;

    /**
     * the basket value is below the minimum value specified for the voucher series.
     */
    const USE_ERROR_BASKET_VALUE_TO_LOW = 2;

    /**
     * another voucher of the same series is already used within the basket even though
     * the voucher series has been defined as "only one of the same series per basket".
     */
    const USE_ERROR_VOUCHER_WITH_SAME_SERIES_USED = 4;

    /**
     * another voucher is already being used within the basket even though
     * the voucher series has been defined as "may not be used with any other voucher".
     */
    const USE_ERROR_OTHER_VOUCHER_USED = 8;

    /**
     * the customer used a voucher of this series before for a previous order even though
     * the series has been marked as "only one voucher per customer".
     */
    const USE_ERROR_CUSTOMER_USED_VOUCHER_SERIES_BEFORE = 16;

    /**
     * this is not the first order from the customer, but the voucher series has been defined
     * as "use only on first order".
     */
    const USE_ERROR_NOT_FIRST_ORDER = 32;

    /**
     * the voucher has been restricted to a set of customers, and the current customer is not in that list.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER = 64;

    /**
     * the voucher has been restricted to a set of customer groups, none of which the current customer is in.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP = 128;

    /**
     * the voucher may not be used for the current basket.
     */
    const USE_ERROR_NOT_VALID_FOR_CURRENT_BASKET = 256;

    /**
     * the voucher has been restricted to a set of customers, and the current customer is not logged in.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER_MISSING_LOGIN = 512;

    /**
     * the voucher has been restricted to a set of customer groups, and the current customer is in not logged in.
     */
    const USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP_MISSING_LOGIN = 1024;

    /**
     * each voucher added to the basket gets a unique voucher key used to identify the
     * voucher in the basket. the value will be used in the IsSameAs method if set (otherwise the IsSameAs will perform
     * like the parent method).
     *
     * @var string
     */
    public $sBasketVoucherKey = null;

    /**
     * holds the result of GetValue - the value of the voucher within the context of the current basket.
     *
     * @var float
     */
    private $voucherValueInBasket = 0.0;

    /**
     * return a link to remove the voucher from the basket.
     *
     * @param string $sMessageHandler - the message handler which should be used to display the result
     *                                - if nothing is specified, MTShopBasketCore::MSG_CONSUMER_NAME will be used
     *
     * @return string
     */
    public function GetRemoveFromBasketLink($sMessageHandler = null)
    {
        if (is_null($sMessageHandler)) {
            $sMessageHandler = MTShopBasketCore::MSG_CONSUMER_NAME;
        }
        $oShopConfig = $this->getShopService()->getActiveShop();

        return $this->getActivePageService()->getLinkToActivePageRelative(array(
            'module_fnc' => array(
                $oShopConfig->GetBasketModuleSpotName() => 'RemoveVoucher',
            ),
            MTShopBasketCore::URL_REQUEST_PARAMETER => array(
                MTShopBasketCore::URL_MESSAGE_CONSUMER_NAME => $sMessageHandler,
                MTShopBasketCore::URL_VOUCHER_BASKET_KEY => $this->sBasketVoucherKey,
            ),
        ));
    }

    /**
     * @return void
     */
    protected function PostWakeUpHook()
    {
        // the series holds info about the voucher - including for example min value. changes in this should affect the voucher right away. so clear the internal cache on wakeup
        $sClear = null;
        $this->SetInternalCache('oLookupshop_voucher_series_id', $sClear);
        parent::PostWakeUpHook();
    }

    /**
     * Enter description here...
     *
     * @param TdbShopVoucher $oItem
     *
     * @return bool
     */
    public function IsSameAs($oItem)
    {
        if (!is_null($this->sBasketVoucherKey) || !is_null($oItem->sBasketVoucherKey)) {
            return 0 == strcmp($this->sBasketVoucherKey, $oItem->sBasketVoucherKey);
        } else {
            return parent::IsSameAs($oItem);
        }
    }

    /**
     * The method checks if the voucher can be used by the active user and the active basket.
     * If function returns 0 the you can use the voucher.
     *
     * @return int
     */
    public function AllowUseOfVoucher()
    {
        $bAllowUse = TdbShopVoucher::ALLOW_USE;

        $oBasket = TShopBasket::GetInstance();
        $oExistingVouchers = $oBasket->GetVoucherList();
        if (!is_null($oExistingVouchers)) {
            $oMatchingVoucher = $oExistingVouchers->FindItemWithProperty('id', $this->id);
            if ($oMatchingVoucher) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_OTHER_VOUCHER_USED;
            }
        }

        $oSeries = $this->GetFieldShopVoucherSeries();
        $oUser = TdbDataExtranetUser::GetInstance();

        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            // check if the series is active
            if (!$oSeries->IsActive()) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_SERIES_INACTIVE;
            }
        }

        // get articles affected by the voucher - we need at least one matching product in order for the voucher
        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            $iNumberOfItemsAffected = $oBasket->GetBasketQuantityForVoucher($this);
            if (0 == $iNumberOfItemsAffected) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CURRENT_BASKET;
            }
        }

        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            // note: we are interested in the total product value (- produts that can not be used for the voucher) - vouchers used
            $dRelevantValue = $oBasket->GetBasketSumForVoucher($this) - $oBasket->dCostVouchers;
            if ($oSeries->fieldRestrictToValue > 0 && ($dRelevantValue < $oSeries->fieldRestrictToValue || $dRelevantValue <= 0)) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_BASKET_VALUE_TO_LOW;
            }
        }

        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            // check if another voucher of the same series is already in use... if this voucher is restricted to one per series per purchase
            $oActiveVoucherList = $oBasket->GetVoucherList();
            if (null !== $oActiveVoucherList && $oActiveVoucherList->Length() > 0) {
                if ($oSeries->fieldAllowNoOtherVouchers) {
                    $bAllowUse = TdbShopVoucher::USE_ERROR_OTHER_VOUCHER_USED;
                } else {
                    // check if other vouchers have that restriction
                    $oActiveVoucherList->GoToStart();

                    while (TdbShopVoucher::ALLOW_USE == $bAllowUse && ($oTmpVoucher = $oActiveVoucherList->Next())) {
                        if ($oTmpVoucher->GetFieldShopVoucherSeries()->fieldAllowNoOtherVouchers) {
                            $bAllowUse = TdbShopVoucher::USE_ERROR_OTHER_VOUCHER_USED;
                        }
                    }
                    $oActiveVoucherList->GoToStart();
                }
            } elseif (null !== $oActiveVoucherList && $oSeries->fieldRestrictToOtherSeries) {
                while ((TdbShopVoucher::ALLOW_USE == $bAllowUse) && ($oTmpVoucher = $oActiveVoucherList->Next())) {
                    $oTmpSeries = $oTmpVoucher->GetFieldShopVoucherSeries();
                    if ($oTmpSeries->IsSameAs($oSeries)) {
                        $bAllowUse = TdbShopVoucher::USE_ERROR_VOUCHER_WITH_SAME_SERIES_USED;
                    }
                }
                $oActiveVoucherList->GoToStart();
            }
        }

        if (TdbShopVoucher::ALLOW_USE == $bAllowUse && $oSeries->fieldRestrictToOnePerUser) {
            // check if the user has used a voucher of this series before
            if (!is_null($oUser->id) && !empty($oUser->id)) {
                $iNumberOfTimesUsed = $oSeries->NumberOfTimesUsedByUser($oUser->id, array($this->id));
                if ($iNumberOfTimesUsed > 0) {
                    $bAllowUse = TdbShopVoucher::USE_ERROR_CUSTOMER_USED_VOUCHER_SERIES_BEFORE;
                }
            }
        }

        // if the voucher may only be used with the first order, check if the user has ordered before
        if (TdbShopVoucher::ALLOW_USE == $bAllowUse && $oSeries->fieldRestrictToFirstOrder) {
            $oUserOrders = TdbShopOrderList::GetListForDataExtranetUserId($oUser->id);
            if ($oUserOrders->Length() > 0) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_FIRST_ORDER;
            }
        }

        $bUserLoggedIn = $oUser->IsLoggedIn();
        // if the voucher is restricted to some user, make sure this user is in that list
        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            $aUserListRestricton = $oSeries->GetMLTIdList('data_extranet_user', 'data_extranet_user_mlt');
            if (count($aUserListRestricton) > 0 && true === $bUserLoggedIn && !in_array($oUser->id, $aUserListRestricton)) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER;
            } elseif (count($aUserListRestricton) > 0 && false === $bUserLoggedIn) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER_MISSING_LOGIN;
            }
        }

        // if the voucher is restricted to some user group, make sure this user is in that list
        if (TdbShopVoucher::ALLOW_USE == $bAllowUse) {
            $aUserGroupListRestricton = $oSeries->GetMLTIdList('data_extranet_group', 'data_extranet_group_mlt');
            if (count($aUserGroupListRestricton) > 0 && true === $bUserLoggedIn && !$oUser->InUserGroups($aUserGroupListRestricton)) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP;
            } elseif (count($aUserGroupListRestricton) > 0 && false === $bUserLoggedIn) {
                $bAllowUse = TdbShopVoucher::USE_ERROR_NOT_VALID_FOR_CUSTOMER_GROUP_MISSING_LOGIN;
            }
        }

        return $bAllowUse;
    }

    /**
     * Returns the value of the voucher - takes the current basket and user into consideration.
     *
     * @param bool  $bCalculateVoucher - set to true when we calculate the voucher value for the basket
     *                                 this should only be set to true when calling the method through the TShopBasketVoucherList::GetVoucherValue
     * @param float $dMaxValueAllowed  - if a value is passed, then the voucher will never exceed the value passed
     * @param bool $bSponsoredVouchers
     *
     * @return float|null
     * @psalm-return ($bCalculateVoucher is true ? float : float|null)
     */
    public function GetValue($bCalculateVoucher = false, $dMaxValueAllowed = null, $bSponsoredVouchers = false)
    {
        if ($bCalculateVoucher) {
            $oSeries = $this->GetFieldShopVoucherSeries();
            $dValue = $oSeries->fieldValue;
            $oBasket = TShopBasket::GetInstance();
            $dBasketValueApplicableForVoucher = $oBasket->GetBasketSumForVoucher($this);
            if ('prozent' == $oSeries->fieldValueType) {
                $dValue = round($dBasketValueApplicableForVoucher * ($dValue / 100), 2);
            } else {
                // now we need to subtract the amount already used...
                $dValue = $dValue - $this->GetValuePreviouslyUsed_GetValueHook();
            }

            // now if the voucher is worth more than the current basket, we need to use only the part that we can
            if ($dBasketValueApplicableForVoucher < $dValue) {
                $dValue = $dBasketValueApplicableForVoucher;
            }
            if ($dValue > $dMaxValueAllowed) {
                $dValue = $dMaxValueAllowed;
            }

            // if this is a NOT sponsored voucher, we need to reduce the article value
            if (false == $bSponsoredVouchers) {
                $oBasket->ApplyNoneSponsoredVoucherValueToItems($this, $dValue);
            }
            $this->voucherValueInBasket = $dValue;
        }

        return $this->voucherValueInBasket;
    }

    /**
     * special hook for the GetValue Method.
     *
     * @return float
     */
    protected function GetValuePreviouslyUsed_GetValueHook()
    {
        return $this->GetValuePreviouslyUsed();
    }

    /**
     * return the amount of the voucher used up in previous orders.
     *
     * @return int
     */
    public function GetValuePreviouslyUsed()
    {
        // we have to make sure that is value is accurate... so we will fetch it from database every time
        // performance should not be a big issue, since it only affects users that include a voucher in their basket
        $dValueUsed = 0;
        $query = "SELECT SUM(`value_used`) AS totalused
                  FROM `shop_voucher_use`
            INNER JOIN `shop_order` ON `shop_voucher_use`.`shop_order_id` = `shop_order`.`id`
                 WHERE `shop_voucher_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($this->id)."'
                   AND `shop_order`.`canceled` = '0'
                 ";
        if ($aValue = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            if (null !== $aValue['totalused']) {
                $dValueUsed = $aValue['totalused'];
            }
        }

        return $dValueUsed;
    }

    /**
     * marks the voucher as used in the database, and logs the voucher  data for the current user
     * in the shop_voucher_use table.
     *
     * @param int $iShopOrderId - the order for which the voucher is being used
     *
     * @return void
     */
    public function CommitVoucherUseForCurrentUser($iShopOrderId)
    {
        $oShopVoucherUse = TdbShopVoucherUse::GetNewInstance();
        /** @var $oShopvoucherUse TdbShopVoucherUse */
        $aData = array('shop_voucher_id' => $this->id, 'date_used' => date('Y-m-d H:i:s'), 'value_used' => $this->GetValue(), 'shop_order_id' => $iShopOrderId);
        // hook to post-convert value (as may be required when dealing with currency)
        $this->CommitVoucherUseForCurrentUserPreSaveHook($aData);
        $oShopVoucherUse->LoadFromRow($aData);
        $oShopVoucherUse->AllowEditByAll(true);
        $oShopVoucherUse->Save();

        if ($this->checkMarkVoucherAsCompletelyUsed()) {
            $this->MarkVoucherAsCompletelyUsed();
        }
    }

    /**
     * if the voucher
     *  1.) has an absolute value, a sponsor, and if the value has been used up
     *  2.) has an absolute value, no sponsor
     *  3.) has an relative value
     * we need to mark the voucher as used.
     *
     * @return bool
     */
    public function checkMarkVoucherAsCompletelyUsed()
    {
        $bMarkVoucherAsCompletelyUsed = false;
        $oVoucherSeries = $this->GetFieldShopVoucherSeries();
        if ('absolut' == $oVoucherSeries->fieldValueType && !is_null($oVoucherSeries->GetFieldShopVoucherSeriesSponsor())) {
            $dValueUsed = $this->GetValuePreviouslyUsed();
            if ($dValueUsed >= $this->GetVoucherSeriesOriginalValue()) {
                $bMarkVoucherAsCompletelyUsed = true;
            }
        } elseif ('absolut' == $oVoucherSeries->fieldValueType && is_null($oVoucherSeries->GetFieldShopVoucherSeriesSponsor())) {
            $bMarkVoucherAsCompletelyUsed = true;
        } else {
            $bMarkVoucherAsCompletelyUsed = true;
        }

        return $bMarkVoucherAsCompletelyUsed;
    }

    /**
     * return the original value of the connected series.
     *
     * @return float|null
     */
    protected function GetVoucherSeriesOriginalValue()
    {
        $oVoucherSeries = $this->GetFieldShopVoucherSeries();

        return $oVoucherSeries->fieldValue;
    }

    /**
     * method can be used to process the use data before the commit is called.
     *
     * @param array $aData
     *
     * @return void
     */
    protected function CommitVoucherUseForCurrentUserPreSaveHook(&$aData)
    {
    }

    /**
     * mark the voucher as Completely used.
     *
     * @return void
     */
    public function MarkVoucherAsCompletelyUsed()
    {
        $aData = $this->sqlData;
        $aData['date_used_up'] = date('Y-m-d H:i:s');
        $aData['is_used_up'] = '1';
        $this->LoadFromRow($aData);
        $bEditState = $this->bAllowEditByAll;
        $this->AllowEditByAll(true);
        $this->Save();
        $this->AllowEditByAll($bEditState);
    }

    /**
     * return true if the voucher may be used for the article.
     *
     * @param TShopBasketArticle $oArticle
     *
     * @return bool
     */
    public function AllowVoucherForArticle(TShopBasketArticle $oArticle)
    {
        $bMayBeUsed = true;

        $oVoucherDef = $this->GetFieldShopVoucherSeries();

        if ($oArticle->fieldExcludeFromVouchers && empty($oVoucherDef->fieldShopVoucherSeriesSponsorId)) {
            $bMayBeUsed = false;
        }

        // check article restrictions
        if ($bMayBeUsed) {
            $aArticleRestrictons = $oVoucherDef->GetMLTIdList('shop_article_mlt');
            if (count($aArticleRestrictons) > 0 && !in_array($oArticle->id, $aArticleRestrictons)) {
                $bMayBeUsed = false;
            }
        }

        // check manufacturer restrictions
        if ($bMayBeUsed) {
            $aManufacturerRestrictons = $oVoucherDef->GetMLTIdList('shop_manufacturer_mlt');
            if (count($aManufacturerRestrictons) > 0 && !in_array($oArticle->fieldShopManufacturerId, $aManufacturerRestrictons)) {
                $bMayBeUsed = false;
            }
        }

        // check category restrictions
        if ($bMayBeUsed) {
            $aCategoryRestrictons = $oVoucherDef->GetMLTIdList('shop_category_mlt');
            if (count($aCategoryRestrictons) > 0) {
                if (!$oArticle->IsInCategory($aCategoryRestrictons)) {
                    $bMayBeUsed = false;
                }
            }
        }

        // check product group restrictions
        if ($bMayBeUsed) {
            $aArticleGroupRestrictons = $oVoucherDef->GetMLTIdList('shop_article_group_mlt');
            if (count($aArticleGroupRestrictons) > 0) {
                $aGroups = $oArticle->GetMLTIdList('shop_article_group_mlt');
                $aMatches = array_intersect($aGroups, $aArticleGroupRestrictons);
                if (0 == count($aMatches)) {
                    $bMayBeUsed = false;
                }
            }
        }

        return $bMayBeUsed;
    }

    /**
     * generates a vachouer code by $iLength.
     *
     * @param int $iLength
     *
     * @return string
     */
    public static function GenerateVaoucherCode($iLength = 10)
    {
        trigger_error('Methode is deprecated. Use GenerateVoucherCode instead', E_USER_WARNING);
        $aPasswordChars = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
        mt_srand((float) microtime() * 1000000);
        for ($i = 1; $i <= (count($aPasswordChars) * 2); ++$i) {
            $swap = mt_rand(0, count($aPasswordChars) - 1);
            $tmp = $aPasswordChars[$swap];
            $aPasswordChars[$swap] = $aPasswordChars[0];
            $aPasswordChars[0] = $tmp;
        }

        return substr(implode('', $aPasswordChars), 0, $iLength);
    }

    /**
     * generates a voucher code by $iLength.
     *
     * @param int $iLength
     *
     * @return string
     */
    public static function GenerateVoucherCode($iLength = 10)
    {
        $aPasswordChars = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
        mt_srand((float) microtime() * 1000000);
        for ($i = 1; $i <= (count($aPasswordChars) * 2); ++$i) {
            $swap = mt_rand(0, count($aPasswordChars) - 1);
            $tmp = $aPasswordChars[$swap];
            $aPasswordChars[$swap] = $aPasswordChars[0];
            $aPasswordChars[0] = $tmp;
        }

        return substr(implode('', $aPasswordChars), 0, $iLength);
    }

    /**
     * return true if the voucher is sponsored - else return false
     * @return bool
     */
    public function IsSponsored()
    {
        $bIsSponsored = $this->GetFromInternalCache('bVoucherIsSponsored');
        if (is_null($bIsSponsored)) {
            $bIsSponsored = false;
            $oVoucherSeries = $this->GetFieldShopVoucherSeries();
            if ($oVoucherSeries && !empty($oVoucherSeries->fieldShopVoucherSeriesSponsorId)) {
                $bIsSponsored = true;
            }
            $this->SetInternalCache('bVoucherIsSponsored', $bIsSponsored);
        }

        return $bIsSponsored;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return ShopServiceInterface
     */
    private function getShopService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
    }
}
