<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerOgone extends TShopPaymentHandlerOgoneBase
{
    /**
     * executes payment for order. this method is called, wenn the user confirmed the
     * order. if you need to transfer controll to an external process (as may be required for 3d secure)
     * then you can use self::SetExecutePaymentInterrupt(true) before passing controll. this tells
     * the shop to continue order execution as soon as controll is passed back to it. (chameleon will
     * call $this->PostExecutePaymentHook() as soon as controll is returned  - this is where you should
     * check if the data returnd is valid/invalid.
     *
     * Redirect to ogone payment service
     *
     * @param string $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        TTools::WriteLogEntry('In ExecutePayment Ogone for order id '.$oOrder->id." (nr: {$oOrder->fieldOrdernumber}", 4, __FILE__, __LINE__);
        $bPaymentOk = parent::ExecutePayment($oOrder);
        if ($bPaymentOk) {
            $sExternalPaymentURL = $this->GetExternalPaymentHandlerURL($oOrder);
            TTools::WriteLogEntry('OGONE: request string '.$sExternalPaymentURL, 4, __FILE__, __LINE__);
            TdbShopPaymentHandler::SetExecutePaymentInterrupt(true);
            $this->getRedirect()->redirect($sExternalPaymentURL);
        }

        return $bPaymentOk;
    }

    /**
     * return url to ogone payment service with all needed parameter.
     *
     * @return string
     */
    protected function GetExternalPaymentHandlerURL(TdbShopOrder $oOrder)
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        $oCountry = $oUser->GetCountry();
        $sActiveFrontEndLanguageCode = self::getLanguageService()->getLanguageIsoCode();
        $aParameter = [
            'PSPID' => $this->GetPSPID(),
            'ORDERID' => $oOrder->fieldOrdernumber,
            'AMOUNT' => $oOrder->fieldValueTotal * 100,
            'CURRENCY' => $this->GetCurrency(),
            'LANGUAGE' => strtolower($sActiveFrontEndLanguageCode).'_'.strtoupper($sActiveFrontEndLanguageCode),
            'CN' => $oUser->fieldFirstname.' '.$oUser->fieldLastname,
            'EMAIL' => $oUser->fieldName,
            'OWNERADDRESS' => $oUser->fieldStreet.' '.$oUser->fieldStreetnr,
            'OWNERCTY' => $oCountry->fieldName,
            'OWNERTOWN' => $oUser->fieldCity,
            'OWNERZIP' => $oUser->fieldPostalcode,
            'PARAMPLUS' => 'PAYCALL='.self::URL_IDENTIFIER.'&PAYHAID='.$this->sqlData['cmsident'], 'PARAMVAR' => self::URL_IDENTIFIER_NOTIFY, ];
        $this->AddCustomParameter($aParameter);
        $sOgonePaymentLayoutPageURL = $this->GetOgonePaymentLayoutPage();
        if ($sOgonePaymentLayoutPageURL) {
            $aParameter['TP'] = $sOgonePaymentLayoutPageURL;
        }
        $aParameter['ACCEPTURL'] = $this->GetResponseURL('success');
        $aParameter['DECLINEURL'] = $this->GetResponseURL('decline');
        $aParameter['SHASIGN'] = $this->BuildOutgoingHash($aParameter);
        $sExternalHandlerURL = $this->GetPaymentURL().'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));

        return $sExternalHandlerURL;
    }

    /**
     * @param array<string, mixed> $parameter
     *
     * @return void
     */
    protected function AddCustomParameter($parameter)
    {
    }

    /**
     * Returns the PSPID for test or live modus.
     *
     * @return string|false
     */
    protected function GetPSPID()
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            $sPSPID = $this->GetConfigParameter('user_id');
        } else {
            $sPSPID = $this->GetConfigParameter('user_id_test');
        }

        return $sPSPID;
    }

    /**
     * Get the URL to the payment layout.
     * Return false if not configured.
     *
     * @return bool|mixed|string
     */
    protected function GetOgonePaymentLayoutPage()
    {
        $sOgonePaymentLayoutSystemPage = trim($this->GetConfigParameter('layout_system_page'));
        $sOgonePaymentLayoutPageURL = false;
        if (!empty($sOgonePaymentLayoutSystemPage)) {
            $oShop = ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
            $sOgonePaymentLayoutPageURL = $oShop->GetLinkToSystemPage($sOgonePaymentLayoutSystemPage, null, true);
            $sOgonePaymentLayoutPageURL = str_replace('https://', '', $sOgonePaymentLayoutPageURL);
            $sOgonePaymentLayoutPageURL = str_replace('http://', '', $sOgonePaymentLayoutPageURL);
            $sOgonePaymentLayoutPageURL = 'https://'.$sOgonePaymentLayoutPageURL;
        }

        return $sOgonePaymentLayoutPageURL;
    }

    /**
     * The method is called from TShopBasket AFTER ExecutePayment was successfully executed.
     * The method is ALSO called, if the payment handler passed execution to an external service from within the ExecutePayment
     * Method. Note: if you return false, then the system will return to the called order step and the
     * order will be canceled (shop_order.canceled = 1) - the article stock will be returned.
     *
     * if you return true, then the order is marked as paid.
     *
     *
     * In Ogone we only arrive at this method, when returning from the GUI Form. We need to check if
     * a) the order has already been marked as paid (as may be the case if the notify URL arrives BEFORE the user returns)
     * b) the payment was a success
     * c) there was an error in the payment.
     *
     * @param string $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function PostExecutePaymentHook(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::PostExecutePaymentHook($oOrder, $sMessageConsumer);
        if ($bPaymentOk) {
            $oGlobal = TGlobal::instance();
            $sPaymentStatus = false;
            if ($oGlobal->UserDataExists('STATUS')) {
                $sPaymentStatus = $oGlobal->GetUserData('STATUS');
            }
            $sPassTroughNotKnowState = trim($this->GetConfigParameter('sPassTroughNotKnowState'));
            if ('false' === $sPassTroughNotKnowState || '0' === $sPassTroughNotKnowState || 0 === $sPassTroughNotKnowState) {
                $sPassTroughNotKnowState = false;
            } else {
                $sPassTroughNotKnowState = true;
            }
            if ('5' === $sPaymentStatus || '9' === $sPaymentStatus || '51' === $sPaymentStatus || '91' === $sPaymentStatus || ($sPassTroughNotKnowState && ('92' === $sPaymentStatus)) || ($sPassTroughNotKnowState && ('52' === $sPaymentStatus))) {
                $bPaymentOk = $this->ValidateResponseData($oGlobal->GetUserData(), $oOrder, $sMessageConsumer);
                if ($bPaymentOk && '9' === $sPaymentStatus) {
                    $oOrder->SetStatusPaid();
                }
            } elseif ('0' === $sPaymentStatus || '2' === $sPaymentStatus || '93' === $sPaymentStatus) { // Ogone returned with not success state
                $sErrorMessage = $oGlobal->GetUserData('NCERROR');
                TTools::WriteLogEntry("Ogone Payment: Ogone returns with error [{$oOrder->id}] [{$sErrorMessage}]", 1, __FILE__, __LINE__);
                $oOrder->SetStatusCanceled(true);
                $bPaymentOk = false;
            } elseif ('1' === $sPaymentStatus || (!$sPassTroughNotKnowState && ('92' === $sPaymentStatus)) || (!$sPassTroughNotKnowState && ('52' === $sPaymentStatus))) { // user clicked abort link. cancel order
                $oURLData = TCMSSmartURLData::GetActive();
                $oOrder->SetStatusCanceled(true);
                TTools::WriteLogEntry("Ogone Payment: user canceled payment or transaction in not known state [{$oOrder->id}]: ".print_r($oURLData, true), 1, __FILE__, __LINE__);
                $bPaymentOk = false;
            } else { // error - find out what and return false
                $oURLData = TCMSSmartURLData::GetActive();
                TTools::WriteLogEntry("Ogone Payment: unknown response for order [{$oOrder->id}]: maybe user used browser back button".print_r($oURLData, true), 1, __FILE__, __LINE__);
                $bPaymentOk = false;
            }
        }

        return $bPaymentOk;
    }

    /**
     * return true if the get/post response data is valid (order exits, amount matches, hash is valid, etc)
     * For Ogone we have only to check the security hash.
     *
     * @param array $aResponseData
     * @param string $sMessageConsumer
     *
     * @return bool
     */
    protected function ValidateResponseData($aResponseData, TdbShopOrder $oOrder, $sMessageConsumer)
    {
        $bIsValid = $this->CheckIncomingHash($aResponseData);

        return $bIsValid;
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
