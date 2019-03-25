<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgImageHotspotItemMarker extends TAdbPkgImageHotspotItemMarker
{
    const VIEW_PATH = 'pkgImageHotspot/views/db/TPkgImageHotspotItemMarker';

    /**
     * render the hotspot image.
     *
     * @param string $sViewName     - name of the view
     * @param string $sViewType     - where to look for the view
     * @param array  $aCallTimeVars - optional parameters to pass to render method
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        $oView->AddVar('oMarker', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbPkgImageHotspotItemMarker::VIEW_PATH, $sViewType);
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
     * fetches the connected record and tries to get a url from that.
     *
     * @return string
     */
    public function GetURLForConnectedRecord()
    {
        $oSpotObject = &$this->GetFieldLinkedRecord();
        $oCmsConfig = &TdbCmsConfig::GetInstance();
        $sLink = $this->fieldUrl;
        if (is_object($oSpotObject)) {
            if ($oSpotObject instanceof TdbCmsTplPage) {
                $sLink = $this->getPageService()->getLinkToPageObjectRelative($oSpotObject);
            } elseif ($oSpotObject instanceof TdbShopArticle) {
                $sLink = $oSpotObject->GetDetailLink();
            } elseif ($oSpotObject instanceof TdbShopCategory) {
                $sLink = $oSpotObject->GetLink();
            } elseif ($oCmsConfig->GetConfigParameter('pkgArticle', false, true)) {
                if ($oSpotObject instanceof TPkgArticle_BreadcrumbItem) {
                    $sLink = $oSpotObject->GetLink();
                } elseif ($oSpotObject instanceof TPkgArticleCategory_BreadcrumbItem) {
                    $sLink = $oSpotObject->GetLink();
                } elseif ($oSpotObject instanceof TdbPkgArticle) {
                    $sLink = $oSpotObject->GetLinkDetailPage();
                } elseif ($oSpotObject instanceof TdbPkgArticleCategory) {
                    $sLink = $oSpotObject->GetURL();
                }
            } else { //nothing that we know matched - try to use  generic method
                $sLink = $oSpotObject->GetURL();
                // still no url? trigger a user error
                if (empty($sLink)) {
                    trigger_error("couldn't get url from connected record object make sure you implement a method for fetching the url - maybe you have to extend ".__CLASS__.' and overwrite the method GetLinkFromConnectedRecord()', E_USER_ERROR);
                }
            }
        }

        return $sLink;
    }
}
