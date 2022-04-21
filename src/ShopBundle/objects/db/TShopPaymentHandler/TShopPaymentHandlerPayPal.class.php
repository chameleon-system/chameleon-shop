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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class TShopPaymentHandlerPayPal extends TShopPaymentHandlerPayPal_PayViaLink
{
    const URL_IDENTIFIER = '_paypalapi_';

    /**
     * Version: this is the API version in the request.
     * It is a mandatory parameter for each API request.
     */
    const PAYPAL_API_VERSION = '84.0';

    protected $sPayPalToken = null;
    protected $aCheckoutDetails = null;

    /**
     * {@inheritdoc}
     */
    protected function GetViewPath()
    {
        return self::VIEW_PATH.'/TShopPaymentHandlerPayPal';
    }

    /**
     * {@inheritdoc}
     */
    public function PostSelectPaymentHook($sMessageConsumer)
    {
        $bContinue = parent::PostSelectPaymentHook($sMessageConsumer);
        if ($bContinue) {
            $bContinue = $this->CallPayPalExpressCheckout($sMessageConsumer);
        }

        return $bContinue;
    }

    /**
     * return the URL to the IFrame we call in order for paypal to collect the data.
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    protected function CallPayPalExpressCheckout($sMessageConsumer)
    {
        $oGlobal = TGlobal::instance();

        $sReturnURLBase = $this->getActivePageService()->getActivePage()->GetRealURLPlain(array(), true);
        if ('.html' == substr($sReturnURLBase, -5)) {
            $sReturnURLBase = substr($sReturnURLBase, 0, -5);
        }
        if ('/' != substr($sReturnURLBase, -1)) {
            $sReturnURLBase .= '/';
        }
        $sSuccessURL = $sReturnURLBase.self::URL_IDENTIFIER.'success/spot_'.$oGlobal->GetExecutingModulePointer()->sModuleSpotName;
        $sCancelURL = $sReturnURLBase.self::URL_IDENTIFIER.'cancel/spot_'.$oGlobal->GetExecutingModulePointer()->sModuleSpotName;

        $oBasket = TShopBasket::GetInstance();

        $aParameter = array();
        $aParameter['PAYMENTREQUEST_0_AMT'] = number_format($oBasket->dCostTotal, 2); // the total value to charge (use US-Format (1000.00)
        $aParameter['PAYMENTREQUEST_0_CURRENCYCODE'] = $this->GetCurrencyIdentifier();
        $aParameter['RETURNURL'] = $sSuccessURL; // go to the checkout complete page
        $aParameter['CANCELURL'] = $sCancelURL; //urldecode(str_replace('&amp;','&',$oActivePage->GetRealURL(array('paypalreturn'=>'1'),$aExcludes,true))); // return to the cancel page

        // styling
        $aParameter['HDRIMG'] = ''; // - : specify an image to appear at the top left of the payment page
        $aParameter['HDRBORDERCOLOR'] = ''; // - : set the border color around the header of the payment page
        $aParameter['HDRBACKCOLOR'] = ''; // - : set the background color for the background of the header of the payment page
        $aParameter['PAYFLOWCOLOR'] = ''; // - : set the background color for the payment page

        //$aParameter['notify_url'] = $this->GetInstantPaymentNotificationListenerURL(); // instant payment notification url

        $logger = $this->getPaypalLogger();

        $logger->info('Parameters sent to the PayPal API', $aParameter);
        $aResponse = $this->ExecutePayPalCall('SetExpressCheckout', $aParameter);

        $sSuccess = false;
        // store relevant data in session - and redirect to paypal
        if (array_key_exists('ACK', $aResponse)) {
            $sAckResponse = strtoupper($aResponse['ACK']);
            if ('SUCCESS' == $sAckResponse) {
                $this->sPayPalToken = urldecode($aResponse['TOKEN']);
                $payPalURL = $this->GetConfigParameter('url').'?cmd=_express-checkout&token='.$this->sPayPalToken;
                $this->getRedirect()->redirect($payPalURL);
                $sSuccess = true;
            }
        }
        if (!$sSuccess) {
            $sResponse = self::GetPayPalErrorMessage($aResponse);
            $logger->critical('PayPal payment could not be initiated.', array($sResponse));

            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => 'Error Number: '.$aResponse['L_ERRORCODE0']));
        }

        return $sSuccess;
    }

    /**
     * renders a paypal response error message.
     *
     * @param $aMessageData
     *
     * @return string
     */
    protected static function GetPayPalErrorMessage($aMessageData)
    {
        $aMsg = array();
        if (array_key_exists('curl_error_no', $aMessageData)) {
            $aMsg[] = 'Error Number: '.$aMessageData['curl_error_no'];
            $aMsg[] = 'Error Message: '.$aMessageData['curl_error_msg'];
        } elseif (count($aMessageData) > 0) {
            $aMsg[] = 'Ack: '.$aMessageData['ACK'];
            $aMsg[] = 'Correlation ID: '.$aMessageData['CORRELATIONID'];
            $aMsg[] = 'Version: '.$aMessageData['VERSION'];
            $count = 0;
            while (isset($aMessageData['L_SHORTMESSAGE'.$count])) {
                $aMsg[] = 'Error Number: '.$aMessageData['L_ERRORCODE'.$count];
                $aMsg[] = 'Short Message: '.$aMessageData['L_SHORTMESSAGE'.$count];
                $aMsg[] = 'Long Message: '.$aMessageData['L_LONGMESSAGE'.$count];
                $count = $count + 1;
            }
        } else {
            $aMsg[] = 'Problem communicating with PayPal. See log for details.';
        }
        $sMsg = implode("\n", $aMsg);
        $sMsg = nl2br($sMsg);

        return $sMsg;
    }

    /**
     * the method is called when an external payment handler returns successfully.
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        $bPaymentTransmitOk = parent::PostProcessExternalPaymentHandlerHook();
        if ($bPaymentTransmitOk) {
            $oGlobal = TGlobal::instance();
            $aUserData = $oGlobal->GetUserData();
            $sToken = $oGlobal->GetUserData('token');
            if (empty($sToken) && !is_null($this->sPayPalToken)) {
                $sToken = $this->sPayPalToken;
            } // recover token from session if not return from paypal
            if (!empty($sToken)) { // ignore request without a token (paypal would sometimes generate 2 reqeusts via browser redirect - one with and one without payload. we need to ignore the one without)
                $this->sPayPalToken = $sToken;
                // now fetch the details
                $this->aCheckoutDetails = $this->ExecutePayPalCall('GetExpressCheckoutDetails', array('TOKEN' => $this->sPayPalToken));
            }
        }

        return $bPaymentTransmitOk;
    }

    /**
     * executes payment for order - in this case, we commit the paypal payment.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder);

        $oCurrency = null;
        if (method_exists($oOrder, 'GetFieldPkgShopCurrency')) {
            $oCurrency = $oOrder->GetFieldPkgShopCurrency();
        }
        $sCurrency = $this->GetCurrencyIdentifier($oCurrency);
        $request = $this->getCurrentRequest();
        $aCommand = array(
            'TOKEN' => $this->sPayPalToken,
            'PAYERID' => $this->aCheckoutDetails['PAYERID'],
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_AMT' => round($oOrder->fieldValueTotal, 2),
            'PAYMENTREQUEST_0_CURRENCYCODE' => $sCurrency,
            'IPADDRESS' => null === $request ? '' : $request->getClientIp(),
            'PAYMENTREQUEST_0_INVNUM' => $this->GetOrderNumber($oOrder),
            'PAYMENTREQUEST_0_CUSTOM' => $this->id.','.$oOrder->id,
        );
        $sIPNURL = $this->GetInstantPaymentNotificationListenerURL($oOrder);
        if (!empty($sIPNURL)) {
            $aCommand['PAYMENTREQUEST_0_NOTIFYURL'] = $sIPNURL;
        }

        $aAnswer = $this->ExecutePayPalCall('DoExpressCheckoutPayment', $aCommand);
        $ack = strtoupper($aAnswer['ACK']);
        if ('SUCCESS' == $ack) {
            $bPaymentOk = true;
            // add the response data to the order
            foreach ($aAnswer as $sKey => $sVal) {
                if (!array_key_exists($sKey, $this->aPaymentUserData)) {
                    $this->aPaymentUserData[$sKey] = $sVal;
                }
            }
            //        $this->SaveUserPaymentDataToOrder($oOrder->id);

            if (array_key_exists('PAYMENTINFO_0_PAYMENTSTATUS', $aAnswer) && 'COMPLETED' == strtoupper($aAnswer['PAYMENTINFO_0_PAYMENTSTATUS'])) {
                $oOrder->SetStatusPaid();
            }
        } else {
            $bPaymentOk = false;
        }

        if (!$bPaymentOk) {
            // error!
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => self::GetPayPalErrorMessage($aAnswer)));
            $logContext = array(
                'Command' => $aCommand,
                'PayPalAnswer' => $aAnswer,
            );
            $this->getPaypalLogger()->critical('PayPal payment could not be executed for order id '.$oOrder->id, $logContext);
        }

        return $bPaymentOk;
    }

    /**
     * Overwrite this if you need to add prefix to order number because you have more than one shop with equal order numbers.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return mixed
     */
    protected function GetOrderNumber(TdbShopOrder $oOrder)
    {
        return $oOrder->fieldOrdernumber;
    }

    // =============================================================================================
    /**
     * The following methods have been adopted from the samples provided by paypal.
     */

    /**
     * Function to perform the API call to PayPal using API signature.
     *
     * @param string $methodName - is name of API  method
     * @param array  $nvp        - name value pairs - see PayPal documentation for details
     *
     * @return array - returns an associative array containing the response from the server
     */
    public function ExecutePayPalCall($methodName, $nvp)
    {
        $this->getPaypalLogger()->info(sprintf('PayPal-Request: %s with %s', $methodName, print_r($nvp, true)));

        $apiEndpoint = $this->GetConfigParameter('urlApiEndpoint');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //NVPRequest for submitting to server
        $parameters = array(
            'METHOD' => $methodName,
            'VERSION' => self::PAYPAL_API_VERSION,
            'PWD' => $this->GetConfigParameter('apiPassword'),
            'USER' => $this->GetConfigParameter('apiUserName'),
            'SIGNATURE' => $this->GetConfigParameter('apiSignatur'),
        );
        foreach ($nvp as $sKey => $sVal) {
            if (!array_key_exists($sKey, $parameters)) {
                $parameters[$sKey] = $sVal;
            }
        }

        $nvpreq = str_replace('&amp;', '&', TTools::GetArrayAsURL($parameters));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
        $response = curl_exec($ch);

        //converting NVPResponse to an associative array
        $nvpResArray = $this->ExtractPayPalNVPResponse($response);

        if (curl_errno($ch)) {
            $logContext = array(
                'url' => $apiEndpoint,
                'params' => $parameters,
            );
            $this->getPaypalLogger()->critical('PayPal curl error: '.curl_error($ch).' ('.curl_errno($ch).')', $logContext);
        } else {
            curl_close($ch);
        }

        return $nvpResArray;
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
        if (is_array($this->aPaymentUserData)) {
            if (array_key_exists('TRANSACTIONID', $this->aPaymentUserData)) {
                $sIdent = $this->aPaymentUserData['TRANSACTIONID'];
            } elseif (array_key_exists('PAYMENTINFO_0_TRANSACTIONID', $this->aPaymentUserData)) {
                $sIdent = $this->aPaymentUserData['PAYMENTINFO_0_TRANSACTIONID'];
            }
        }

        return $sIdent;
    }

    /**
     * return the currency identifier for the currency we pay in.
     *
     * @param $oPkgShopCurrency TdbPkgShopCurrency
     *
     * @return string
     */
    protected function GetCurrencyIdentifier($oPkgShopCurrency = null)
    {
        $sCurrencyCode = strtoupper(parent::GetCurrencyIdentifier($oPkgShopCurrency));
        $aAllowedCodes = array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HUF', 'JPY', 'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD');
        if (!in_array($sCurrencyCode, $aAllowedCodes)) {
            $sCurrencyCode = '';
        }

        return $sCurrencyCode;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    /**
     * @return IPkgCmsCoreLog
     *
     * @deprecated - since 6.3.0 - use getPaypalLogger() instead
     */
    protected function getLogger()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('cmsPkgCore.logChannel.standard');
    }

    private function getPaypalLogger(): LoggerInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
