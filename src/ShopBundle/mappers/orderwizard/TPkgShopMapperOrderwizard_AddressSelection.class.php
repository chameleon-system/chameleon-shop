<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapperOrderwizard_AddressSelection extends AbstractPkgExtranetMapper_Address
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('selectedAddressId', 'string', null, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        parent::Accept($oVisitor, $bCachingEnabled, $oCacheTriggerManager);
        /** @var $oAddressObject TdbDataExtranetUserAddress */
        $oAddressObject = $oVisitor->GetSourceObject('oAddressObject');
        $selectedAddressId = $oVisitor->GetSourceObject('selectedAddressId');

        $oVisitor->SetMappedValue('bActive', $oAddressObject->id == $selectedAddressId);
        $oVisitor->SetMappedValue('sAddressId', $oAddressObject->id);
    }
}
