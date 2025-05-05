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

class TPkgShopWishlistArticle extends TAdbPkgShopWishlistArticle
{
    /**
     * return comment text as html.
     *
     * @return string
     */
    public function GetCommentAsHTML()
    {
        $sText = trim($this->fieldComment);
        $sText = TGlobal::OutHTML($sText);
        $sText = nl2br($sText);

        return $sText;
    }

    /**
     * get link to remove item from wishlist.
     *
     * @param bool $bIncludePortalLink
     *
     * @return string
     */
    public function GetRemoveFromWishlistLink($bIncludePortalLink = false)
    {
        $oShopConfig = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $aParameters = ['module_fnc['.$oShopConfig->GetBasketModuleSpotName().']' => 'RemoveFromWishlist', MTShopBasketCore::URL_ITEM_ID => $this->id, MTShopBasketCore::URL_MESSAGE_CONSUMER => MTShopBasketCore::MSG_CONSUMER_NAME];

        return $this->getActivePageService()->getLinkToActivePageRelative($aParameters);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
