<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerEasyCash_Debit extends TShopPaymentHandlerEasyCash
{
    const EASY_CASH_DEBIT_PATH = '/edddirect.aspx';
    const EASY_CASH_CONFIRM_PATH = '/confirm.aspx';
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerEasyCashDebitMSG';

    /**
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder, $sMessageConsumer);
        if ($bPaymentOk) {
            $aEasyCashPayload = $this->GetPaymentPayload($oOrder);

            $aResponse = $this->SendRequestToEasyCash(self::EASY_CASH_DEBIT_PATH, $aEasyCashPayload);
            $bPaymentOk = $this->ProcessOrderResponse($oOrder, $aResponse, $aEasyCashPayload, $sMessageConsumer);
        }

        return $bPaymentOk;
    }

    /**
     * return true if the user data is valid
     * data is loaded from GetUserPaymentData().
     *
     * @return bool
     */
    public function ValidateUserInput()
    {
        $bIsValid = parent::ValidateUserInput();
        $oMsgManager = TCMSMessageManager::GetInstance();
        $aRequiredFields = array('accountNr', 'accountOwner', 'bankNr', 'bankName');
        foreach ($aRequiredFields as $sRequiredField) {
            $sValue = (array_key_exists($sRequiredField, $this->aPaymentUserData)) ? ($this->aPaymentUserData[$sRequiredField]) : ('');
            $sValue = trim($sValue);
            if (empty($sValue)) {
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-'.$sRequiredField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    /**
     * @param TdbShopOrder $oOrder
     * @param string       $aResponse        - te raw response inkl. header
     * @param string       $aRequestData     - the original request data. we pass this in case some extension wants to log the request
     * @param string       $sMessageConsumer
     *
     * @return bool
     */
    protected function ProcessOrderResponse($oOrder, $aResponse, $aRequestData, $sMessageConsumer)
    {
        $bPaymentOk = false;

        $sResponseBody = $aResponse['response'];
        $aResponseParameter = array();
        parse_str($sResponseBody, $aResponseParameter);

        $sPayId = (array_key_exists('PayID', $aResponseParameter)) ? ($aResponseParameter['PayID']) : ('');
        $sXID = (array_key_exists('XID', $aResponseParameter)) ? ($aResponseParameter['XID']) : ('');
        $sStatus = (array_key_exists('Status', $aResponseParameter)) ? ($aResponseParameter['Status']) : ('');
        $sErrorCode = (array_key_exists('Code', $aResponseParameter)) ? ($aResponseParameter['Code']) : ('');

        if (0 == strcasecmp($sStatus, 'OK') || 0 == strcasecmp($sStatus, 'AUTHORIZED')) {
            // confirm request with PayID and XID
            $iMaxConfirmAttempts = 3;
            $bConfirmed = false;
            do {
                --$iMaxConfirmAttempts;
                $bConfirmed = $this->ConfirmEasyCashResponse($sPayId, $sXID);
            } while (!$bConfirmed && $iMaxConfirmAttempts >= 0);

            if ($bConfirmed) {
                $bPaymentOk = true;
            } else {
                TTools::WriteLogEntry('EasyCash ProcessOrderResponse Error OrderID-'.$oOrder->id.': UNABLE TO CONFIRM - giving up after 3 requests; ', 1, __FILE__, __LINE__);
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => TGlobal::Translate('chameleon_system_shop.payment_easy_cash.error_unable_to_confirm_payment')));
            }
        } else {
            TTools::WriteLogEntry('EasyCash ProcessOrderResponse Error OrderID-'.$oOrder->id.': '.$sErrorCode.'; '.$sResponseBody, 1, __FILE__, __LINE__);
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => $sErrorCode));
        }
        // save data to order
        foreach ($aResponseParameter as $sParamName => $sParamKey) {
            $aInfo = array('shop_order_id' => $oOrder->id, 'name' => 'PayResponse '.date('Y-m-d H:i:s').': '.$sParamName, 'value' => $sParamKey);
            $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
            $oPaymentInfo->AllowEditByAll(true);
            $oPaymentInfo->Save();
        }

        return $bPaymentOk;
    }

    /**
     * returns true if the transaction confirmation was successfully send to EasyCash.
     *
     * @param $sPayId
     * @param $sXID
     *
     * @return bool
     */
    protected function ConfirmEasyCashResponse($sPayId, $sXID)
    {
        $bConfirmed = false;
        $aParam = array('PayID' => $sPayId, 'XID' => $sXID);
        $aResponse = $this->SendRequestToEasyCash(self::EASY_CASH_CONFIRM_PATH, $aParam);
        if (is_array($aResponse) && stristr($aResponse['header'], 'HTTP/1.1 200 OK')) {
            $bConfirmed = true;
        } else {
            TTools::WriteLogEntry("EasyCash ConfirmEasyCashResponse Error: UNABLE TO CONFIRM; sPayId: {$sPayId}; sXID: {$sXID}".print_r($aResponse, true), 1, __FILE__, __LINE__);
        }

        return $bConfirmed;
    }

    /**
     * return an array with all parameters required by easy cash for the payment execution.
     *
     * @param TdbShopOrder $oOrder
     * @param null         $dPaymentAmount
     *
     * @return array
     */
    protected function GetPaymentPayload($oOrder, $dPaymentAmount = null)
    {
        $aPayload = parent::GetPaymentPayload($oOrder, $dPaymentAmount);
        $aPayload['AccNr'] = str_replace(' ', '', $this->aPaymentUserData['accountNr']);
        $aPayload['AccIBAN'] = str_replace(' ', '', $this->aPaymentUserData['bankNr']);
        $aPayload['AccBank'] = $this->aPaymentUserData['bankName'];
        $aPayload['AccOwner'] = $this->aPaymentUserData['accountOwner'];

        return $aPayload;
    }

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerEasyCash_Debit';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['accountNr'] = '';
        $aData['accountOwner'] = '';
        $aData['bankNr'] = '';
        $aData['bankName'] = '';

        return $aData;
    }
}
