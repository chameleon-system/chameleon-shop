<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopDhlPackstation_TShopPaymentMethod extends TPkgShopDhlPackstation_TShopPaymentMethodAutoParent
{
    // **************************************************************************

    public function IsValidForCurrentUser()
    {
        $bValidForUser = parent::IsValidForCurrentUser();
        if (false === $bValidForUser) {
            return $bValidForUser;
        }

        if (false === $this->fieldPkgDhlPackstationAllowForPackstation) {
            $oUser = TdbDataExtranetUser::GetInstance();
            $oShipping = $oUser->GetShippingAddress();
            if ($oShipping && $oShipping->fieldIsDhlPackstation) {
                $bValidForUser = false;
            }
        }

        return $bValidForUser;
    }
}
