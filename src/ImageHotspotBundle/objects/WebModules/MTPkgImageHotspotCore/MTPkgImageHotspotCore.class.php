<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Es wird ein Modul geschaffen, bei dem eine beliebige Anzahl Bilder definiert werden kann.
 * Bei jedem Bild können Hotspots im Bild positioniert und mit einem Artikel verknüpft werden.
 * Auf der Webseite wird das erste Bild angezeigt, jeweils mit einem Link zu den anderen Bildern
 * (eine Art Blätterfunktion). Die Hotspots sind mit kleinen Kreuzen auf dem Bild markiert. Fährt
 * ein Kunde mit der Maus über einen Hotspot, erscheinen der Artikelname und der Preis. Klickt der
 * Kunde auf den Hotspot, dann gelangt er zur Detailseite des Artikels.
/**/
class MTPkgImageHotspotCore extends TUserCustomModelBase
{
    /**
     * @var string|null
     */
    protected $sActiveItemId = null;
    /**
     * @var TdbPkgImageHotspotItem|null
     */
    private $oActiveItem = null;

    /**
     * @var TdbPkgImageHotspotItem|null
     */
    private $oNextItem = null;

    /**
     * @var TdbPkgImageHotspot|null
     */
    private $oHotspotConfig = null;

    /**
     * any request data send to the module.
     *
     * @var array
     */
    protected $aUserRequestData = array();

    /**
     * @var bool
     */
    protected $bAllowHTMLDivWrapping = true;

    /**
     * {@inheritdoc}
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'AjaxRenderHotspotImage';
    }

    /**
     * returns the hotspot in $this->sactiveItemid rendered as html.
     *
     * @param string $sViewName
     * @param string $sType
     *
     * @return \stdClass
     */
    protected function AjaxRenderHotspotImage($sViewName = 'standard', $sType = 'Core')
    {
        $oResult = new stdClass();
        $oResult->sItemPage = '';
        $oActiveItem = $this->GetActiveItem();
        if (!is_null($oActiveItem)) {
            $oResult->sItemId = $oActiveItem->id;
            $sMapperConfig = (isset($this->aUserRequestData['sMapperConfig'])) ? ($this->aUserRequestData['sMapperConfig']) : (false);
            if (false !== $sMapperConfig) {
                $oResult->sItemPage = $this->RenderUsingMapperConfig($sMapperConfig);
            } else {
                if (array_key_exists('sViewName', $this->aUserRequestData)) {
                    $sViewName = $this->aUserRequestData['sViewName'];
                }
                if (array_key_exists('sType', $this->aUserRequestData)) {
                    $sType = $this->aUserRequestData['sType'];
                }
                $oResult->sItemPage = $oActiveItem->Render($sViewName, $sType);
                $oResult->iListKey = md5($oActiveItem->fieldPkgImageHotspotId);
            }
        }

        return $oResult;
    }

    /**
     * @param string $sMapperConfig
     *
     * @return string
     */
    protected function RenderUsingMapperConfig($sMapperConfig)
    {
        return TTools::CallModule('MTPkgImageHotspot', $sMapperConfig, array('instanceID' => $this->instanceID), $this->sModuleSpotName);
    }

    /**
     * {@inheritdoc}
     */
    public function Init()
    {
        parent::Init();
        if ($this->global->UserDataExists(TdbPkgImageHotspotItem::GetURLParameterBaseForActiveSpot())) {
            $this->aUserRequestData = $this->global->GetUserData(TdbPkgImageHotspotItem::GetURLParameterBaseForActiveSpot());
            if (!is_array($this->aUserRequestData)) {
                $this->aUserRequestData = array();
            }
            if (array_key_exists('id', $this->aUserRequestData)) {
                $this->sActiveItemId = $this->aUserRequestData['id'];
            }
        }
        if (is_null($this->sActiveItemId) || empty($this->sActiveItemId)) {
            // get the first entry from the list
            $oItemList = &$this->GetHotspotConfig()->GetFieldPkgImageHotspotItemList();
            $oItemList->bAllowItemCache = true;
            $this->oActiveItem = $oItemList->Next();
            if (false == $this->oActiveItem) {
                $this->oActiveItem = null;
            }
            if (!is_null($this->oActiveItem)) {
                $this->sActiveItemId = $this->oActiveItem->id;
            }
            $this->oNextItem = $oItemList->Next();
            if (false == $this->oNextItem) {
                $this->oNextItem = null;
            }
        }
    }

