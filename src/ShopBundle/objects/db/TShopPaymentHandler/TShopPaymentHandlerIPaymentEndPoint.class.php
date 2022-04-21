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
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\UrlUtil;

/**
 * don't use this Class to set up an payment handler because this call has only
 * standard content. Use TShopPaymentHandlerIPaymentCreditCard or TShopPaymentHandlerIPaymentDebit instead.
 */
class TShopPaymentHandlerIPaymentEndPoint extends TdbShopPaymentHandler implements IPkgShopPaymentIPNPaymentHandler
{
    /**
     * constant for class specific error messages.
     */
    const MSG_MANAGER_NAME = 'TShopIPaymentHandlerdMSG';

    /**
     * System sets constant to url that the system knows its a ipayment call.
     */
    const URL_PARAMETER_NAME = 'i_payment';

    /**
     * id for saved payment data on ipayment server.
     *
     * @var $sStorageId
     */
    protected $sStorageId = false;

    /**
     * method is called after the user selected his payment and submitted the payment page
     * return false if you want to send the user back to the payment selection page.
     *
     * DeuCS performs no processing at this step. we keep the overwritten function here only to clarity
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    public function PostSelectPaymentHook($sMessageConsumer)
    {
        $bContinue = parent::PostSelectPaymentHook($sMessageConsumer);
        if (true == $bContinue) {
            // we need a non empty sStorageId to continue
            if (empty($this->sStorageId)) {
                $bContinue = false;
            }
        }

        return $bContinue;
    }

    /**
     * return the URL to which the user input is send. using this method you can redirect the input
     * to go directly to, for example, the payment provider. if you return an empty string, the default target (ie the payment
     * data entry step) will be used.
     *
     * @return string
     */
    public function GetUserInputTargetURL()
    {
        return $this->GetRequestURL();
    }

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath();
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['cc_typ'] = '';
        $aData['cc_number'] = '';
        $aData['addr_name'] = '';
        $aData['cc_expdate_month'] = date('n');
        $aData['cc_expdate_year'] = date('Y');
        $aData['cc_expdate_month'] = '';

