<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgExtranetRegistrationGuestMapper_Form extends AbstractViewMapper
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
        $oRequirements->NeedsSourceObject('oActiveUser', 'TdbDataExtranetUser', TdbDataExtranetUser::GetInstance());
        $oRequirements->NeedsSourceObject('oExtranetConfiguration', 'TdbDataExtranet', TdbDataExtranet::GetInstance());
        $oRequirements->NeedsSourceObject('oThankYouOrderStep', 'TdbShopOrderStep');
        $oRequirements->NeedsSourceObject('oTextBlock');
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
        /** @var $oActiveUser TdbDataExtranetUser */
        $oActiveUser = $oVisitor->GetSourceObject('oActiveUser');
        /** @var $oActiveOrderStep TdbShopOrderStep */
        $oActiveOrderStep = $oVisitor->GetSourceObject('oThankYouOrderStep');
        $oLastBoughtUser = $oActiveOrderStep->GetLastUserBoughtFromSession();
        $bRegistrationGuestIsAllowed = false;
        if ('thankyou' == $oActiveOrderStep->fieldSystemname && $oLastBoughtUser) {
            if ($oActiveUser->RegistrationGuestIsAllowed($oLastBoughtUser)) {
                /** @var $oTextBlock TdbPkgCmsTextBlock */
                $oTextBlock = $oVisitor->GetSourceObject('oTextBlock');
                if ($oTextBlock && $oTextBlock instanceof TdbPkgCmsTextBlock) {
                    $aTextBlock = array();
                    $aTextBlock['sTitle'] = $oTextBlock->fieldName;
                    $aTextBlock['sText'] = $oTextBlock->GetTextField('content');
                    $oVisitor->SetMappedValue('aTextData', $aTextBlock);
                }
                $aFieldPassword = array();
                $aFieldPassword['sValue'] = '';
                $aFieldPassword['sError'] = $this->GetMessageForField('password', TdbDataExtranetUser::MSG_FORM_FIELD);
                $oVisitor->SetMappedValue('aFieldPassword', $aFieldPassword);

                $aFieldPasswordCheck = array();
                $aFieldPasswordCheck['sValue'] = '';
                $aFieldPasswordCheck['sError'] = $this->GetMessageForField('password2', TdbDataExtranetUser::MSG_FORM_FIELD);
                $oVisitor->SetMappedValue('aFieldPasswordCheck', $aFieldPasswordCheck);

                /** @var $oExtranetConfiguration TdbDataExtranet */
                $oExtranetConfiguration = $oVisitor->GetSourceObject('oExtranetConfiguration');
                $oVisitor->SetMappedValue('sExtranetSpotName', $oExtranetConfiguration->fieldExtranetSpotName);
                $sFailureURL = $oActiveUser->GetLinkForRegistrationGuest();
                $oVisitor->SetMappedValue('sSuccesURL', $oExtranetConfiguration->GetLinkMyAccountPage());
                $oVisitor->SetMappedValue('sFailureURL', $sFailureURL);
                $bRegistrationGuestIsAllowed = true;
            }
        }
        $oVisitor->SetMappedValue('bAllowShowRegistrationGuest', $bRegistrationGuestIsAllowed);
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
}
