<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopBasketVoucherCoreList extends TIterator
{

    /**
     * Adds a voucher to the list. note that it wil not check if the voucher is valid (this must be done by the calling method)
     * Returns the voucher key generated when adding the voucher.
     *
     * @param TdbShopVoucher $oVoucher
     *
     * @return void
     */
    public function AddItem($oVoucher)
    {
        $oVoucher->sBasketVoucherKey = $this->GetUniqueItemKey();
        parent::AddItem($oVoucher);
    }

    /**
     * return a unique sBasketVoucherKey for the current list.
     *
     * @return string
     */
    protected function GetUniqueItemKey()
    {
        $iCurrentPointer = $this->getItemPointer();
        $this->GoToStart();
        $aItemKeyList = array();
        while ($oItem = $this->Next()) {
            $aItemKeyList[] = $oItem->sBasketVoucherKey;
        }
        // now generate a key

        do {
            $sKey = md5(uniqid(rand(), true));
        } while (in_array($sKey, $aItemKeyList));

        return $sKey;
    }

    /**
     * return voucher.
     *
     * @return TdbShopVoucher|false
     */
    public function next(): TdbShopVoucher|bool
    {
        return parent::Next();
    }

    /**
     * return the total voucher value for all active vouchers. note that the method will fetch the current basket
     * using the baskets singleton factory.
     *
     * @param bool $bSponsoredVouchers - set to true: only sponsored vouchers, false only none sponsored vouchers
     *
     * @return float
     */
    public function GetVoucherValue($bSponsoredVouchers = false)
    {
        $dValue = 0;
        $oBasket = TShopBasket::GetInstance();

        $iTmpPointer = $this->getItemPointer();
        $this->GoToStart();
        $maxValueShippingCostAdjustment = true === $bSponsoredVouchers ? $oBasket->dCostShipping : 0;
        while ($oVoucher = $this->Next()) {
            if (($bSponsoredVouchers && $oVoucher->IsSponsored()) || (false == $bSponsoredVouchers && false == $oVoucher->IsSponsored())) {
                $dMaxValue = ($oBasket->dCostArticlesTotalAfterDiscounts + $maxValueShippingCostAdjustment) - $dValue;
                $dValue += $oVoucher->GetValue(true, $dMaxValue, $bSponsoredVouchers);
            }
        }

        $this->setItemPointer($iTmpPointer);

        return $dValue;
    }

    /**
     * Removes all vouchers from the basket, that are not valid based on the contents of the basket and the current user
     * Returns the number of vouchers removed.
     *
     * @param string      $sMessangerName
     * @param TShopBasket $oBasket
     *
     * @return void
     */
    public function RemoveInvalidVouchers($sMessangerName, $oBasket = null)
    {
        // since the min value of the basket for which a voucher may work is affected by other vouchers,
        // we need to remove the vouchers first, and then add them one by one back to the basket
        // we suppress the add messages, but keep the negative messages

        if (is_null($oBasket)) {
            $oBasket = TShopBasket::GetInstance();
        }
        $oMessageManager = TCMSMessageManager::GetInstance();

        // get copy of vouchers
        $aVoucherList = $this->_items;
        $this->Destroy();

        $bInvalidVouchersFound = false;
        foreach ($aVoucherList as $iVoucherKey => $oVoucher) {
            // skip all vouchers that are not of the current type that is being calculated.
            if (
                (TShopBasket::VOUCHER_NOT_SPONSORED === $oBasket->getVoucherTypeCurrentlyRecalculating() && $oVoucher->IsSponsored()) ||
                (TShopBasket::VOUCHER_SPONSORED === $oBasket->getVoucherTypeCurrentlyRecalculating() && !$oVoucher->IsSponsored())
            ) {
                $this->AddItem($oVoucher);
                continue;
            }

            $cVoucherAllowUseCode = $oVoucher->AllowUseOfVoucher();
            if (TdbShopVoucher::ALLOW_USE == $cVoucherAllowUseCode) {
                $this->AddItem($oVoucher);
            } else {
                $bInvalidVouchersFound = true;
                $this->RemoveInvalidVoucherHook($oVoucher, $oBasket);
                // send message that the voucher was removed
                $aMessageData = $oVoucher->GetObjectPropertiesAsArray();
                $aMessageData['iRemoveReasoneCode'] = $cVoucherAllowUseCode;
                $oMessageManager->AddMessage($sMessangerName, 'VOUCHER-ERROR-NO-LONGER-VALID-FOR-BASKET', $aMessageData);
            }
        }

        if ($bInvalidVouchersFound) {
            // recalculate the basket
            $oBasket->RecalculateBasket();
        }
    }

    /**
     * called when a voucher is removed that is no longer allowed for the basket. you can use this method to add any
     * handling to the event you wnat.
     *
     * @param TdbShopVoucher $oInvalidVoucher
     * @param TShopBasket    $oBasket
     *
     * @return void
     */
    protected function RemoveInvalidVoucherHook($oInvalidVoucher, $oBasket)
    {
    }

    /**
     * returns true if at least one voucher has free shipping.
     *
     * @return bool
     */
    public function HasFreeShippingVoucher()
    {
        $bHasFreeShipping = false;
        $tmpCurrentPointer = $this->getItemPointer();
        $this->GoToStart();

        while (!$bHasFreeShipping && ($oItem = $this->Next())) {
            $oVoucherSeries = $oItem->GetFieldShopVoucherSeries();
            if ($oVoucherSeries->fieldFreeShipping) {
                $bHasFreeShipping = true;
            }
        }
        $this->setItemPointer($tmpCurrentPointer);

        return $bHasFreeShipping;
    }
}
