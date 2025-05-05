<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_ShippingGroupList extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oShippingGroupList', 'TdbShopShippingGroupList');
        $oRequirements->NeedsSourceObject('oActiveShippingGroup', 'TdbShopShippingGroup', null, true);
        $oRequirements->NeedsSourceObject('oLocal', 'TCMSLocal', TCMSLocal::GetActive());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oShippingGroupList TdbShopShippingGroupList */
        $oShippingGroupList = $oVisitor->GetSourceObject('oShippingGroupList');
        /** @var $oActiveShippingGroup TdbShopShippingGroup */
        $oActiveShippingGroup = $oVisitor->GetSourceObject('oActiveShippingGroup');
        /** @var $oLocal TCMSLocal */
        $oLocal = $oVisitor->GetSourceObject('oLocal');

        $aFormControlParameter = [
            'module_fnc' => ['[{sModuleSpotName}]' => 'ExecuteStep'],
            'orderstepmethod' => 'ChangeShippingGroup',
        ];

        $aShippingGroups = [];
        $oShippingGroupList->GoToStart();
        while ($oShippingGroup = $oShippingGroupList->Next()) {
            $dCost = $oShippingGroup->GetShippingCostsForBasket();
            $aShippingGroups[] = [
                'bIsActive' => ($oActiveShippingGroup && $oShippingGroup->id == $oActiveShippingGroup->id),
                'id' => $oShippingGroup->id,
                'sTitle' => $oShippingGroup->fieldName,
                'sCost' => (0 != $dCost) ? ($oLocal->FormatNumber($dCost, 2)) : (''),
            ];
        }

        $aData = [
            'sFormControlParameter' => TTools::GetArrayAsFormInput($aFormControlParameter),
            'aShippingGroups' => $aShippingGroups,
        ];

        $oVisitor->SetMappedValueFromArray($aData);
    }
}
