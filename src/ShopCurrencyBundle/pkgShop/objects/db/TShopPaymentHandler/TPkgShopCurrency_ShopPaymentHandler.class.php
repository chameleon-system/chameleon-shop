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
 * if we have an active currency, set it as default.
/**/
class TPkgShopCurrency_ShopPaymentHandler extends TPkgShopCurrency_ShopPaymentHandlerAutoParent
{
    /**
     * return the currency identifier for the currency we pay in.
     *
     * @param TdbPkgShopCurrency|null $oPkgShopCurrency
     *
     * @return string
     */
    protected function GetCurrencyIdentifier($oPkgShopCurrency = null)
    {
        if (is_null($oPkgShopCurrency)) {
            $oPkgShopCurrency = TdbPkgShopCurrency::GetActiveInstance();
        }

        return parent::GetCurrencyIdentifier($oPkgShopCurrency);
    }
}
