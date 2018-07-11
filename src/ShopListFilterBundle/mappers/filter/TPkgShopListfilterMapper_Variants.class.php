<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopListfilterMapper_Variants extends TPkgShopListfilterMapper_FilterStandard
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
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('sShopVariantTypeIds', 'string');
        $oRequirements->NeedsSourceObject('aFilterData', 'array');
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
     *
     * @return
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);

        $aFilterData = $oVisitor->GetSourceObject('aFilterData');
        $sShopVariantTypeIds = $oVisitor->GetSourceObject('sShopVariantTypeIds');

        $aRestriction = array();
        reset($aFilterData);
        foreach ($aFilterData as $aFilter) {
            $aRestriction[] = MySqlLegacySupport::getInstance()->real_escape_string($aFilter['sValue']);
        }

        $query = "SELECT *
                    FROM `shop_variant_type_value`
                   WHERE `shop_variant_type_id` IN ({$sShopVariantTypeIds})
                     AND `name` IN ('".implode("','", $aRestriction)."')
                 ";
        $oValueList = TdbShopVariantTypeValueList::GetList($query);
        $aMapping = array();
        while ($oValue = $oValueList->Next()) {
            $aMapping[$oValue->fieldName] = $oValue;
        }
        reset($aFilterData);
        foreach ($aFilterData as $sIndex => $aFilter) {
            if (isset($aMapping[$aFilter['sValue']])) {
                $aFilterData[$sIndex]['color_code'] = $aMapping[$aFilter['sValue']]->fieldColorCode;
                $aFilterData[$sIndex]['cms_media_id'] = $aMapping[$aFilter['sValue']]->fieldCmsMediaId;
                $aFilterData[$sIndex]['named_grouped'] = $aMapping[$aFilter['sValue']]->fieldNameGrouped;
            }
        }
        reset($aFilterData);

        $oVisitor->SetMappedValue('aFilterData', $aFilterData);
    }
}
