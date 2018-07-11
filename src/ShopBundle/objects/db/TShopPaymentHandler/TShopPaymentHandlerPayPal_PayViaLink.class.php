<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\UrlUtil;

/**
 * a simple paypal integration that does not execute the payment on order, but instead provides a payment
 * link/form that can be used by the user to pay for the order
 * if you want to accept partial payments, you need to set bAcceptPartialPayments to 1.
/**/
class TShopPaymentHandlerPayPal_PayViaLink extends TdbShopPaymentHandler
{
    const URL_IDENTIFIER_IPN = '_paypalipn_'; // instant payment notification URL identifier
    const LOG_FILE = 'paypal.log';

    /**
     * @param TdbShopOrder $oOrder
     * @param null         $dAmount
     * @param string       $sCurrency - you can pass the currency used as an optional parameter to the method. if the currency
     *                                package is installed and you do not pass the parameter, the method will use the currency
     *                                of the order. if the package ist not installed, we default to EUR
     *
     * @return string
     */
    public function GetPayPalPaymentLink($oOrder, $dAmount = null, $sCurrency = null)
    {
        if (is_null($dAmount)) {
            $dAmount = $oOrder->fieldValueTotal;
        }

        if (is_null($sCurrency)) {
            $oCurrency = null;
            if (method_exists($oOrder, 'GetFieldPkgShopCurrency')) {
                $oCurrency = $oOrder->GetFieldPkgShopCurrency();
            }
            $sCurrency = $this->GetCurrencyIdentifier($oCurrency);
        }
        $sLanguage = 'DE';
        $oLanguage = $oOrder->GetFieldCmsLanguage();
        if ($oLanguage) {
            $sLanguage = strtoupper($oLanguage->fieldIso6391);
        }

        $aParameter = array();
        $aParameter['cmd'] = '_ext-enter';
        $aParameter['redirect_cmd'] = '_xclick';
        $aParameter['business'] = $this->GetConfigParameter('business'); //$paypal_mail

        $aParameter['item_name'] = $oOrder->fieldOrdernumber;
        $aParameter['currency_code'] = $sCurrency;
        $aParameter['amount'] = $dAmount;
        $aParameter['notify_url'] = $this->GetInstantPaymentNotificationListenerURL($oOrder);
        $aParameter['lc'] = $sLanguage;
        $aParameter['address_override'] = '1';
        $aParameter['custom'] = $this->id.','.$oOrder->id;
        $aParameter['email'] = $oOrder->fieldUserEmail;
        $shop = $oOrder->GetFieldShop();
        $defaultCountryCode = 'DE';
        if (null !== $shop) {
            $defaultCountryCode = $shop->GetFieldDataCountry()->getIso3166CountryCode();
        }
        if ($oOrder->fieldAdrShippingUseBilling) {
            $aParameter['first_name'] = $oOrder->fieldAdrBillingFirstname;
            $aParameter['last_name'] = $oOrder->fieldAdrBillingLastname;
            $aParameter['address1'] = $oOrder->fieldAdrBillingStreet;
            $aParameter['zip'] = $oOrder->fieldAdrBillingPostalcode;
            $aParameter['city'] = $oOrder->fieldAdrBillingCity;
            $sCountry = $defaultCountryCode;
            $oCountry = $oOrder->GetFieldAdrBillingCountry();
            if ($oCountry) {
                $sCountry = $oCountry->getIso3166CountryCode();
            }
            $aParameter['country'] = $sCountry;
        } else {
            $aParameter['first_name'] = $oOrder->fieldAdrShippingFirstname;
            $aParameter['last_name'] = $oOrder->fieldAdrShippingLastname;
            $aParameter['address1'] = $oOrder->fieldAdrShippingStreet;
            $aParameter['zip'] = $oOrder->fieldAdrShippingPostalcode;
            $aParameter['city'] = $oOrder->fieldAdrShippingCity;
            $sCountry = $defaultCountryCode;
            $oCountry = $oOrder->GetFieldAdrShippingCountry();
            if ($oCountry) {
                $sCountry = $oCountry->getIso3166CountryCode();
            }
            $aParameter['country'] = $sCountry;
        }
        //$aParameter['forencoding'] = '&#261;'; //
        $aParameter['charset'] = 'UTF-8';

        $sBaseURL = $this->GetConfigParameter('url');

        return $sBaseURL.$this->getUrlUtilService()->getArrayAsUrl($aParameter, '?', '&');
    }

