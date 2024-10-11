<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepMapper_UserProfile extends AbstractTCMSWizardStepMapper
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        $aUserData = $oVisitor->GetSourceObject('aUserInput');
        $sSpotName = $oVisitor->GetSourceObject('sSpotName');
        $aFieldMessages = $oVisitor->GetSourceObject('aFieldMessages');
        $sWizardSpotName = $oVisitor->GetSourceObject('sWizardModuleModuleSpot');
        /** @var $oWizardStep TCMSWizardStep */
        $oWizardStep = $oVisitor->GetSourceObject('oObject');

        $aFieldBirthdate = array();
        $aFieldBirthdate['sError'] = $this->GetMessageForField('birthdate', $aFieldMessages);
        $aFieldBirthdate['sValue'] = $this->GetValueForField('birthdate', $aUserData);
        $oVisitor->SetMappedValue('aFieldBirthdate', $aFieldBirthdate);

        $aTextData = array();
        $aTextData['sTitle'] = $oWizardStep->fieldName;
        $aTextData['sText'] = $oWizardStep->GetTextField('description');

        $sUrl = $oWizardStep->GetStepURL();

        $oVisitor->SetMappedValue('sSpotName', $sSpotName);
        $oVisitor->SetMappedValue('sWizardSpotName', $sWizardSpotName);
        $oVisitor->SetMappedValue('aTextData', $aTextData);
        $oVisitor->SetMappedValue('sWizardSpotName', $sWizardSpotName);
        $oVisitor->SetMappedValue('sStepURL', $sUrl);
    }

    /**
     * Get field message for given field in given array.
     *
     * @param string $sFieldName
     * @param array  $aFieldMessages
     *
     * @return string
     */
    protected function GetMessageForField($sFieldName, $aFieldMessages)
    {
        $sMessage = '';
        if (is_array($aFieldMessages) && isset($aFieldMessages[$sFieldName])) {
            $sMessage = $aFieldMessages[$sFieldName];
        }

        return $sMessage;
    }

    /**
     * Get user input value for given field name from given array.
     *
     * @template T
     * @param array<string, T> $aUserData
     * @param string $sFieldName
     * @return T|'' - empty string if field does not exist
     */
    protected function GetValueForField($sFieldName, $aUserData)
    {
        $sValue = '';
        if (isset($aUserData[$sFieldName])) {
            $sValue = $aUserData[$sFieldName];
        }

        return $sValue;
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('sSpotName');
        $oRequirements->NeedsSourceObject('sWizardModuleModuleSpot');
        $oRequirements->NeedsSourceObject('aUserInput');
        $oRequirements->NeedsSourceObject('aFieldMessages');
    }
}
