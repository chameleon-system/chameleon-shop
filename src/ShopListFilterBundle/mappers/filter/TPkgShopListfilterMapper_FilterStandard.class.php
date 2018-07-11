<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterMapper_FilterStandard extends AbstractPkgShopListfilterMapper_Filter
{
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
     *
     * @return
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

        /** @var $oFilterItem TPkgShopListfilterItemMultiselect */
        $oFilterItem = $oVisitor->GetSourceObject('oFilterItem');

        /** @var $oActiveFilter TdbPkgShopListfilter */
        $oActiveFilter = $oVisitor->GetSourceObject('oActiveFilter');

        $aFilterData = array();
        $aOptions = $oFilterItem->GetOptions();
        foreach ($aOptions as $sValue => $iCount) {
            $aFilterData[] = array(
                'sValue' => $sValue,
                'bActive' => $oFilterItem->IsSelected(trim($sValue)),
                'iCount' => $iCount,
                'sURL' => $oFilterItem->GetAddFilterURL(trim($sValue)),
                'bAllowMultiSelect' => $oFilterItem->fieldAllowMultiSelection,
            );
        }

        $oVisitor->SetMappedValue('aFilterData', $aFilterData);
    }
}
