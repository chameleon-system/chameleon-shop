<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopBasketDiscountCoreList extends TIterator
{
    /**
     * @return TdbShopDiscount|bool
     */
    public function next(): TdbShopDiscount|bool
    {
        return parent::Next();
    }

    /**
     * return the total discount value for all active discounts. note that the method will fetch the current basket
     * using the baskets singleton factory.
     *
     * @return float
     */
    public function GetDiscountValue()
    {
        $dValue = 0;
        $iTmpPointer = $this->getItemPointer();
        $this->GoToStart();
        while ($oDiscount = $this->Next()) {
            $dValue += $oDiscount->GetValue();
        }

        $this->setItemPointer($iTmpPointer);

        return $dValue;
    }

    /**
     * Removes all discounts from the basket, that are not valid based on the contents of the basket and the current user
     * Returns the number of discounts removed.
     *
     * @param string $sMessangerName - optional message manager to which we output why a discount was removed
     *
     * @return void
     */
    public function RemoveInvalidDiscounts($sMessangerName = null)
    {
        // since the min value of the basket for which a discount may work is affected by other discounts,
        // we need to remove the discounts first, and then add them one by one back to the basket
        // we suppress the add messages, but keep the negative messages

        $oMessageManager = TCMSMessageManager::GetInstance();

        // get copy of discounts
        $aDiscountList = $this->_items;
        $this->Destroy();
        foreach ($aDiscountList as $iDiscountKey => $oDiscount) {
            /** @var $oDiscount TdbShopDiscount */
            $cDiscountAllowUseCode = $oDiscount->AllowUseOfDiscount();
            if (TdbShopDiscount::ALLOW_USE == $cDiscountAllowUseCode) {
                $this->AddItem($oDiscount);
            } else {
                if (!is_null($sMessangerName)) {
                    // send message that the discount was removed
                    $aMessageData = $oDiscount->GetObjectPropertiesAsArray();
                    $aMessageData['iRemoveReasoneCode'] = $cDiscountAllowUseCode;
                    $oMessageManager->AddMessage($sMessangerName, 'DISCOUNT-ERROR-NO-LONGER-VALID-FOR-BASKET', $aMessageData);
                }
            }
        }
    }
}
