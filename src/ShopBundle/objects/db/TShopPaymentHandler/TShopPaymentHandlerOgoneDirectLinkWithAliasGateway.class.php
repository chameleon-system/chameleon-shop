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
use ChameleonSystem\CoreBundle\ServiceLocator;

class TShopPaymentHandlerOgoneDirectLinkWithAliasGateway extends TShopPaymentHandlerOgoneAliasGateway
{
    /**
     * define message manager consumer name.
     */
    public const MSG_MANAGER_NAME = 'TShopPaymentHandlerOgoneDirectLinkWithAliasGatewayMSG';

    /**
     * parsed xml response data.
     *
     * @var array
     */
    protected $aXMLResponseData = [];

    /**
     * return the path to the views for the payment handler relative to library/classes.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return self::VIEW_PATH.'/TShopPaymentHandlerOgoneDirectLinkWithAliasGateway';
    }

    /**
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
            $aViewVariables = [];
        }
        $aViewVariables['sPaymentRequestUrl'] = $this->GetPaymentURL();

        return $aViewVariables;
    }

    /**
     * method is called after the user selected his payment and submitted the payment page
     * return false if you want to send the user back to the payment selection page.
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    public function PostSelectPaymentHook($sMessageConsumer)
    {
        $bContinue = parent::PostSelectPaymentHook($sMessageConsumer);
        if ($bContinue) {
            if (isset($this->aPaymentUserData['STATUS'])) {
                if ('0' == $this->aPaymentUserData['STATUS']) {
                    // we just created an alias, so create a transaction
                    $bContinue = $this->ExecuteExternalPaymentCall();
                } elseif ('2' == $this->aPaymentUserData['STATUS']) {
                    $messageManager = TCMSMessageManager::GetInstance();
                    $messageManager->AddMessage(TShopPaymentHandlerOgoneAliasGateway::MSG_MANAGER_NAME, 'PAYMENT-HANDLER-OGONE-ALIAS-GATEWAY-ERROR-NOT-AUTHORIZED');
                    // alias was updated, check if we have to delete old transaction and create new one (e.g. when currency or basket changed or 3D-Secure PIN was wrong)
                    // or if we just want to update/renew our transaction
                    if (!$this->TransactionMatchesCurrentBasketState() || '0' != $this->aXMLResponseData['NCERROR']) {
                        if ('0' == $this->aXMLResponseData['NCERROR']) { // only delete when no error occured (=transaction is not yet authorized)
                            $this->UpdateTransaction('DES');
                        }
                        $bContinue = $this->ExecuteExternalPaymentCall();
                    } else {
                        $bContinue = $this->UpdateTransaction('REN');
                    }
                } else {
                    // status is '1'=error or we dont have a status, something's gone wrong...
                    $bContinue = false;
                }
            } else {
                $bContinue = false;
            }
        }

        if ($bContinue && isset($this->aXMLResponseData['STATUS']) && 46 == $this->aXMLResponseData['STATUS']) {
            // we need to make a 3D-Secure authentication
            $s3DSecurePage = $this->GetConfigParameter('3dsecure-shop-system-page-name');
            if (!empty($s3DSecurePage)) {
                $this->Set3DSecureFormToSession($this->aXMLResponseData['HTML_ANSWER']);
                $oShop = ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
                $sTargetURL = $oShop->GetLinkToSystemPage($s3DSecurePage, null, true);
                $this->getRedirectService()->redirect($sTargetURL);
            }
        }

        return $bContinue;
    }

    /**
     * Checks wether the data in our transaction matches the current basket contents (total cost, currency) yet.
     *
     * @return bool
     */
    protected function TransactionMatchesCurrentBasketState()
    {
        $bIsMatching = true;
        $this->GetTransactionStatus();
        if (!isset($this->aXMLResponseData['CURRENCY']) || $this->aXMLResponseData['CURRENCY'] != $this->GetCurrencyIdentifier()) {
            $bIsMatching = false;
        }
        if ($bIsMatching) {
            $oBasket = TShopBasket::GetInstance();
            $sBasketAmount = number_format(round($oBasket->dCostTotal * 100) / 100, 2, '.', '');
            if (!isset($this->aXMLResponseData['AMOUNT']) || $this->aXMLResponseData['AMOUNT'] != $sBasketAmount) {
                $bIsMatching = false;
            }
        }

        return $bIsMatching;
    }