        return $aData;
    }

    /**
     * Get all needed parameter for first request to ipayment
     * Contain standat parameter and storage parameter.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetAllInputFieldParameter($aParameter = array())
    {
        if (!is_array($aParameter)) {
            $aParameter = array();
        }
        $aParameter = $this->GetStandardInputFieldParameter($aParameter);
        $aParameter['trx_typ'] = $this->GetConfigParameter('trx_typ');
        $aParameter['use_datastorage'] = $this->GetConfigParameter('use_datastorage');
        $aParameter['datastorage_reuse_method'] = $this->GetConfigParameter('datastorage_reuse_method');
        $sDataExpireDateSec = time() + ((int) $this->GetConfigParameter('expiretime_min')) * 60;
        $aParameter['datastorage_expirydate'] = date('Y/m/d H:i:s', $sDataExpireDateSec);
        $oGlobal = TGlobal::instance();
        $aParameter['spot'] = $oGlobal->GetExecutingModulePointer()->sModuleSpotName;

        return $aParameter;
    }

    /**
     * Get standard parameters needed for all requests to Ipayment.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetStandardInputFieldParameter($aParameter = array())
    {
        if (!is_array($aParameter)) {
            $aParameter = array();
        }

        $oShopBasket = TShopBasket::GetInstance();

        $aParameter['ppcdccd'] = '51a492928930f294568ff'; // chameleon ipayment identifier
        $aParameter['trxuser_id'] = $this->GetConfigParameter('trxuser_id');
        $aParameter['trxpassword'] = $this->GetConfigParameter('transaction_password');
        $aParameter['adminactionpassword'] = $this->GetConfigParameter('adminactionpassword');
        $aParameter['silent'] = $this->GetConfigParameter('silent_mode');
        $aParameter['advanced_strict_id_check'] = $this->GetConfigParameter('advanced_strict_id_check');
        $aParameter['return_paymentdata_details'] = $this->GetConfigParameter('return_paymentdata_details');

        $aParameter['shopper_id'] = $oShopBasket->sBasketIdentifier;
        $aParameter['trx_amount'] = round($oShopBasket->dCostTotal * 100, 0);
        $aParameter['trx_currency'] = $this->GetCurrencyIdentifier();

        $sSecurityHash = $this->GenerateSecurityHash($aParameter);
        if (!empty($sSecurityHash)) {
            $aParameter['trx_securityhash'] = $sSecurityHash;
        }

        $oStep = $this->GetActiveOrderStep();
        $redirectUrl = $this->GetExecutePaymentErrorURL($oStep->fieldSystemname);
        $aParameter['redirect_url'] = $redirectUrl.'/'.self::URL_PARAMETER_NAME.'/success';
        $aParameter['silent_error_url'] = $redirectUrl.'/'.self::URL_PARAMETER_NAME.'/error';

        return $aParameter;
    }

    /**
     * return the security hash for the input data.
     *
     * @param $aParameter
     *
     * @return string
     */
    protected function GenerateSecurityHash($aParameter)
    {
        $trx_securityhash = '';
        $sSharedSecret = $this->GetConfigParameter('shared_secret');
        if (!empty($sSharedSecret)) {
            $trx_securityhash = md5($aParameter['trxuser_id'].$aParameter['trx_amount'].$aParameter['trx_currency'].$aParameter['trxpassword'].$sSharedSecret);
        }

        return $trx_securityhash;
    }

    protected function GetRedirectUrl()
    {
        return $this->getActivePageService()->getActivePage()->GetRealURLPlain(array(), true);
    }

    /**
     * Return payment method specific parameter
     * Overwrite this if you want to add specific paramters.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetPaymentTypeSpecifivParameter($aParameter = array())
    {
        if (!is_array($aParameter)) {
            $aParameter = array();
        }

        return $aParameter;
    }

    /**
     * Get the active oder step neede for redirect url.
     *
     * @return TdbShopOrderStep|null
     */
    protected function GetActiveOrderStep()
    {
        $oActiveOrderStep = null;
        $oGlobal = TGlobal::instance();
        $sActiveStepSysName = $oGlobal->GetUserData('stpsysname');
        $oActiveOrderStep = TdbShopOrderStep::GetStep($sActiveStepSysName);

        return $oActiveOrderStep;
    }

    /**
     * Get parameter to pay a transaction on IPayment with saved storage id.
     *
     * @param array $aParameter
     *
     * @return array $aParameter
     */
    protected function GetPayFieldParameter($aParameter = array(), $oOrder)
    {
        if (!is_array($aParameter)) {
            $aParameter = array();
        }
        $aParameter = $this->GetStandardInputFieldParameter($aParameter);
        if (!empty($this->sStorageId)) {
            $aParameter['from_datastorage_id'] = $this->sStorageId;
        }
        $oShopBasket = TShopBasket::GetInstance();
        $oActivePaymentMethod = $oShopBasket->GetActivePaymentMethod();
        $aParameter['shop_payment_method_id'] = $oActivePaymentMethod->id;
        $redirectUrl = $this->GetRedirectUrl();
        $sSilentErrorURL = $this->GetExecutePaymentErrorURL();
        if ($sSilentErrorURL) {
            $aParameter['silent_error_url'] = $sSilentErrorURL.'/'.self::URL_PARAMETER_NAME.'/error';
        }
        $aParameter['redirect_url'] = $redirectUrl;
        $oIPNManager = new TPkgShopPaymentIPNManager();
        $oPortal = $this->getPortalDomainService()->getActivePortal();
        $aParameter['hidden_trigger_url'] = $oIPNManager->getIPNURL($oPortal, $oOrder);
        $aParameter['expire_datastorage'] = true;
        $aParameter['execute_order'] = true;
        $oGlobal = TGlobal::instance();
        $aParameter['spot'] = $oGlobal->GetExecutingModulePointer()->sModuleSpotName;
        $aParameter['trx_typ'] = $this->GetConfigParameter('trx_typ_pay');

        return $aParameter;
    }

    /**
     * Get the redirect error url on payment execute.
     *
     * @param string $stepName
     *
     * @return string $sURL
     */
    protected function GetExecutePaymentErrorURL($stepName = '')
    {
        if (empty($stepName)) {
            $stepName = 'shipping';
        }
        $sURL = false;
        $oShippingStep = TdbShopOrderStep::GetStep($stepName);
        if (null !== $oShippingStep) {
            $sURL = $oShippingStep->GetStepURL(true, true);
            if ('/' === substr($sURL, -1)) {
                $sURL = substr($sURL, 0, -1);
            }
        }

        return $sURL;
    }

    /**
     * Get user address data as array.
     *
     * @return array $aUserAddressData
     */
    protected function GetUserAddressData()
    {
        $aUserAddressData = array();
        $oActiveUser = TdbDataExtranetUser::GetInstance();
        if ($oActiveUser) {
            $oBillingAddress = $oActiveUser->GetBillingAddress();
            if ($oBillingAddress) {
                $aUserAddressData['addr_street'] = $oBillingAddress->fieldStreet.' '.$oBillingAddress->fieldStreetnr;
                $aUserAddressData['addr_zip'] = $oBillingAddress->fieldPostalcode;
                $aUserAddressData['addr_city'] = $oBillingAddress->fieldCity;
                $aUserAddressData['addr_country'] = $this->GetBillingCountryISOCode($oBillingAddress);
                $aUserAddressData['addr_email'] = $oActiveUser->fieldName;
            }
        }

        return $aUserAddressData;
    }

    /**
     * Get the iso code from users billing address.
     *
     * @param TdbDataExtranetUserAddress $oBillingAddress
     *
     * @return string $sIsoCode
     */
    protected function GetBillingCountryISOCode($oBillingAddress)
    {
        $sIsoCode = '';
        $oCountry = $oBillingAddress->GetFieldDataCountry();
        if (!is_null($oCountry)) {
            $oSystemCountry = $oCountry->GetFieldTCountry();
            if (!is_null($oSystemCountry)) {
                $sIsoCode = $oSystemCountry->fieldIsoCode2;
            }
        }

        return $sIsoCode;
    }

    /**
     * Get the ipayment request url with replaced account id.
     *
     * @return string $sRequestUrl
     */
    protected function GetRequestURL()
    {
        $sAccountId = $this->GetConfigParameter('account_id');
        $sRequestUrl = $this->GetConfigParameter('request_url');
        $sRequestUrl = str_replace('[{sAccountId}]', $sAccountId, $sRequestUrl);

        return $sRequestUrl;
    }

    /**
     * added all needed parameter for a request to IPayment
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = parent::GetAdditionalViewVariables($sViewName, $sViewType);
        if (!is_array($aViewVariables)) {
            $aViewVariables = array();
        }
        $aViewVariables['sRequestUrl'] = $this->GetRequestURL();
        $aViewVariables['IPaymentHiddenInput'] = $this->GetAllInputFieldParameter();
        $aViewVariables['aUserAddressData'] = $this->GetUserAddressData();
        $aViewVariables['aPaymenttypeSpecificParameter'] = $this->GetPaymentTypeSpecifivParameter();
        //      $this->SetErrorCodesFromResponseToMessageManager();
        return $aViewVariables;
    }

    /**
     * return an array with all parameters that need to be sent to ipayment in addition to the user-specific data (ie cc-data or debit data).
     *
     * @return array
     */
    public function getRequestParameters()
    {
        $aViewVariables = array();

        $aTmp = $this->GetAllInputFieldParameter();
        foreach (array_keys($aTmp) as $sTmpKey) {
            $aViewVariables[$sTmpKey] = $aTmp[$sTmpKey];
        }
        $aTmp = $this->GetUserAddressData();
        foreach (array_keys($aTmp) as $sTmpKey) {
            $aViewVariables[$sTmpKey] = $aTmp[$sTmpKey];
        }
        $aTmp = $this->GetPaymentTypeSpecifivParameter();
        foreach (array_keys($aTmp) as $sTmpKey) {
            $aViewVariables[$sTmpKey] = $aTmp[$sTmpKey];
        }

        return $aViewVariables;
    }

    /**
     * Overwrite this to generate messages.
     */
    protected function SetErrorCodesFromResponseToMessageManager()
    {
    }

    /**
     * Get error message sent by IPayment to cms.
     *
     * @return string|false
     */
    protected function GetErrorCodesFromResponse()
    {
        $SReturnMessage = false;
        if ($this->bIsCorrectIPaymentType()) {
            $oGlobal = TGlobal::instance();
            $SReturnState = $oGlobal->GetUserData('ret_status');
            if ('ERROR' == $SReturnState) {
                $SReturnMessage = $oGlobal->GetUserData('ret_errormsg');
                $SReturnMessage = utf8_encode($SReturnMessage);
            }
        }

        return $SReturnMessage;
    }

    /**
     * Save storage id sent from IPayment and make security check
     * the method is called when an external payment handler returns successfully.
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        $bPaymentTransmitOk = parent::PostProcessExternalPaymentHandlerHook();
        if ($bPaymentTransmitOk) {
            $oGlobal = TGlobal::instance();
            $sStatus = $oGlobal->GetUserData('ret_status');
            if ('ERROR' == $sStatus) {
                $this->SetErrorCodesFromResponseToMessageManager();
                $bPaymentTransmitOk = false;
            }
            $this->sStorageId = $oGlobal->GetUserData('storage_id');
            $this->aPaymentUserData = $oGlobal->GetUserData();
            // the data from ipayment is not returned as iso-8859
            foreach (array_keys($this->aPaymentUserData) as $sKey) {
                if (!is_array($this->aPaymentUserData[$sKey])) {
                    $this->aPaymentUserData[$sKey] = utf8_encode($this->aPaymentUserData[$sKey]);
                }
            }
            $this->aPaymentUserData['sStorageId'] = $this->sStorageId;
        }

        if (true === $bPaymentTransmitOk) {
            $aSecuritiyCheckPostParameter = $this->GetNeedeResponsePostParameterForSecurityCheck();
            $bPaymentTransmitOk = $this->IsSecurityCheckOk($aSecuritiyCheckPostParameter['trxuser_id'], $aSecuritiyCheckPostParameter['trx_amount'], $aSecuritiyCheckPostParameter['trx_currency'], $aSecuritiyCheckPostParameter['ret_authcode'], $aSecuritiyCheckPostParameter['ret_trx_number'], $aSecuritiyCheckPostParameter['ret_param_checksum']);
            if (false === $bPaymentTransmitOk) {
                // security error - unable to validate hash
                TTools::WriteLogEntry('iPayment Error: unable to validate security hash. Either the Request is manipulated, or the "Transaktions-Security-Key" was not set in the ipayment configuration', 1, __FILE__, __LINE__);
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(TShopPaymentHandlerIPaymentCreditCard::MSG_MANAGER_NAME, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => 'unable to validate security hash. Either the Request is manipulated, or the "Transaktions-Security-Key" was not set in the ipayment configuration'));
            }
        }

        return $bPaymentTransmitOk;
    }

    /**
     * executes payment for order.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder);
        if ($bPaymentOk) {
            TdbShopPaymentHandler::SetExecutePaymentInterrupt(true);
            $bPaymentOk = $this->ExecuteIPaymentCall($oOrder);
        }

        return $bPaymentOk;
    }

    /**
     * The method is called from TShopBasket AFTER ExecutePayment was successfully executed.
     * The method is ALSO called, if the payment handler passed execution to an external service from within the ExecutePayment
     * Method.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function PostExecutePaymentHook(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = false;
        $oGlobal = TGlobal::instance();

        $aResult = $this->GetNeedeResponsePostParameterForSecurityCheck();
        $aResult['ret_status'] = $oGlobal->GetUserData('ret_status');
        $this->aPaymentUserData = $aResult;
        $this->aPaymentUserData['sStorageId'] = $this->sStorageId;
        $this->aPaymentUserData['aReturnPayload'] = print_r($this->aPaymentUserData, true);
        $this->aPaymentUserData['bChameleonPaymentOk'] = '0';
        if (count($aResult) > 0 && array_key_exists('ret_status', $aResult) && 'SUCCESS' == $aResult['ret_status']) {
            $bPaymentOk = true;
        }
        if ($bPaymentOk) {
            $bPaymentOk = $this->IsSecurityCheckOk($aResult['trxuser_id'], $aResult['trx_amount'], $aResult['trx_currency'], $aResult['ret_authcode'], $aResult['ret_trx_number'], $aResult['ret_param_checksum']);
            if ($bPaymentOk) {
                $this->aPaymentUserData['IsSecurityCheckOk'] = '1';
            } else {
                $this->aPaymentUserData['IsSecurityCheckOk'] = '0';
            }
        }
        if ($bPaymentOk) {
            $oOrder->SetStatusPaid();
            $this->aPaymentUserData['bChameleonPaymentOk'] = '1';
        }
        parent::PostExecutePaymentHook($oOrder); // mark payment method as completed

        return $bPaymentOk;
    }

    /**
     * Send payment call to IPayment and check if payment was successfully done.
     */
    protected function ExecuteIPaymentCall($oOrder)
    {
        $parameters = $this->GetPayFieldParameter(array(), $oOrder);
        $requestUrl = $this->GetRequestURL();
        $url = $requestUrl.$this->getUrlUtil()->getArrayAsUrl($parameters, '?', '&');
        $this->getRedirect()->redirect($url);
    }

    /**
     * Check if the payment call to IPayment was succesfully done
     * by parameter ret_status and hash check.
     *
     * deprecated
     *
     * @param string $response if empty function get parameter from post
     *
     * @return bool $bPaymentOk
     */
    protected function IsSuccessResponse($response = '')
    {
        $bPaymentOk = false;
        $oGlobal = TGlobal::instance();
        if (empty($response)) {
            $aResult = $this->GetNeedeResponsePostParameterForSecurityCheck();
            $aResult['ret_status'] = $oGlobal->GetUserData('ret_status');
        } else {
            $aResult = $this->GetResultFormResponse($response);
        }

        if (count($aResult) > 0 && array_key_exists('ret_status', $aResult) && 'SUCCESS' == $aResult['ret_status']) {
            $bPaymentOk = true;
        }
        if ($bPaymentOk) {
            $bPaymentOk = $this->IsSecurityCheckOk($aResult['trxuser_id'], $aResult['trx_amount'], $aResult['trx_currency'], $aResult['ret_authcode'], $aResult['ret_trx_number'], $aResult['ret_param_checksum']);
        }

        return $bPaymentOk;
    }

    /**
     * make md5 check over security parameter with shared secret and chek resutl with
     * hash from IPayment.
     *
     * @param string $trxuser_id
     * @param string $trx_amount
     * @param string $trx_currency
     * @param string $trxpassword
     * @param string $ret_trx_number
     * @param string $sOldChecksum
     *
     * @return bool $bChecksumIsOk
     */
    protected function IsSecurityCheckOk($trxuser_id, $trx_amount, $trx_currency, $trxpassword, $ret_trx_number, $sOldChecksum)
    {
        $bChecksumIsOk = true;
        $sSharedSecret = $this->GetConfigParameter('shared_secret');
        if (!empty($sSharedSecret)) {
            $sNewChecksum = md5($trxuser_id.$trx_amount.$trx_currency.$trxpassword.$ret_trx_number.$sSharedSecret);
            if ($sNewChecksum != $sOldChecksum) {
                $bChecksumIsOk = false;
            }
        }

        return $bChecksumIsOk;
    }

    /**
     * Get parameter from IPayment response which are needed to check if payment request was successful.
     *
     * @param string $response
     *
     * @return array $aNeededResponseParameterToCheck
     */
    protected function GetResultFormResponse($response)
    {
        $aNeededResponseParameterToCheck = array('ret_status' => '', 'ret_errormsg' => '', 'trxuser_id' => '', 'trx_amount' => '', 'trx_currency' => '', 'ret_authcode' => '', 'ret_trx_number' => '', 'ret_param_checksum' => '');
        preg_match('/<a href=".*'.self::URL_PARAMETER_NAME.'\\/(success|error)\?(.*)">/', $response, $aMatch);
        if (count($aMatch) > 2) {
            $aResponseParts = explode('&', $aMatch[2]);
            foreach ($aResponseParts as $sRepsponsePart) {
                $aResponseKeyValue = explode('=', $sRepsponsePart);
                if (2 == count($aResponseKeyValue) && array_key_exists($aResponseKeyValue[0], $aNeededResponseParameterToCheck)) {
                    $aNeededResponseParameterToCheck[$aResponseKeyValue[0]] = $aResponseKeyValue[1];
                }
            }
        }

        return $aNeededResponseParameterToCheck;
    }

    /**
     * Get response parameter from IPayment which are needed to make a security hash check.
     *
     * @return array $aParameter
     */
    protected function GetNeedeResponsePostParameterForSecurityCheck()
    {
        $aParameter = array();
        $oGlobal = TGlobal::instance();
        $aParameter['trxuser_id'] = $oGlobal->GetUserData('trxuser_id');
        $aParameter['trx_amount'] = $oGlobal->GetUserData('trx_amount');
        $aParameter['trx_currency'] = $oGlobal->GetUserData('trx_currency');
        $aParameter['ret_authcode'] = $oGlobal->GetUserData('ret_authcode');
        $aParameter['ret_trx_number'] = $oGlobal->GetUserData('ret_trx_number');
        $aParameter['ret_param_checksum'] = $oGlobal->GetUserData('ret_param_checksum');

        return $aParameter;
    }

    /**
     * Check after response from IPayment if the paymenthandler is the one which sent request to IPayment.
     *
     * @return bool $bIsCorrectIPaymentType
     */
    protected function bIsCorrectIPaymentType()
    {
        $bIsCorrectIPaymentType = false;
        $aParameter = $this->GetPaymentTypeSpecifivParameter();
        $oGlobal = TGlobal::instance();
        $sResponsePaymentType = $oGlobal->GetUserData('trx_paymenttyp');
        if (!empty($sResponsePaymentType) && array_key_exists('trx_paymenttyp', $aParameter) && $aParameter['trx_paymenttyp'] == $sResponsePaymentType) {
            $bIsCorrectIPaymentType = true;
        }

        return $bIsCorrectIPaymentType;
    }

    /**
     * some payment methods (such as paypal) get a reference number from the external
     * service, that allows the shop owner to identify the payment executed in their
     * Webservice. Since it is sometimes necessary to provided this identifier.
     *
     * every payment method that provides such an identifier needs to overwrite this method
     *
     * returns an empty string, if the method has no identifier.
     *
     * @return string
     */
    public function GetExternalPaymentReferenceIdentifier()
    {
        $sIdent = '';
        if (is_array($this->aPaymentUserData) && array_key_exists('ret_trx_number', $this->aPaymentUserData)) {
            $sIdent = $this->aPaymentUserData['ret_trx_number'];
        }

        return $sIdent;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return UrlUtil
     */
    private function getUrlUtil()
    {
        return ServiceLocator::get('chameleon_system_core.util.url');
    }

    private function getPortalDomainService(): PortalDomainServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return ServiceLocator::get('chameleon_system_core.redirect');
    }
}
