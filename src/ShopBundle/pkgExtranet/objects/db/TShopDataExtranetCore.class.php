<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopDataExtranetCore extends TShopDataExtranetCoreAutoParent
{
    /**
     * returns link to current page with logout method as parameter for given spotname of extranet module if not on "thank you" page of order process
     * otherwise it would return link to home page with logout method as parameter for given spotname.
     *
     * @param string $sSpotName
     *
     * @return string
     */
    public function GetLinkLogout($sSpotName)
    {
        $oGlobal = TGlobal::instance();
        // if we are on the last page of checkout process we can't use the active page url for logout
        // so we redirect to the home url with logout method as parameter
        if ($oGlobal->UserDataExists(MTShopOrderWizardCore::URL_PARAM_STEP_SYSTEM_NAME) && 'thankyou' == $oGlobal->GetUserData(MTShopOrderWizardCore::URL_PARAM_STEP_SYSTEM_NAME)) {
            return self::getPageService()->getLinkToPortalHomePageAbsolute().'?'.TTools::GetArrayAsURL(array('module_fnc['.$sSpotName.']' => 'Logout'));
        }

        return parent::GetLinkLogout($sSpotName);
    }
}
