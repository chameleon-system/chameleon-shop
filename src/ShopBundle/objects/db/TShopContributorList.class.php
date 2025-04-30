<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopContributorList extends TAdbShopContributorList
{
    public const VIEW_PATH = 'pkgShop/views/db/TShopContributorList';

    /**
     * used to display an article.
     *
     * @param string $sViewName - the view to use
     * @param string $sViewType - where the view is located (Core, Custom-Core, Customer)
     * @param array $aCallTimeVars - place any custom vars that you want to pass through the call here
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = [])
    {
        $oView = new TViewParser();

        // add view variables
        $oView->AddVar('oContributorList', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, self::VIEW_PATH, $sViewType);
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
        $aViewVariables = [];

        return $aViewVariables;
    }
}
