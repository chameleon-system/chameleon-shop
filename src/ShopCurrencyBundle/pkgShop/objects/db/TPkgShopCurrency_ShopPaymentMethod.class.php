<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopCurrency_ShopPaymentMethod extends TPkgShopCurrency_ShopPaymentMethodAutoParent
{
    public $fieldValueOriginal = null;

    protected function PostLoadHook()
    {
        if (false == TGlobal::IsCMSMode() && is_array($this->sqlData)) {
            $this->dPrice = null;
            // first we need to restore the original price data
            if (array_key_exists('value_type', $this->sqlData) && 'absolut' == $this->sqlData['value_type']) {
                $sFieldName = 'value';
                if (array_key_exists($sFieldName, $this->sqlData)) {
                    $this->fieldValueOriginal = $this->sqlData[$sFieldName];
                    $this->sqlData[$sFieldName] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData[$sFieldName]);
                }
            }
        }
        parent::PostLoadHook();
    }

    protected function PostWakeupHook()
    {
        if (array_key_exists('value_type', $this->sqlData) && 'absolut' == $this->sqlData['value_type']) {
            $this->dPrice = null;
            $sFieldName = 'value';
            if (!is_null($this->fieldValueOriginal)) {
                $this->sqlData[$sFieldName] = $this->fieldValueOriginal;
            }

            if (array_key_exists($sFieldName, $this->sqlData)) {
                $this->sqlData[$sFieldName] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData[$sFieldName]);
                $this->fieldValue = $this->sqlData[$sFieldName];
                $oActiveCurrency = TdbPkgShopCurrency::GetActiveInstance();
                if ($oActiveCurrency) {
                    $this->fieldValueFormated = $oActiveCurrency->GetFormattedCurrency($this->fieldValue);
                }
            }
        }
    }
}
