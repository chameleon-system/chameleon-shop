<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgImageHotspotMapper extends AbstractViewMapper
{
    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oPkgImageHotspot', 'TdbPkgImageHotspot');
        $oRequirements->NeedsSourceObject('sActiveItemId', 'string');
        $oRequirements->NeedsSourceObject('sMapperConfig', 'string'); // the module view to use when rendering the content of the presenter
        $oRequirements->NeedsSourceObject('aObjectRenderConfig', 'array'); // array of the form array('classname'=>array('mapper'=>('mapper1','mapper2'),'snippet'=>'view.html.twig'),...)
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapeprVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oPkgImageHotspot TdbPkgImageHotspot */
        $oPkgImageHotspot = $oVisitor->GetSourceObject('oPkgImageHotspot');
        if ($oPkgImageHotspot && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oPkgImageHotspot->table, $oPkgImageHotspot->id);
        }

        $oVisitor->SetMappedValue('iAutoSlideTime', $oPkgImageHotspot->fieldAutoSlideTime);

        $sActiveItemId = $oVisitor->GetSourceObject('sActiveItemId');
        $oVisitor->SetMappedValue('sActiveItemId', $sActiveItemId);
        $sMapperConfig = $oVisitor->GetSourceObject('sMapperConfig');

        $aRenderObjectConfig = $oVisitor->GetSourceObject('aObjectRenderConfig');

        $oActiveItem = TdbPkgImageHotspotItem::GetNewInstance($sActiveItemId);
        $oActiveItemImage = $oActiveItem->GetImage(0, 'cms_media_id', true);
        if ($oActiveItem && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oActiveItem->table, $oActiveItem->id);
            $oCacheTriggerManager->addTrigger('cms_media_id', $oActiveItemImage->id);
        }

        $oVisitor->SetMappedValue('sHeadline', $oPkgImageHotspot->fieldName);

        $oVisitor->SetMappedValue('sBackgroundImageId', $oActiveItemImage->id);
        $oVisitor->SetMappedValue('backgroundImageCropId', $oActiveItem->fieldCmsMediaIdImageCropId);
        $oVisitor->SetMappedValue('sBackgroundImageAlt', $oActiveItem->fieldName);

        $oNextItem = $oActiveItem->GetNextItem();
        $oPreviousItem = $oActiveItem->GetPreviousItem();

        if ($oNextItem) {
            $oVisitor->SetMappedValue('aNext', $this->getNaviFromItem($oNextItem, $sMapperConfig));
            if ($oNextItem && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oNextItem->table, $oNextItem->id);
            }
        }

        if ($oPreviousItem) {
            $oVisitor->SetMappedValue('aPrevious', $this->getNaviFromItem($oPreviousItem, $sMapperConfig));
            if ($oPreviousItem && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oPreviousItem->table, $oPreviousItem->id);
            }
        }
        $oItemList = $oPkgImageHotspot->GetFieldPkgImageHotspotItemList();
        $aNavi = array();
        while ($oItem = $oItemList->Next()) {
            if (true === $oItem->fieldActive) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oItem->table, $oItem->id);
                }
                $aNavi[] = $this->getNaviFromItem($oItem, $sMapperConfig, $oItem->id == $oActiveItem->id);
            }
        }
        $oVisitor->SetMappedValue('aNavigation', $aNavi);

        // get overlay images
        $oLayoverList = $oActiveItem->GetFieldPkgImageHotspotItemMarkerList();
        $aImageLayoverList = array();
        /**
         * @var $oLayover TdbPkgImageHotspotItemMarker
         */
        while ($oLayover = $oLayoverList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oLayover->table, $oLayover->id);
            }
            $oImage = $oLayover->GetImage(0, 'cms_media_id');
            if (null !== $oImage) {
                $sImageURL = $oImage->GetRelativeURL();

                if ($oImage && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger('cms_media_id', $oImage->id);
                }

                $oImageHover = $oLayover->GetImage(0, 'cms_media_hover_id');
                if ($oImageHover && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger('cms_media_id', $oImageHover->id);
                }

                $sHoverImageURL = '';
                if ($oImageHover) {
                    $sHoverImageURL = $oImageHover->GetRelativeURL();
                }

                $sTargetURL = $oLayover->GetURLForConnectedRecord();
                if (empty($sTargetURL)) {
                    $sTargetURL = $oLayover->fieldUrl;
                }

                if (!empty($sTargetURL)) {
                    $sLayoverContent = '';
                    if ($oLayover->fieldShowObjectLayover) {
                        $oTargetObject = $oLayover->GetFieldLinkedRecord();
                        $sLayoverContent = $this->renderObject($oTargetObject, $aRenderObjectConfig);
                    }

                    $aImageLayoverList[] = array(
                        'id' => $oLayover->id,
                        'iLeft' => $oLayover->fieldLeft,
                        'iTop' => $oLayover->fieldTop,
                        'sLink' => $oLayover->GetURLForConnectedRecord(),
                        'sContent' => $sLayoverContent,
                        'bShowLayover' => true,
                        'sTitle' => $oLayover->fieldName,
                        'sImageURL' => $sImageURL,
                        'sHoverImageURL' => $sHoverImageURL,
                    );
                }
            }
        }
        $oVisitor->SetMappedValue('aImageLayoverList', $aImageLayoverList);

        // now get spots
        $aMarkerList = array();
        $oMarkerList = $oActiveItem->GetFieldPkgImageHotspotItemSpotList();
        while ($oMarker = $oMarkerList->Next()) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oMarker->table, $oMarker->id);
            }

            $sTargetURL = $oMarker->GetURLForConnectedRecord();
            if (empty($sTargetURL)) {
                $sTargetURL = $oMarker->fieldExternalUrl;
            }

            if (!empty($sTargetURL)) {
                $oTargetObject = $oMarker->GetSpotObject();

                if ($oTargetObject && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oTargetObject->table, $oTargetObject->id);
                }

                $oLinkedRecord = $oMarker->GetFieldLinkedRecord();
                if ($oLinkedRecord && $bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oLinkedRecord->table, $oLinkedRecord->id);
                }
                $sMarkerDirection = $oMarker->fieldHotspotType;
                if ('Hotspot-Links' == $sMarkerDirection) {
                    $sMarkerDirection = 'left';
                } else {
                    $sMarkerDirection = 'right';
                }

                if ($oLinkedRecord) {
                    $sMarkerName = $oLinkedRecord->GetName();
                } else {
                    $sMarkerName = str_replace(array('http://', 'https://'), '', $sTargetURL);
                }

                $sLayoverContent = '';
                if ($oTargetObject && $oMarker->fieldShowSpot) {
                    $sLayoverContent = $this->renderObject($oTargetObject, $aRenderObjectConfig);
                }

                $aMarkerList[] = array(
                    'id' => $oMarker->id,
                    'iLeft' => $oMarker->fieldLeft,
                    'iTop' => $oMarker->fieldTop,
                    'sLink' => $sTargetURL,
                    'sContent' => $sLayoverContent,
                    'sDirection' => $sMarkerDirection,
                    'sLinkText' => $sMarkerName,
                    'imageMap' => $oMarker->fieldPolygonArea,
                );
            }
        }
        $oVisitor->SetMappedValue('aMarkerList', $aMarkerList);
    }

    /**
     * renders hotspot marker layover container content.
     *
     * @param $oTargetObject
     * @param array $aRenderObjectConfig
     *
     * @return string
     */
    protected function renderObject($oTargetObject, $aRenderObjectConfig)
    {
        $sClassName = get_class($oTargetObject);
        if (false === isset($aRenderObjectConfig[$sClassName])) {
            return '';
        }

        $aMapper = $aRenderObjectConfig[$sClassName]['mapper'];
        if (false === is_array($aMapper)) {
            $aMapper = array($aMapper);
        }
        $sSnippet = $aRenderObjectConfig[$sClassName]['snippet'];
        $oViewRenderer = new ViewRenderer();
        foreach ($aMapper as $sMapper) {
            if (true === empty($sMapper)) {
                continue;
            }
            $oViewRenderer->addMapperFromIdentifier($sMapper);
        }
        $oViewRenderer->AddSourceObject('oObject', $oTargetObject);

        return $oViewRenderer->Render($sSnippet);
    }

    /**
     * @param TdbPkgImageHotspotItem $oItem
     * @param string                 $sMapperConfig
     * @param bool                   $bIsActive
     *
     * @return array
     */
    protected function getNaviFromItem(TdbPkgImageHotspotItem $oItem, $sMapperConfig, $bIsActive = false)
    {
        return array(
            'sTitle' => $oItem->fieldName,
            'sLink' => $oItem->GetLink(),
            'sLinkJS' => $oItem->GetAjaxLink(null, null, array('sMapperConfig' => $sMapperConfig)),
            'bIsActive' => $bIsActive,
            'sItemId' => $oItem->id,
        );
    }
}