    /**
     * return the hotspot following the current active spot. if the active spot is the
     * last spot, then we will return the first step.
     *
     * @return TdbPkgImageHotspotItem|null
     */
    protected function &GetNextItem()
    {
        if (is_null($this->oNextItem)) {
            $oActiveItem = &$this->GetActiveItem();
            $oItemList = &$this->GetHotspotConfig()->GetFieldPkgImageHotspotItemList();
            if ($oItemList->Length() < 2) {
                $retValue = null; // write to variable to satisfy strict mode
                return $retValue;
            }
            $oItemList->GoToStart();
            while (is_null($this->oNextItem) && ($oItem = $oItemList->Next())) {
                if ($oItem->IsSameAs($oActiveItem)) {
                    $this->oNextItem = $oItemList->Next();
                    if (false == $this->oNextItem) {
                        $oItemList->GoToStart();
                        $this->oNextItem = $oItemList->Next();
                    }
                }
            }
            if (!is_null($this->oNextItem) && $this->oNextItem->IsSameAs($oActiveItem)) {
                $this->oNextItem = null;
            }
        }

        return $this->oNextItem;
    }

    /**
     * return config for the instance.
     *
     * @return TdbPkgImageHotspot
     */
    protected function &GetHotspotConfig()
    {
        if (is_null($this->oHotspotConfig)) {
            $this->oHotspotConfig = TdbPkgImageHotspot::GetNewInstance();
            if (!$this->oHotspotConfig->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
                $this->oHotspotConfig = null;
            }
        }

        return $this->oHotspotConfig;
    }

    /**
     * return the active item.
     *
     * @return TdbPkgImageHotspotItem
     */
    protected function &GetActiveItem()
    {
        if (is_null($this->oActiveItem) && !is_null($this->sActiveItemId)) {
            $this->oActiveItem = TdbPkgImageHotspotItem::GetNewInstance();
            $this->oActiveItem->Load($this->sActiveItemId);
        }

        return $this->oActiveItem;
    }

    /**
     * {@inheritdoc}
     */
    public function &Execute()
    {
        parent::Execute();
        $this->data['oHotspotConfig'] = $this->GetHotspotConfig();
        $this->data['oActiveItem'] = $this->GetActiveItem();
        $this->data['oNextItem'] = $this->GetNextItem();
        $this->data['sLinkNextItem'] = '';
        $this->data['oAllItems'] = &$this->GetHotspotConfig()->GetFieldPkgImageHotspotItemList();

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();
        $parameters['sActiveItemId'] = $this->sActiveItemId;

        return $parameters;
    }

    /**
     * if the content that is to be cached comes from the database (as ist most often the case)
     * then this function should return an array of assoc arrays that point to the
     * tables and records that are associated with the content. one table entry has
     * two fields:
     *   - table - the name of the table
     *   - id    - the record in question. if this is empty, then any record change in that
     *             table will result in a cache clear.
     *
     * @return array
     */
    public function _GetCacheTableInfos()
    {
        $aTriggers = parent::_GetCacheTableInfos();
        if (!is_array($aTriggers)) {
            $aTriggers = array();
        }
        $oHotspotConfig = &$this->GetHotspotConfig();
        if (!is_null($oHotspotConfig)) {
            $aTriggers[] = array('table' => 'pkg_image_hotspot', 'id' => $oHotspotConfig->id);
        }
        $aTriggers[] = array('table' => 'pkg_image_hotspot_item', 'id' => $this->sActiveItemId);
        $aTriggers[] = array('table' => 'pkg_image_hotspot_item_spot', 'id' => '');

        $oActiveItem = &$this->GetActiveItem();
        if (!empty($oActiveItem)) {
            $aTriggers[] = array('table' => 'cms_media', 'id' => $oActiveItem->fieldCmsMediaId);
        }

        return $aTriggers;
    }

    /**
     * {@inheritdoc}
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        if (!is_array($aIncludes)) {
            $aIncludes = array();
        }
        $aIncludes[] = '<link href="'.TGlobal::GetStaticURL(URL_USER_CMS_PUBLIC.'/blackbox/pkgImageHotspot/MTPkgImageHotspot.css').'"  rel="stylesheet" type="text/css" />';
        $aIncludes[] = '<script src="'.TGlobal::GetStaticURL('/chameleon/blackbox/javascript/jquery/chameleon/jquery.chameleonImageSlider.js').'" type="text/javascript"></script>';
        $aIncludes = array_merge($aIncludes, $this->getResourcesForSnippetPackage('pkgImageHotspot'));

        return $aIncludes;
    }
}
