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

class TShopModuleArticlelistOrderbyList extends TShopModuleArticlelistOrderbyListAutoParent
{
    const VIEW_PATH = 'pkgShop/views/db/TShopModuleArticlelistOrderbyList';

    /**
     * return list for a set of ids.
     *
     * @param array $aIdList
     * @param int   $iLanguageId
     *
     * @return TdbShopModuleArticlelistOrderbyList
     */
    public static function &GetListForIds($aIdList, $iLanguageId = null)
    {
        $aIdList = TTools::MysqlRealEscapeArray($aIdList);
        $sQuery = self::GetDefaultQuery($iLanguageId, "`id` IN ('".implode("', '", $aIdList)."') ");

        return TdbShopModuleArticlelistOrderbyList::GetList($sQuery, $iLanguageId);
    }

    /**
     * used to display an article.
     *
     * @param string $sViewName     - the view to use
     * @param string $sViewType     - where the view is located (Core, Custom-Core, Customer)
     * @param array  $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($iActiveId, $sFormName, $sFieldName, $sViewName = 'changeorder', $sViewType = 'Core', $aCallTimeVars = array(), $bAllowCache = true)
    {
        $oView = new TViewParser();
        $oView->AddVar('oList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $oView->AddVar('iActiveId', $iActiveId);
        $oView->AddVar('sFormName', $sFormName);
        $oView->AddVar('sFieldName', $sFieldName);

        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);
        $sHTML = $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);

        $sActivePageUrl = $this->getActivePageService()->getLinkToActivePageRelative(array(), array('module_fnc', 'listkey', 'listrequest', 'listpage'));
        $stringReplacer = new TPkgCmsStringUtilities_VariableInjection();

        return $stringReplacer->replace($sHTML, ['sActivePageUrl' => $sActivePageUrl]);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        return array();
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
