<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopCurrency_ShopVoucher extends TPkgShopCurrency_ShopVoucherAutoParent
{
    /**
     * special hook for the GetValue Method.
     *
     * @return float
     */
    protected function GetValuePreviouslyUsed_GetValueHook()
    {
        return TdbPkgShopCurrency::ConvertToActiveCurrency(parent::GetValuePreviouslyUsed_GetValueHook());
    }

    /**
     * method can be used to process the use data before the commit is called
     * we use it here to convert the value of the voucher used back to the base currency (since that is what we want to store.
     *
     * @param array $aData
     */
    protected function CommitVoucherUseForCurrentUserPreSaveHook(&$aData)
    {
        // value used is converted to the base currency - euro
        $oCurrency = TdbPkgShopCurrency::GetBaseCurrency();
        $oActive = TdbPkgShopCurrency::GetActiveInstance();
        $aData['value_used_in_order_currency'] = $aData['value_used'];
        if (false !== $oCurrency) {
            $aData['pkg_shop_currency_id'] = $oCurrency->id;
        }
        if ($oCurrency && $oActive && $oActive->id != $oCurrency->id) {
            $aData['pkg_shop_currency_id'] = $oActive->id;
            $aData['value_used'] = $oCurrency->Convert($aData['value_used'], $oActive);
        }
    }

    /**
     * return the original value of the connected series IN BASE CURRENCY (not current currency).
     *
     * @return float|null
     */
    protected function GetVoucherSeriesOriginalValue()
    {
        $oVoucherSeries = &$this->GetFieldShopVoucherSeries();
        $dValue = $oVoucherSeries->fieldValue;
        if ('absolut' == $oVoucherSeries->fieldValueType) {
            $oCurrency = TdbPkgShopCurrency::GetBaseCurrency();
            $oActive = TdbPkgShopCurrency::GetActiveInstance();
            if ($oCurrency && $oActive && $oActive->id != $oCurrency->id) {
                $dValue = $oCurrency->Convert($dValue, $oActive);
            }
        }

        return $dValue;
    }
}
