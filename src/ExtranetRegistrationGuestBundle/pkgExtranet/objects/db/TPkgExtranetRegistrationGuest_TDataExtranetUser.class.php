<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgExtranetRegistrationGuest_TDataExtranetUser extends TPkgExtranetRegistrationGuest_TDataExtranetUserAutoParent
{
    public const NAME_SYSTEM_PAGE = 'register-after-shopping';

    /**
     * Checks if active user and given last bought user.
     *
     * @param TdbDataExtranetUser $oLastBoughtUser
     *
     * @return bool
     */
    public function RegistrationGuestIsAllowed($oLastBoughtUser)
    {
        $bRegistrationGuestIsAllowed = false;
        if (!is_null($oLastBoughtUser) && !$this->IsLoggedIn() && !$oLastBoughtUser->LoginExists()) {
            $bRegistrationGuestIsAllowed = true;
        }

        return $bRegistrationGuestIsAllowed;
    }

    /**
     * Returns the link to registration guest page.
     *
     * @return string
     */
    public function GetLinkForRegistrationGuest()
    {
        $oURLData = TCMSSmartURLData::GetActive();
        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getShopForPortalId($oURLData->iPortalId);
        $sRegisterPath = $oShop->GetLinkToSystemPage(self::NAME_SYSTEM_PAGE);

        return $sRegisterPath;
    }
}
