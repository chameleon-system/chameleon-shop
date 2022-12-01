<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepMapper_UserAddressForm extends AbstractTCMSWizardStepMapper
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $oExtranetConfig = TdbDataExtranet::GetInstance();
        if ($oExtranetConfig && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oExtranetConfig->table, $oExtranetConfig->id);
        }
        /** @var $oWizardStep TCMSWizardStep */
        $oWizardStep = $oVisitor->GetSourceObject('oObject');
        if ($oWizardStep && $bCachingEnabled) {
            $oCacheTriggerManager->addTrigger($oWizardStep->table, $oWizardStep->id);
        }
        $aTextData = array();
        $aTextData['sTitle'] = $oWizardStep->fieldName;
        $aTextData['sText'] = $oWizardStep->GetTextField('description');
        $sUrl = $oWizardStep->GetStepURL();
        $oVisitor->SetMappedValue('sSpotName', $oExtranetConfig->fieldExtranetSpotName);
        $oVisitor->SetMappedValue('sWizardSpotName', MTCMSWizardCore::URL_PARAM_MODULE_SPOT);
        $oVisitor->SetMappedValue('aTextData', $aTextData);
        $oVisitor->SetMappedValue('sStepURL', $sUrl);
    }
}
