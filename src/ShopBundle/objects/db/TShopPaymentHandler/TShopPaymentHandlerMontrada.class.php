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

class TShopPaymentHandlerMontrada extends TdbShopPaymentHandler
{

    /** @var string|null */
    protected $sMontradaTransactionId = null;

    /** @var array<string, mixed>|null */
    protected $aCheckoutDetails = null;

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerMontrada';
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
            $bContinue = $this->CallMontradaFormService($sMessageConsumer);
        }

        return $bContinue;
    }

    /**
     * return timestamp in a montrada usable format.
     *
     * @return string
     */
    public static function GetMontradaTimestamp()
    {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $sDate = date('YmdHis');
        date_default_timezone_set($defaultTimezone);

        return $sDate; // yyyymmddhhmmss
    }

    /**
     * calculate request hash.
     *
     * @param array $aInput
     *
     * @return string
     */
    protected function GetMontradaRequstHash($aInput)
    {
        $sDelimiter = '-';

        $sString = $this->GetConfigParameter('secret').$sDelimiter;
        $sString .= $this->GetConfigParameter('merchid').$sDelimiter;
        $sString .= $aInput['orderid'].$sDelimiter;
        $sString .= $this->GetConfigParameter('payments').$sDelimiter;
        $sString .= $aInput['amount'].$sDelimiter;
        $sString .= $aInput['currency'].$sDelimiter;
        $sString .= $aInput['command'].$sDelimiter;
        $sString .= $aInput['timestamp'];

        return hash('sha256', $sString);
    }

    /**
     * calculate response hash.
     *
     * @param array $aInput
     *
     * @return string
     */
    protected function GetMontradaResponseHash($aInput)
    {
        $sDelimiter = '-';

        $sString = $this->GetConfigParameter('secret').$sDelimiter;
        $sString .= $this->GetConfigParameter('merchid').$sDelimiter;
        $sString .= $aInput['orderid'].$sDelimiter;
        $sString .= $aInput['amount'].$sDelimiter;
        $sString .= $aInput['currency'].$sDelimiter;
        $sString .= $aInput['result'].$sDelimiter;
        if (array_key_exists('trefnum', $aInput) && !empty($aInput['trefnum'])) {
            $sString .= $aInput['trefnum'].$sDelimiter;
        }
        $sString .= $aInput['timestamp'];

        return hash('sha256', $sString);
    }

    /**
     * return the URL to the montrada form service.
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    protected function CallMontradaFormService($sMessageConsumer)
    {
        $oBasket = TShopBasket::GetInstance();
        $oBasket->CommitCopyToDatabase(true);

        $oGlobal = TGlobal::instance();

        $oLang = self::getLanguageService()->getActiveLanguage();
        $timeStamp = self::GetMontradaTimestamp();

        $aParameter = array();
        $aParameter['merchid'] = $this->GetConfigParameter('merchid'); //  = Formularservice-Händler-ID
        $aParameter['lang'] = $oLang->fieldIso6391; // lang = (de/en)
        $aParameter['command'] = 'preauthorization'; // command = preauthorization

        $aParameter['orderid'] = $oBasket->sBasketIdentifier; // orderid = ? (24 char - )
        $aParameter['amount'] = round($oBasket->dCostTotal * 100); // amount (total in cent)
        $aParameter['currency'] = 'EUR'; // currency = EUR
        $aParameter['payments'] = $this->GetConfigParameter('payments'); //  = cc/dd/cc,dd (cc = kreditkarte, dd = lastschrift)
        $aParameter['timestamp'] = $timeStamp; // timestamp = Zeitstempel der Warenkorberstellung; Zeitzone UTC; Format: yyyyMMddHHmmss

        $aParameter['psphash'] = $this->GetMontradaRequstHash($aParameter); // psphash=SHA-256 Hashwert zur Sicherung der Übergabeparameter.

        // add basket design
        $oLocal = &TCMSLocal::GetActive();
        $aParameter['h.1'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_h1');
        $aParameter['h.2'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_h2');
        $oBasketArticles = $oBasket->GetBasketContents();
        $oBasketArticles->GoToStart();
        $aArticleNames = array();
        while ($oBasketArticle = $oBasketArticles->Next()) {
            $aArticleNames[] = $oBasketArticle->dAmount.' x '.$oBasketArticle->fieldName;
        }
        $oBasketArticles->GoToStart();

        $iIndex = 0;
        $aParameter['w.'.$iIndex.'.1'] = implode('; ', $aArticleNames);
        $aParameter['w.'.$iIndex.'.2'] = $oLocal->FormatNumber($oBasket->dCostArticlesTotalAfterDiscounts).' EUR';
        ++$iIndex;

        // add shipping & payment costs
        if ($oBasket->dCostShipping > 0) {
            $aParameter['w.'.$iIndex.'.1'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_shipping');
            $aParameter['w.'.$iIndex.'.2'] = $oLocal->FormatNumber($oBasket->dCostShipping, 2).' EUR';
            ++$iIndex;
        }
        $oLocal = &TCMSLocal::GetActive();
        if ($oBasket->dCostPaymentMethodSurcharge > 0) {
            $aParameter['w.'.$iIndex.'.1'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_payment_surcharge');
            $aParameter['w.'.$iIndex.'.2'] = $oLocal->FormatNumber($oBasket->dCostPaymentMethodSurcharge, 2).' EUR';
            ++$iIndex;
        }

        // remove vouchers
        if ($oBasket->dCostVouchers > 0) {
            $aParameter['w.'.$iIndex.'.1'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_voucher');
            $aParameter['w.'.$iIndex.'.2'] = '-'.$oLocal->FormatNumber($oBasket->dCostVouchers, 2).' EUR';
            ++$iIndex;
        }
        $aParameter['w.'.$iIndex.'.1'] = TGlobal::Translate('chameleon_system_shop.payment_montrada.payment_form_help');
        $aParameter['w.'.$iIndex.'.2'] = '';
        ++$iIndex;

        $aSuccessCall = array('module_fnc' => array($oGlobal->GetExecutingModulePointer()->sModuleSpotName => 'PostProcessExternalPaymentHandlerHook'));
        $aExcludes = array_keys($aParameter);
        $aExcludes[] = 'aShipping';
        $aExcludes[] = 'orderstepmethod';
        $aExcludes[] = 'aPayment';

        $sSuccessUrl = str_replace('&amp;', '&', $this->getActivePageService()->getLinkToActivePageRelative($aSuccessCall, $aExcludes));

        $aParameter['url'] = $sSuccessUrl;

        return $this->GetAnswerFromServer($aParameter, $sMessageConsumer);
    }

    /**
     * Send Call per POST to montrada. Response is a moved permanently header which we pass through.
     *
     * @param array $aData - the post data to be send to montrada
     * @param string $sMessageConsumer
     *
     * @return bool
     */
    public function GetAnswerFromServer($aData, $sMessageConsumer)
    {
        $bSuccess = false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->GetConfigParameter('url'));
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);

        $aParameter = array();
        foreach ($aData as $sKey => $sVal) {
            if (!array_key_exists($sKey, $aParameter)) {
                $aParameter[$sKey] = $sVal;
            }
        }
        $sData = str_replace('&amp;', '&', TTools::GetArrayAsURL($aParameter));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sData);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            TTools::WriteLogEntry('Call Montrada Page: '.print_r($aData, true).' - '.curl_errno($ch).' - '.curl_error($ch), 1, __FILE__, __LINE__);
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_montrada.error_request'))));

            return $bSuccess;
        } else {
            curl_close($ch);
        }
        $aParts = explode("\n", $response);
        foreach ($aParts as $sHeader) {
            header($sHeader);
        }

        return $bSuccess;
    }

    /**
     * return a config parameter for the payment handler.
     *
     * @param string $sParameterName - the system name of the handler
     *
     * @return string|false
     */
    public function GetConfigParameter($sParameterName)
    {
        static $bUseSandbox = null;
        if (is_null($bUseSandbox)) {
            $bUseSandbox = (IPkgShopOrderPaymentConfig::ENVIRONMENT_SANDBOX === $this->getEnvironment());
        }
        if ($bUseSandbox) {
            $sParameterName .= 'Sandbox';
        }

        return parent::GetConfigParameter($sParameterName);
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

            // check if return data is ok...
            $aReqFields = array('merchid', 'orderid', 'amount', 'currency', 'result', 'timestamp', 'psphash');
            foreach ($aReqFields as $sFieldName) {
                $bPaymentTransmitOk = ($bPaymentTransmitOk && array_key_exists($sFieldName, $aUserData));
            }
            if ($bPaymentTransmitOk && 'success' == $aUserData['result'] && (0 == strcmp($this->GetMontradaResponseHash($aUserData), $aUserData['psphash']))) {
                // all good... we can continue to our order confirm page
                // need to fetch the transaction id
                $aResult = $this->ExecuteRequestCall('txnresult', array('orderid' => $aUserData['orderid']));
                $this->sMontradaTransactionId = $aResult['trefnum'];
                $this->aCheckoutDetails = $aUserData;
            }
        }

        return $bPaymentTransmitOk;
    }

    /**
     * executes payment for order - in this case, we commit the montrada payment.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder &$oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder);

        $aCommand = array('trefnum' => $this->sMontradaTransactionId, 'amount' => round($oOrder->fieldValueTotal * 100));
        $aAnswer = $this->ExecuteRequestCall('capture', $aCommand);
        if (array_key_exists('rc', $aAnswer) && '000' == $aAnswer['rc']) {
            $bPaymentOk = true;
            // add the response data to the order
            foreach ($aAnswer as $sKey => $sVal) {
                if (!array_key_exists($sKey, $this->aPaymentUserData)) {
                    $this->aPaymentUserData[$sKey] = $sVal;
                }
            }
            $this->SaveUserPaymentDataToOrder($oOrder->id);
            $oOrder->SetStatusPaid();
        } else {
            $bPaymentOk = false;
        }

        if (!$bPaymentOk) {
            // error!
            $sMsg = 'unbekannt';
            if (array_key_exists('rmsg', $aAnswer)) {
                $sMsg = $aAnswer['rmsg'];
            }
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => $sMsg));
        }

        return $bPaymentOk;
    }

    // =============================================================================================
    /**
     * The following methods have been adopted from the samples provided by paypal.
     */

    /**
     * Function to perform the API call to PayPal using API signature.
     *
     * @param string $methodName - is name of API  method
     * @param array<string, mixed> $aRequestString - nvpStr is the nvp (name value pair) string -  see paypal documentation for details
     *
     * @return array<string, mixed> - returns an associtive array containing the response from the server
     */
    public function ExecuteRequestCall($methodName, $aRequestString)
    {
        //setting the curl parameters.
        $ch = curl_init();

        $password = $this->GetConfigParameter('pwd');
        $username = $this->GetConfigParameter('userName');
        $url = $this->GetConfigParameter('sPoshCommandUrl');
        $aRequestString['command'] = $methodName;
        $sRequestString = TTools::GetArrayAsURL($aRequestString);
        $sRequestString = str_replace('&amp;', '&', $sRequestString);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sRequestString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
        //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php
        // curl_setopt ($ch, CURLOPT_PROXY, PROXY_HOST.":".PROXY_PORT);

        //getting response from server
        $response = curl_exec($ch);
        $aResponse = $this->ExtractResponse($response);

        $sError = curl_error($ch);
        $sErrorNr = curl_errno($ch);

        if ($sErrorNr) {
            // moving to display page to display curl errors
            //      	  $_SESSION['curl_error_no']=curl_errno($ch) ;
            //      	  $_SESSION['curl_error_msg']=curl_error($ch);
            //      	  $location = "APIError.php";
            //      	  header("Location: $location");
        } else {
            //closing the curl
            curl_close($ch);
        }

        return $aResponse;
    }

    /**
     * This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     *
     * @nvpstr is NVPString.
     *
     * @nvpArray is Associative Array.
     *
     * @param bool|string $nvpstr
     *
     * @return array<string, string>
     */
    public function ExtractResponse($nvpstr)
    {
        $intial = 0;
        $nvpArray = array();

        while (strlen($nvpstr)) {
            //postion of Key
            $keypos = strpos($nvpstr, '=');
            //position of value
            $valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval = substr($nvpstr, $intial, $keypos);
            $valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] = urldecode($valval);
            $nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
        }

        return $nvpArray;
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
        if (is_array($this->aPaymentUserData) && array_key_exists('trefnum', $this->aPaymentUserData)) {
            $sIdent = $this->aPaymentUserData['trefnum'];
        }

        return $sIdent;
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
