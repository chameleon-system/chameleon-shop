<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSWizardStepMapper_UserProfilePassword extends AbstractTCMSWizardStepMapper
{
    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $sSpotName = $oVisitor->GetSourceObject('sSpotName');
        $sWizardSpotName = $oVisitor->GetSourceObject('sWizardModuleModuleSpot');
        $sCustomMSGConsumer = $oVisitor->GetSourceObject('sCustomMSGConsumer');
        /** @var $oWizardStep TCMSWizardStep */
        $oWizardStep = $oVisitor->GetSourceObject('oObject');
        $aFieldList = array('aFieldOldPassword' => 'sRequirePassword', 'aFieldNewPassword' => 'password', 'aFieldNewPasswordCheck' => 'password2');
        $this->SetInputFields($aFieldList, $oVisitor, $sCustomMSGConsumer);

        $aTextData = array();
        $aTextData['sTitle'] = $oWizardStep->fieldName;
        $aTextData['sText'] = $oWizardStep->GetTextField('description');

        $sUrl = $oWizardStep->GetStepURL();

        $oVisitor->SetMappedValue('sSpotName', $sSpotName);
        $oVisitor->SetMappedValue('sWizardSpotName', $sWizardSpotName);
        $oVisitor->SetMappedValue('aTextData', $aTextData);
        $oVisitor->SetMappedValue('sWizardSpotName', $sWizardSpotName);
        $oVisitor->SetMappedValue('sStepURL', $sUrl);
        $oVisitor->SetMappedValue('sCustomMSGConsumer', $sCustomMSGConsumer);
        $oVisitor->SetMappedValue('sSuccessMessage', $this->GetMessageForField('sChangePasswordSuccess', $sCustomMSGConsumer));
    }

    /**
     * set errors and values for given field list.
     *
     * @param array                    $aFieldList   (MappedFieldName(name used in template) => RealFieldName (user input field name) )
     * @param IMapperVisitorRestricted $oVisitor
     * @param string                   $sMSGConsumer
     *
     * @return void
     */
    protected function SetInputFields($aFieldList, $oVisitor, $sMSGConsumer)
    {
        foreach ($aFieldList as $sMappedFieldName => $sRealFieldName) {
            $aField = array();
            $aField['sError'] = $this->GetMessageForField($sRealFieldName, $sMSGConsumer);
            $aField['sValue'] = '';
            $oVisitor->SetMappedValue($sMappedFieldName, $aField);
        }
    }

    /**
     * Set error message for given field from message manager.
     *
     * @param string $sFieldName
     * @param string $sCustomMSGConsumer
     *
     * @return string
     */
    protected function GetMessageForField($sFieldName, $sCustomMSGConsumer)
    {
        $sMessage = '';
        $oMsgManager = TCMSMessageManager::GetInstance();
        if ($oMsgManager->ConsumerHasMessages($sCustomMSGConsumer.'-'.$sFieldName)) {
            $sMessage = $oMsgManager->RenderMessages($sCustomMSGConsumer.'-'.$sFieldName);
        }

        return $sMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        parent::GetRequirements($oRequirements);
        $oRequirements->NeedsSourceObject('sSpotName');
        $oRequirements->NeedsSourceObject('sWizardModuleModuleSpot');
        $oRequirements->NeedsSourceObject('sCustomMSGConsumer');
    }
}
