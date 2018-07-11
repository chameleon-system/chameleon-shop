<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopCurrency_ShopDiscount extends TPkgShopCurrency_ShopDiscountAutoParent
{
    /**
     * the original price in the base currency.
     *
     * @var float
     */
    public $fieldValueOriginal = null;

    protected function PostLoadHook()
    {
        if (false == TGlobal::IsCMSMode() && is_array($this->sqlData)) {
            if (array_key_exists('value_type', $this->sqlData) && 'absolut' == $this->sqlData['value_type']) {
                if (array_key_exists('value', $this->sqlData)) {
                    $this->sqlData['value__original'] = $this->sqlData['value'];
                    $this->sqlData['value'] = TdbPkgShopCurrency::ConvertToActiveCurrency($this->sqlData['value']);
                }
            }
        }
        parent::PostLoadHook();
    }

    protected function PostWakeupHook()
    {
        if (array_key_exists('value_type', $this->sqlData) && 'absolut' == $this->sqlData['value_type']) {
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
