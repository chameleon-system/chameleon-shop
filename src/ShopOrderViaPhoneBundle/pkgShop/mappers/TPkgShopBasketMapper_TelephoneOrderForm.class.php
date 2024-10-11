<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;

class TPkgShopBasketMapper_TelephoneOrderForm extends AbstractViewMapper
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
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        // Set per default
        $oRequirements->NeedsSourceObject('oActiveUser', 'TdbDataExtranetUser', TdbDataExtranetUser::GetInstance());
        $oRequirements->NeedsSourceObject('oActivePage', 'TCMSActivePage', $this->getActivePageService()->getActivePage(), true);
        $oRequirements->NeedsSourceObject('oGlobal', 'TGlobal', $oGlobal = TGlobal::instance());
        $oRequirements->NeedsSourceObject('oMessageManager', 'TCMSMessageManager', $oMessageManager = TCMSMessageManager::GetInstance());
        $oRequirements->NeedsSourceObject('oTextBlock', 'TdbPkgCmsTextBlock', TdbPkgCmsTextBlock::GetInstanceFromSystemName('telephone_order_info_text'), true);
        $oRequirements->NeedsSourceObject('sName', 'string', 'phone');
        $oRequirements->NeedsSourceObject('sFunction', 'string', 'OrderViaPhone');
        $oRequirements->NeedsSourceObject('sCustomMSGConsumer', 'string', MTShopOrderWizardCore::ORDER_VIA_PHONE_MESSAGE_CONSUMER_NAME);
        $oRequirements->NeedsSourceObject('sTelephoneURLParameter', 'string', MTShopOrderWizardCore::ORDER_VIA_PHONE_URL_PARAMETER);

        // Set manually
        $oRequirements->NeedsSourceObject('sSpotName', 'string', '');
        $oRequirements->NeedsSourceObject('sTitle', 'string', '', true);
        $oRequirements->NeedsSourceObject('sText', 'string', '', true);
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
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        $oVisitor->SetMappedValue('sSpotName', $oVisitor->GetSourceObject('sSpotName'));
        $oVisitor->SetMappedValue('sTitle', $oVisitor->GetSourceObject('sTitle'));
        $oVisitor->SetMappedValue('sText', $oVisitor->GetSourceObject('sText'));
        $oVisitor->SetMappedValue('sName', $oVisitor->GetSourceObject('sName'));
        $oVisitor->SetMappedValue('sFunction', $oVisitor->GetSourceObject('sFunction'));

        $oTextBlock = $oVisitor->GetSourceObject('oTextBlock');
        if (!is_null($oTextBlock)) {
            $oVisitor->SetMappedValue('sRawInfoText', $oTextBlock->GetTextField('content'));
        }
        $oActivePage = $oVisitor->GetSourceObject('oActivePage');
        if (!is_null($oActivePage)) {
            $oVisitor->SetMappedValue('sAction', $oActivePage->GetRealURLPlain());
        }

        $oMessageManager = $oVisitor->GetSourceObject('oMessageManager');
        $oVisitor->SetMappedValue('sOverallError', $oMessageManager->RenderMessages($oVisitor->GetSourceObject('sCustomMSGConsumer')));

        $aFieldList = array('aFieldFirstName' => 'firstname',
                            'aFieldLastName' => 'lastname',
                            'aFieldTel' => 'tel',
                            'aFieldReason' => 'subject',
        );
        $oUser = $oVisitor->GetSourceObject('oActiveUser');
        $oGlobal = $oVisitor->GetSourceObject('oGlobal');
        $aUserData = array('firstname' => $oUser->fieldFirstname, 'lastname' => $oUser->fieldLastname, 'tel' => $oUser->fieldTelefon, 'subject' => '');
        if ($oGlobal->UserDataExists($oVisitor->GetSourceObject('sTelephoneURLParameter'))) {
            $aUserData = $oGlobal->GetUserData($oVisitor->GetSourceObject('sTelephoneURLParameter'));
        }
        $this->SetInputFields($aFieldList, $oVisitor, $aUserData);
    }

    /**
     * set errors and values for given field list.
     *
     * @param array                    $aFieldList (MappedFieldName(name used in template) => RealFieldName (user input field name) )
     * @param IMapperVisitorRestricted $oVisitor
     * @param array<string, mixed> $aUserData
     *
     * @return void
     */
    protected function SetInputFields($aFieldList, $oVisitor, $aUserData)
    {
        foreach ($aFieldList as $sMappedFieldName => $sRealFieldName) {
            $aField = array();
            $aField['sError'] = $this->GetMessageForField($sRealFieldName, $oVisitor->GetSourceObject('sCustomMSGConsumer'), $oVisitor);
            $aField['sValue'] = $aUserData[$sRealFieldName];
            $aField['sFieldId'] = $oVisitor->GetSourceObject('sTelephoneURLParameter').'['.$sRealFieldName.']';
            $aField['sName'] = $oVisitor->GetSourceObject('sTelephoneURLParameter').'['.$sRealFieldName.']';
            $oVisitor->SetMappedValue($sMappedFieldName, $aField);
        }
    }

    /**
     * Set error message for given field from message manager.
     *
     * @param string $sFieldName
     * @param string $sCustomMSGConsumer
     * @param IMapperVisitorRestricted $oVisitor
     *
     * @return string
     */
    protected function GetMessageForField($sFieldName, $sCustomMSGConsumer, $oVisitor)
    {
        $sMessage = '';
        $oMsgManager = $oVisitor->GetSourceObject('oMessageManager');
        if ($oMsgManager->ConsumerHasMessages($sCustomMSGConsumer.'-'.$sFieldName)) {
            $sMessage = $oMsgManager->RenderMessages($sCustomMSGConsumer.'-'.$sFieldName);
        }

        return $sMessage;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
