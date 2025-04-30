<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopDhlPackstation_ShopOrder extends TPkgShopDhlPackstation_ShopOrderAutoParent
{
    /**
     * method can be used to modify the data saved to order before the save is executed.
     *
     * @param TShopBasket $oBasket
     * @param array $aOrderData
     *
     * @return void
     */
    protected function LoadFromBasketPostProcessData($oBasket, &$aOrderData)
    {
        parent::LoadFromBasketPostProcessData($oBasket, $aOrderData);
        $oUser = TdbDataExtranetUser::GetInstance();
        $oShipping = $oUser->GetShippingAddress();
        $aOrderData['adr_shipping_is_dhl_packstation'] = $oShipping->fieldIsDhlPackstation;
    }
}