    /**
     * Update the current transaction (Commit order, delete transaction, ...).
     *
     * @param string $sMode - see direct link documentation for available modes
     *
     * @return bool
     */
    protected function UpdateTransaction($sMode = 'SAS')
    {
        $oBasket = TShopBasket::GetInstance();
        $aParameter = ['PSPID' => $this->GetConfigParameter('user_id'), // required
            'USERID' => $this->GetConfigParameter('api_user_id'), // required
            'PSWD' => $this->GetConfigParameter('pswd'), // required
            'AMOUNT' => round($oBasket->dCostTotal * 100), 'OPERATION' => $sMode, ];

        $aParameter = array_merge($aParameter, $this->GetOrderOrPayIdAuthenticationArray());

        $aParameter['SHASign'] = $this->BuildOutgoingHash($aParameter);

        $sExternalHandlerURL = $this->GetDirectMaintenanceURL().'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));
        TTools::WriteLogEntry('OGONE DirectLink: called url: '.$sExternalHandlerURL, 4, __FILE__, __LINE__);
        $sResult = file_get_contents($sExternalHandlerURL);
        TTools::WriteLogEntry('OGONE DirectLink: XML response from DirectLink call: '.$sResult, 1, __FILE__, __LINE__);

        if (false !== $sResult) {
            $this->ParseXMLResponse($sResult);
        }

        return $this->PaymentSuccess();
    }

    /**
     * the method is called when an external payment handler returns successfully.
     *
     * @return bool
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        $bPaymentTransmitOk = parent::PostProcessExternalPaymentHandlerHook();
        if ($bPaymentTransmitOk) {
            $oGlobal = TGlobal::instance();
            $aUserData = $oGlobal->GetUserData();
            $bPaymentTransmitOk = $this->GetTransactionStatus($aUserData['PAYID']);
        }

        return $bPaymentTransmitOk;
    }

    /**
     * Fetch status of the current transaction an write it to aXMLResponseData.
     *
     * @param string|null $sPayId
     *
     * @return bool
     */
    protected function GetTransactionStatus($sPayId = null)
    {
        $aParameter = ['PSPID' => $this->GetConfigParameter('user_id'), // required
            'USERID' => $this->GetConfigParameter('api_user_id'), // required
            'PSWD' => $this->GetConfigParameter('pswd'), // required
        ];

        if (null !== $sPayId) {
            $aParameter['PAYID'] = $sPayId;
        } else {
            $aParameter = array_merge($aParameter, $this->GetOrderOrPayIdAuthenticationArray());
        }

        $sExternalHandlerURL = $this->GetDirectQueryURL().'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));
        TTools::WriteLogEntry('OGONE DirectLink: called url: '.$sExternalHandlerURL, 4, __FILE__, __LINE__);
        $sResult = file_get_contents($sExternalHandlerURL);
        TTools::WriteLogEntry('OGONE DirectLink: XML response from DirectLink call: '.$sResult, 1, __FILE__, __LINE__);

        if (false !== $sResult) {
            $this->ParseXMLResponse($sResult);
        }

        return $this->PaymentSuccess();
    }

    /**
     * executes payment for order.
     *
     * @param string $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder);

        if ($bPaymentOk && $this->TransactionMatchesCurrentBasketState()) {
            $bPaymentOk = $this->UpdateTransaction('SAS'); // capture
        } else {
            $bPaymentOk = false;
        }

        return $bPaymentOk;
    }

    /**
     * The method is called from TShopBasket AFTER ExecutePayment was successfully executed.
     * The method is ALSO called, if the payment handler passed execution to an external service from within the ExecutePayment Method.
     *
     * @param string $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function PostExecutePaymentHook(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $aDirectLinkPaymentUserDataFields = self::GetDirectLinkResponsePaymentUserDataFields();

        foreach ($this->aXMLResponseData as $sKey => $sValue) {
            if (in_array(self::GetDirectLinkResponsePaymentUserDataFieldPrefix().$sKey, $aDirectLinkPaymentUserDataFields)) {
                $this->aPaymentUserData[self::GetDirectLinkResponsePaymentUserDataFieldPrefix().$sKey] = $sValue;
            }
        }

        return parent::PostExecutePaymentHook($oOrder); // mark payment method as completed
    }

    /**
     * Send payment call to external payment provider and check if payment was successfully done.
     *
     * @return bool
     */
    protected function ExecuteExternalPaymentCall()
    {
        $aParameter = $this->GetPaymentParameter();
        $sExternalHandlerURL = $this->GetPaymentURL().'?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));
        TTools::WriteLogEntry('OGONE DirectLink: called url: '.$sExternalHandlerURL, 4, __FILE__, __LINE__);
        $sResult = file_get_contents($sExternalHandlerURL);
        TTools::WriteLogEntry('OGONE DirectLink: XML response from DirectLink call: '.$sResult, 1, __FILE__, __LINE__);

        if (false !== $sResult) {
            $this->ParseXMLResponse($sResult);
        }

        return $this->PaymentSuccess();
    }

