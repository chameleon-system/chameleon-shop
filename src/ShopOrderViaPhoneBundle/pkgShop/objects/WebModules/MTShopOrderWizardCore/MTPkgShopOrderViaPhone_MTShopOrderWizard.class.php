<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;

class MTPkgShopOrderViaPhone_MTShopOrderWizard extends MTPkgShopOrderViaPhone_MTShopOrderWizardAutoParent
{
    const ORDER_VIA_PHONE_URL_PARAMETER = 'order_via_phone';

    const ORDER_VIA_PHONE_MESSAGE_CONSUMER_NAME = 'order_via_phone';

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'OrderViaPhone';
    }

    public function Init()
    {
        parent::Init();
    }

    protected function OrderViaPhone()
    {
        $oGlobal = TGlobal::instance();
        $aUserData = $oGlobal->GetUserData(MTShopOrderWizardCore::ORDER_VIA_PHONE_URL_PARAMETER);
        if ($this->OrderViaPhoneDataValid($aUserData)) {
            if ($this->OrderViaPhoneSendEmail($aUserData)) {
                $this->OrderViaPhoneRedirectToThankYouPage();
            } else {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(MTShopOrderWizardCore::ORDER_VIA_PHONE_MESSAGE_CONSUMER_NAME, 'ORDER-VIA-PHONE-CANT-SEND-EMAIL');
            }
        }
    }

    protected function OrderViaPhoneDataValid($aUserData)
    {
        $bValid = true;
        if (is_array($aUserData) && count($aUserData) > 0) {
            $aRequiredFields = $this->OrderViaPhoneGetRequiredFields();
            $oMsgManager = TCMSMessageManager::GetInstance();
            foreach ($aRequiredFields as $sRequiredField) {
                if (!array_key_exists($sRequiredField, $aUserData) || empty($aUserData[$sRequiredField])) {
                    $bValid = false;
                    $oMsgManager->AddMessage(MTShopOrderWizardCore::ORDER_VIA_PHONE_MESSAGE_CONSUMER_NAME.'-'.$sRequiredField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                }
            }
        } else {
            $bValid = false;
        }

        return $bValid;
    }

    protected function OrderViaPhoneSendEmail($aUserData)
    {
        $oMail = TdbDataMailProfile::GetProfile('order-via-phone');
        if (!empty($aUserData['subject'])) {
            $oMail->SetSubject($aUserData['subject']);
        }
        $oMail->AddData('sFirstname', $aUserData['firstname']);
        $oMail->AddData('sLastname', $aUserData['lastname']);
        $oMail->AddData('sTelNumber', $aUserData['tel']);
        $oBasket = TShopBasket::GetInstance();
        $sArticle = $oBasket->Render('vOrderViaPhoneMail', 'Customer');
        $oMail->AddData('sArticle', $sArticle);

        return $oMail->SendUsingObjectView('emails', 'Customer');
    }

    protected function OrderViaPhoneRedirectToThankYouPage()
    {
        $url = $this->getSystemPageService()->getLinkToSystemPageRelative('order-via-phone');
        $this->getRedirect()->redirect($url);
    }

    protected function OrderViaPhoneGetRequiredFields()
    {
        return array('firstname', 'lastname', 'tel');
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
