<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;

class TPkgShopCurrency_ShopShippingGroup extends TPkgShopCurrency_ShopShippingGroupAutoParent
{
    /**
     * returns currency symbol (by default €)
     * returns currency symbol for active currency.
     *
     * @return string
     */
    protected function GetCurrencySymbol()
    {
        parent::GetCurrencySymbol();

        return ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject()?->GetCurrencyDisplaySymbol();
    }
}