    /**
     * Write 3D-Secure form data send by OGONE to session, so it can be displayed on any page.
     *
     * @param string $s3DSecureForm
     *
     * @return void
     */
    protected function Set3DSecureFormToSession($s3DSecureForm)
    {
        $_SESSION['ogone3DSecureForm'] = $s3DSecureForm;
    }

    /**
     * See Set3DSecureFormToSession(), this is the getter.
     *
     * @static
     *
     * @return string
     */
    public static function Get3DSecureFormFromSession()
    {
        if (isset($_SESSION['ogone3DSecureForm'])) {
            return base64_decode($_SESSION['ogone3DSecureForm']);
        }

        return '';
    }

    /**
     * parse the xml response from the DirectLink API.
     *
     * @param string $sResult - xml response as string
     *
     * @return void
     */
    protected function ParseXMLResponse($sResult)
    {
        $oXMLResponse = new SimpleXMLElement($sResult);
        foreach ($oXMLResponse->attributes() as $sAttributeName => $sAttributeValue) {
            $sAttributeName = strtoupper($sAttributeName);
            $this->aXMLResponseData[$sAttributeName] = (string) $sAttributeValue;
        }
        if (isset($oXMLResponse->HTML_ANSWER)) {
            $this->aXMLResponseData['HTML_ANSWER'] = (string) $oXMLResponse->HTML_ANSWER;
        }
    }

    /**
     * validate the parsed xml response. handle error and / or status codes and return true to proceed or false to cancel order completion.
     *
     * @return bool
     */
    protected function PaymentSuccess()
    {
        if (isset($this->aXMLResponseData['STATUS'])) {
            switch ($this->aXMLResponseData['STATUS']) {
                case 46: // for 3D Secure - (waiting for identification)
                case 5:
                case 9:
                case 51:
                case 52:
                case 92:
                    return true;
                    break;
                case 0:
                case 2:
                default:
                    return false;
                    break;
            }
        } else {
            return false;
        }
    }

    /**
     * prefix for payment user data that will be saved to the order (used to avoid duplicates with data from alias management.
     *
     * @return string
     */
    public static function GetDirectLinkResponsePaymentUserDataFieldPrefix()
    {
        return 'directLinkResponse';
    }

    /**
     * return list of fields from the parsed response xml that will be saved to the order.
     *
     * @return array
     */
    public static function GetDirectLinkResponsePaymentUserDataFields()
    {
        return [self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'PAYID', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'STATUS', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'AMOUNT', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'CURRENCY', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'PM', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'BRAND', self::GetDirectLinkResponsePaymentUserDataFieldPrefix().'HTML_ANSWER'];
    }

