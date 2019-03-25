<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TShopWishlistArticle extends TShopWishlistArticleAutoParent
{
    /**
     * return the link that can be used to add the article to the users wishlist.
     *
     * @param bool $bIncludePortalLink
     *
     * @return string
     */
    public function GetToWishlistLink($bIncludePortalLink = false, $bRedirectToLoginPage = true)
    {
        $oShopConfig = TdbShop::GetInstance();

        $aParameters = array('module_fnc['.$oShopConfig->GetBasketModuleSpotName().']' => 'AddToWishlist', MTShopBasketCore::URL_ITEM_ID => $this->id, MTShopBasketCore::URL_ITEM_AMOUNT => 1, MTShopBasketCore::URL_MESSAGE_CONSUMER => $this->GetMessageConsumerName());

        $aIncludeParams = TdbShop::GetURLPageStateParameters();
        $oGlobal = TGlobal::instance();
        foreach ($aIncludeParams as $sKeyName) {
            if ($oGlobal->UserDataExists($sKeyName) && !array_key_exists($sKeyName, $aParameters)) {
                $aParameters[$sKeyName] = $oGlobal->GetUserData($sKeyName);
            }
        }

        $oActivePage = $this->getActivePageService()->getActivePage();
        $sLink = $oActivePage->GetRealURLPlain($aParameters, $bIncludePortalLink);
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($bRedirectToLoginPage && !$oUser->IsLoggedIn()) {
            $sSuccessLink = $sLink;
            $oExtranet = TdbDataExtranet::GetInstance();
            $sLoginPageURL = $oExtranet->GetLinkLoginPage(true);
            $sLink = $sLoginPageURL.'?sSuccessURL='.urlencode($sSuccessLink);
        }

        return $sLink;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
