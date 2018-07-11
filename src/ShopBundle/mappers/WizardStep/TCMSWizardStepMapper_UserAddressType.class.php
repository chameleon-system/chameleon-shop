<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepMapper_UserAddressType extends AbstractTCMSWizardStepMapper
{
    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('bIsBillingAddress', 'boolean');
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $bIsBillingAddress = $oVisitor->GetSourceObject('bIsBillingAddress');
        $oVisitor->SetMappedValue('bIsBillingAddress', $bIsBillingAddress);
    }
}
