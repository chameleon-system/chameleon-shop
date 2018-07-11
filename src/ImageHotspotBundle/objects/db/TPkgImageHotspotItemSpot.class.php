<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgImageHotspotItemSpot extends TAdbPkgImageHotspotItemSpot
{
    const VIEW_PATH = 'pkgImageHotspot/views/db/TPkgImageHotspotItemSpot';

    /**
     * return a pointer to the object assigned to the spot.
     *
     * @return TCMSRecord
     */
    public function &GetSpotObject()
    {
        $oObject = &$this->GetFromInternalCache('oObjectAssignedToSpot');
        if (is_null($oObject)) {
            $oObject = $this->GetFieldLinkedRecord();
            $this->SetInternalCache('oObjectAssignedToSpot', $oObject);
        }

        return $oObject;
    }

    /**
     * fetches the connected record and tries to get a url from that.
     *
     * @return string
     */
    public function GetURLForConnectedRecord()
    {
        $oSpotObject = &$this->GetSpotObject();
        if (null === $oSpotObject) {
            return '';
        }
        $oCmsConfig = &TdbCmsConfig::GetInstance();
        $sLink = '';

        if ($oSpotObject instanceof TdbCmsTplPage) {
            $sLink = self::getPageService()->getLinkToPageObjectRelative($oSpotObject);
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

        return $sLink;
    }

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
        $oView->AddVar('oSpot', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbPkgImageHotspotItemSpot::VIEW_PATH, $sViewType);
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
     * Add view based clear cache triggers for the Render method here.
     *
     * @param array  $aClearTriggers - clear trigger array (with current contents)
     * @param string $sViewName      - view being requested
     * @param string $sViewType      - location of the view (Core, Custom-Core, Customer)
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function AddClearCacheTriggers(&$aClearTriggers, $sViewName, $sViewType)
    {
        $aClearTriggers[] = array('table' => $this->table, 'id' => $this->id);
        $aClearTriggers[] = array('table' => 'shop_article', 'id' => ''); // for now we keep it simple and react to any article changes. this should later be changed to react only to relevant items
        $oField = $this->GetTableConf()->GetField('linked_record', $this);
        $aTables = $oField->GetAllowedTables();
        foreach ($aTables as $sTableName) {
            $aClearTriggers[] = array('table' => $sTableName, 'id' => '');
        }
    }
}
