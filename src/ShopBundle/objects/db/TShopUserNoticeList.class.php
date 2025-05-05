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

class TShopUserNoticeList extends TAdbShopUserNoticeList
{
    /**
     * return link that can be used to remove the item from the notice list.
     *
     * @return string
     */
    public function GetRemoveFromNoticeListLink()
    {
        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $aParams = ['module_fnc['.$oShop->GetBasketModuleSpotName().']' => 'RemoveFromNoticeList', MTShopBasketCore::URL_ITEM_ID => $this->fieldShopArticleId];

        return $this->getActivePageService()->getLinkToActivePageRelative($aParams);
    }

    /**
     * @return string
     */
    public function GetRemoveFromNoticeListLinkAjax()
    {
        $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $aParams = ['module_fnc['.$oShop->GetBasketModuleSpotName().']' => 'ExecuteAjaxCall', '_fnc' => 'RemoveFromNoticeListAjax', MTShopBasketCore::URL_ITEM_ID => $this->fieldShopArticleId];

        return $this->getActivePageService()->getLinkToActivePageRelative($aParams);
    }

    /**
     * @param string $sViewName
     * @param string $sSubType
     * @param string $sType
     * @param array $aCallTimeVars
     *
     * @return string
     */
    public function Render($sViewName, $sSubType = 'pkgShop/views/db/TShopUserNoticeList', $sType = 'Customer', $aCallTimeVars = [])
    {
        $oView = new TViewParser();
        $oView->AddVar('oNoticeItem', $this);
        $oArticle = $this->GetFieldShopArticle();
        $oView->AddVar('oArticle', $oArticle);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);

        return $oView->RenderObjectPackageView($sViewName, $sSubType, 'Customer');
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