    /**
     * return the instant payment notification (IPN) URL for paypal.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return string
     */
    protected function GetInstantPaymentNotificationListenerURL($oOrder)
    {
        // change to absolute url
        $oShop = TdbShop::GetInstance();
        $sBasketPage = $oShop->GetLinkToSystemPage('checkout', null, true);
        $sURL = $sBasketPage.'/'.self::URL_IDENTIFIER_IPN;
        $sURL = str_replace('&amp;', '&', $sURL);

        return $sURL;
    }

    /**
     * @param TdbShopOrder $oOrder
     * @param array        $aURLParameter
     */
    public function ProcessIPNRequest($oOrder, $aURLParameter)
    {
        $sPayPalURL = $this->GetConfigParameter('url');
        $sPayPalURL = str_replace(array('https://', 'http://'), '', $sPayPalURL);
        $sDomain = substr($sPayPalURL, 0, strpos($sPayPalURL, '/'));
        $sPath = substr($sPayPalURL, strpos($sPayPalURL, '/'));
        // send validate message back to paypal
        $aVerifyData = TGlobal::instance()->GetUserData(null, array(), TCMSUserInput::FILTER_NONE);

        $sData = 'cmd=_notify-validate&'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aVerifyData));
        /** @var TTools $oTools */
        $oTools = ServiceLocator::get('chameleon_system_core.tools');
        $sResponse = $oTools::sendToHost($sDomain, 'POST', $sPath, $sData, false, 'application/x-www-form-urlencoded', true);
        if (0 != strcmp($sResponse, 'VERIFIED')) {
            TTools::WriteLogEntrySimple("PayPal IPN: unable to send notify-validate response. Domain:{$sDomain}\nPath:{$sPath}\nParameter: ".print_r($aURLParameter, true)."\ndata: {$sData}\nRESPONSE: ".$sResponse, 1, __FILE__, __LINE__, self::LOG_FILE);

            return false;
        }

        // check that txn_id has not been previously processed
        $sTxnId = (array_key_exists('txn_id', $aURLParameter)) ? ($aURLParameter['txn_id']) : ('');
        $oPaymentParameterList = $oOrder->GetFieldShopOrderPaymentMethodParameterList();
        $oPaymentParameterList->AddFilterString("`shop_order_payment_method_parameter`.`name` LIKE 'IPN%payment_status' OR `shop_order_payment_method_parameter`.`name` = 'PAYMENTINFO_0_PAYMENTSTATUS'");
        if ($oPaymentParameterList->Length() > 0) {
            while ($oPaymentParameter = $oPaymentParameterList->Next()) {
                if ('Completed' == $oPaymentParameter->fieldValue) {
                    TTools::WriteLogEntrySimple("PayPal IPN: the txn '{$sTxnId}' has been processed before and was set to completed".print_r($aURLParameter, true).' data: '.$sData, 1, __FILE__, __LINE__, self::LOG_FILE);

                    return false;
                }
            }
        }

        // save the data from paypal with the order
        // save IPN data to order
        foreach ($aURLParameter as $sParamName => $sParamKey) {
            $aInfo = array('shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s').': '.$sParamName, 'value' => $sParamKey);
            $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
            $oPaymentInfo->AllowEditByAll(true);
            $oPaymentInfo->Save();
        }

        // check the payment_status is Completed
        $bIsComplete = (array_key_exists('payment_status', $aURLParameter) && 0 == strcasecmp($aURLParameter['payment_status'], 'Completed'));

        if (false === $bIsComplete) {
            // some other response other than is paid - we log the info
            TTools::WriteLogEntrySimple("PayPal IPN: returned payment status '{$aURLParameter['payment_status']}'! ".$aURLParameter.' data: '.$sData, 1, __FILE__, __LINE__, self::LOG_FILE);

            return false;
        }

        $sPaymentCurrency = (array_key_exists('mc_currency', $aURLParameter)) ? ($aURLParameter['mc_currency']) : ('');

        $oCurrency = null;
        if (method_exists($oOrder, 'GetFieldPkgShopCurrency')) {
            $oCurrency = $oOrder->GetFieldPkgShopCurrency();
        }
        $sCurrency = $this->GetCurrencyIdentifier($oCurrency);

        if (0 != strcasecmp($sCurrency, $sPaymentCurrency)) {
            // invalid currency
            TTools::WriteLogEntrySimple('PayPal IPN: invalid currency in request '.print_r($aURLParameter, true), 1, __FILE__, __LINE__, self::LOG_FILE);
            $aInfo = array('shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s'), 'value' => 'invalid currency: '.$sPaymentCurrency);
            $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
            $oPaymentInfo->AllowEditByAll(true);
            $oPaymentInfo->Save();

            return true; // prevent paypal from sending the request again and again
        }

        // check that payment_amount/payment_currency are correct
        $bPaymentOk = false;
        $bPaymentCompleted = false;
        $dPaymentValue = (array_key_exists('mc_gross', $aURLParameter)) ? ($aURLParameter['mc_gross']) : (0);

        $bProcessed = true;
        // we need to store this as a diff var to prevent the check below from matching when a payment in a currency other then the orders currency comes in
        $aInfo = array('shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s').': PaymentInOrderCurrency', 'value' => $dPaymentValue);
        $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
        $oPaymentInfo->AllowEditByAll(true);
        $oPaymentInfo->Save();

        if ('1' != $this->GetConfigParameter('bAcceptPartialPayments')) {
            // no partial payments so make sure the amount/currency matches
            if ($oOrder->fieldValueTotal <= $dPaymentValue) {
                $bPaymentOk = true;
                $bPaymentCompleted = true;
            } else {
                TTools::WriteLogEntrySimple("PayPal IPN: invalid amount paid: required = {$oOrder->fieldValueTotal}, paid = {$dPaymentValue}. Rawdata: ".print_r($aURLParameter, true), 1, __FILE__, __LINE__, self::LOG_FILE);
            }
        } else {
            $bPaymentOk = true;
            if ($oOrder->fieldValueTotal <= $dPaymentValue) {
                $bPaymentCompleted = true;
            } else {
                // there may already be payments that in sum complete the payment...
                $oPaymentParameterList = $oOrder->GetFieldShopOrderPaymentMethodParameterList();
                $oPaymentParameterList->AddFilterString("`shop_order_payment_method_parameter`.`name` LIKE 'IPN %PaymentInOrderCurrency'");
                $dTotalPaid = 0;
                while ($oParam = $oPaymentParameterList->Next()) {
                    $dTotalPaid = $dTotalPaid + (float) $oParam->fieldValue;
                }
                if ($oOrder->fieldValueTotal == $dTotalPaid) {
                    $bPaymentCompleted = true;
                }
            }
        }

        if (false == $bPaymentOk) {
            TTools::WriteLogEntrySimple('PayPal IPN: payment invalid '.print_r($aURLParameter, true), 1, __FILE__, __LINE__, self::LOG_FILE);
        } else {
            if ($bPaymentCompleted) {
                $oOrder->SetStatusPaid(true);
            }
            // payment is ok - we need to call the "IPN Payment Received hook"
            $this->IPNPaymentReceivedHook($oOrder, $dPaymentValue);
        }

        return $bProcessed;
    }

    /**
     * method is called after an IPN (instant payment notification) for an order about a payment has been received and validated)
     * if the payment was enough to pay for the complete order, the order has already been marked as paid.
     * only payments in the orders currency are accepted - so you can assume the currency of the payment to be ok.
     *
     * @param TdbShopOrder $oOrder
     * @param float        $dPaymentValue
     * @param string       $sPaymentCurrency
     */
    protected function IPNPaymentReceivedHook($oOrder, $dPaymentValue)
    {
    }

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerPayPal_PayViaLink';
    }

    /** This function will take NVPString and convert it to an Associative Array and it will decode the response.
     * It is usefull to search for a particular key and displaying arrays.
     *
     * @nvpstr is NVPString.
     * @nvpArray is Associative Array.
     */
    public function ExtractPayPalNVPResponse($nvpstr)
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
     * return a config parameter for the payment handler.
     *
     * @param string $sParameterName - the system name of the handler
     *
     * @return string
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
     * @return UrlUtil
     */
    private function getUrlUtilService()
    {
        return ServiceLocator::get(
            'chameleon_system_core.util.url'
        );
    }
}
