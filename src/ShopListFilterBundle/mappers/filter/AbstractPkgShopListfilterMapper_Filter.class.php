<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractPkgShopListfilterMapper_Filter extends AbstractViewMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('oFilterItem', 'TdbPkgShopListfilterItem');
        $oRequirements->NeedsSourceObject('oActiveFilter', 'TdbPkgShopListfilter', TdbPkgShopListfilter::GetActiveInstance());
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        /** @var $oFilterItem TdbPkgShopListfilterItem */
        $oFilterItem = $oVisitor->GetSourceObject('oFilterItem');
        $aFilterData = array(
            'sTitle' => $oFilterItem->fieldName,
            'sInputURLName' => $oFilterItem->GetURLInputName(),
            'sResetURL' => $oFilterItem->GetAddFilterURL(''),
            'bActive' => $oFilterItem->IsActive(),
        );
        $oVisitor->SetMappedValueFromArray($aFilterData);
    }
}