    /**
     * get parameters for the payment execution call (creates a transaction with reserved mode).
     *
     * @return array
     */
    protected function GetPaymentParameter()
    {
        $oBasket = TShopBasket::GetInstance();
        $sCurrency = $this->GetCurrencyIdentifier();

        if (isset($this->aXMLResponseData['ORDERID'])) {
            if (isset($this->aPaymentUserData['OrderIncrease'])) {
                $this->aPaymentUserData['OrderIncrease'] = $this->aPaymentUserData['OrderIncrease'] + 1;
            } else {
                $this->aPaymentUserData['OrderIncrease'] = 1;
            }
            $sOrderId = $oBasket->sBasketIdentifier.'-'.$this->aPaymentUserData['OrderIncrease'];
        } else {
            $sOrderId = $oBasket->sBasketIdentifier;
        }
        $request = ServiceLocator::get('request_stack')->getCurrentRequest();
        $sUserIpAddress = $request->getClientIp();
        $aParameter = ['PSPID' => $this->GetConfigParameter('user_id'), // required
            'ORDERID' => $sOrderId, // required
            'USERID' => $this->GetConfigParameter('api_user_id'), // required
            'PSWD' => $this->GetConfigParameter('pswd'), // required
            'AMOUNT' => round($oBasket->dCostTotal * 100), // required - order value multiplied by 100 because we won't have any thousand or decimal separators
            'CURRENCY' => $sCurrency, // required
            'ALIAS' => $this->aPaymentUserData['ALIAS'], 'OPERATION' => 'RES', // required - use RES for reservation or SAL for direct booking (sell) (overrides ogone backend account setting)
            'REMOTE_ADDR' => $sUserIpAddress, 'RTIMEOUT' => 60, ];

        $aParameter = array_merge($aParameter, $this->Get3DSecureParameter());

        // generate the hash
        $aParameter['SHASign'] = $this->BuildOutgoingHash($aParameter);

        return $aParameter;
    }

    /**
     * get parameters for 3D Secure payment if enabled via config parameter.
     *
     * @return array
     */
    protected function Get3DSecureParameter()
    {
        $aParameter = [];
        if ('true' === $this->GetConfigParameter('3DS_ENABLED')) {
            $oActivePage = $this->getActivePageService()->getActivePage();
            $oGlobal = TGlobal::instance();
            $aSuccessCall = ['module_fnc' => [$oGlobal->GetExecutingModulePointer()->sModuleSpotName => 'PostProcessExternalPaymentHandlerHook']];

            $sSuccessURL = urldecode(str_replace('&amp;', '&', $oActivePage->GetRealURLPlain($aSuccessCall, true)));

            $aParameter = ['FLAG3D' => 'Y', 'WIN3DS' => $this->GetConfigParameter('3DS_WIN3DS'), 'ACCEPTURL' => $sSuccessURL, 'DECLINEURL' => $this->GetErrorURL('confirm'), 'EXCEPTIONURL' => $this->GetErrorURL('confirm'), 'HTTP_ACCEPT' => 'Accept: '.$_SERVER['HTTP_ACCEPT'], 'HTTP_USER_AGENT' => 'User-Agent: '.$_SERVER['HTTP_USER_AGENT'], 'LANGUAGE' => 'de_DE'];
        }

        return $aParameter;
    }

    /**
     * Get the direct query service URL - this is the endpoint to fetch data for
     * the current transaction.
     *
     * @return string|false
     */
    protected function GetDirectQueryURL()
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            $sPaymentURL = $this->GetConfigParameter('sOgoneDirectQueryURLLive');
        } else {
            $sPaymentURL = $this->GetConfigParameter('sOgoneDirectQueryURLTest');
        }

        return $sPaymentURL;
    }

    /**
     * Get the maintenance service URL - this is the endpoint to edit the
     * current transaction, to commit it for example.
     *
     * @return string|false
     */
    protected function GetDirectMaintenanceURL()
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            $sPaymentURL = $this->GetConfigParameter('sOgoneDirectMaintenanceURLLive');
        } else {
            $sPaymentURL = $this->GetConfigParameter('sOgoneDirectMaintenanceURLTest');
        }

        return $sPaymentURL;
    }

    /**
     * Return an array with PayId or OrderId, if one is set. Most API-Calls need at least
     * one of them to select the right transaction.
     *
     * @return array
     */
    protected function GetOrderOrPayIdAuthenticationArray()
    {
        $aAuthArray = [];
        if (isset($this->aXMLResponseData['PAYID']) && !empty($this->aXMLResponseData['PAYID'])) {
            $aAuthArray = ['PAYID' => $this->aXMLResponseData['PAYID']];
        } elseif (isset($this->aPaymentUserData['ORDERID']) && !empty($this->aPaymentUserData['ORDERID'])) {
            $aAuthArray = ['ORDERID' => $this->aPaymentUserData['ORDERID']];
        }

        return $aAuthArray;
    }

    private function getActivePageService(): ActivePageServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    private function getRedirectService(): ICmsCoreRedirect
    {
        return ServiceLocator::get('chameleon_system_core.redirect');
    }
}
