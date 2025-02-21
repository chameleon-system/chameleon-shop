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
use ChameleonSystem\ShopBundle\Payment\PaymentConfig\Interfaces\ShopPaymentConfigLoaderInterface;
use ChameleonSystem\ShopBundle\PkgShopPaymentTransaction\PayPalTransactionHandler;
use esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface;
use Psr\Log\LoggerInterface;

/**
 * a simple paypal integration that does not execute the payment on order, but instead provides a payment
 * link/form that can be used by the user to pay for the order
 * if you want to accept partial payments, you need to set bAcceptPartialPayments to 1.
 * /**/
class TShopPaymentHandlerPayPal_PayViaLink extends TdbShopPaymentHandler implements PaymentHandlerWithTransactionSupportInterface
{
    public const PAYMENT_MODE_DELAYED = 'preauthorization';
    public const PARAM_PAYMENT_MODE = 'payment_mode';
    public const URL_IDENTIFIER_IPN = '_paypalipn_'; // instant payment notification URL identifier
    protected const PARAMETER_IS_PAYMENT_ON_SHIPMENT = 'isPaymentOnShipment';

    public function paymentTransactionHandlerFactory($portalId)
    {
        $config = $this->getShopPaymentConfigLoader()->loadFromPaymentHandlerId($this->id, $portalId);

        return new PayPalTransactionHandler($config);
    }

    /**
     * return true if capture on shipment is active.
     *
     * @return bool
     */
    public function isCaptureOnShipment()
    {
        if ($this->GetUserPaymentDataItem(self::PARAMETER_IS_PAYMENT_ON_SHIPMENT)) {
            return 1 === (int) $this->GetUserPaymentDataItem(self::PARAMETER_IS_PAYMENT_ON_SHIPMENT);
        }

        return self::PAYMENT_MODE_DELAYED === $this->GetConfigParameter(self::PARAM_PAYMENT_MODE);
    }

    public function SetPaymentUserData($aPaymentUserData)
    {
        if (false === array_key_exists(self::PARAMETER_IS_PAYMENT_ON_SHIPMENT, $aPaymentUserData)) {
            // fix current value if not set
            $isDelayedPayment = self::PAYMENT_MODE_DELAYED
                === $this->GetConfigParameter(self::PARAM_PAYMENT_MODE);

            $aPaymentUserData[self::PARAMETER_IS_PAYMENT_ON_SHIPMENT] = $isDelayedPayment ? '1' : '0';
        }

        parent::SetPaymentUserData($aPaymentUserData);
    }

    /**
     * @param TdbShopOrder $oOrder
     * @param null $dAmount
     * @param string $sCurrency - you can pass the currency used as an optional parameter to the method. if the currency
     *                          package is installed and you do not pass the parameter, the method will use the currency
     *                          of the order. if the package ist not installed, we default to EUR
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

        $aParameter = [];
        $aParameter['cmd'] = '_ext-enter';
        $aParameter['redirect_cmd'] = '_xclick';
        $aParameter['business'] = $this->GetConfigParameter('business'); // $paypal_mail

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
        $oShop = ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop();
        $sBasketPage = $oShop->GetLinkToSystemPage('checkout', null, true);
        $sURL = $sBasketPage.'/'.self::URL_IDENTIFIER_IPN;

        return str_replace('&amp;', '&', $sURL);
    }

    /**
     * @param TdbShopOrder $oOrder
     * @param array $aURLParameter
     *
     * @return bool
     */
    public function ProcessIPNRequest($oOrder, $aURLParameter)
    {
        $logger = $this->getPaypalLogger();

        $sPayPalURL = $this->GetConfigParameter('url');
        $sPayPalURL = str_replace(['https://', 'http://'], '', $sPayPalURL);
        $sDomain = substr($sPayPalURL, 0, strpos($sPayPalURL, '/'));
        $sPath = substr($sPayPalURL, strpos($sPayPalURL, '/'));
        // send validate message back to paypal
        $aVerifyData = ServiceLocator::get('chameleon_system_core.global')->GetUserData(null, [], TCMSUserInput::FILTER_NONE);

        $sData = 'cmd=_notify-validate&'.$this->getUrlUtilService()->getArrayAsUrl($aVerifyData, '', '&');
        $sResponse = $this->sendRequest($sDomain, $sPath, $sData);
        if (0 !== strcmp($sResponse, 'VERIFIED')) {
            $logger->error(
                'PayPal IPN: unable to send notify-validate response.',
                [
                    'order_id' => $oOrder->id,
                    'order_number' => $oOrder->fieldOrdernumber,
                    'domain' => $sDomain,
                    'path' => $sPath,
                    'url_parameters' => $aURLParameter,
                    'data' => $sData,
                    'response' => $sResponse,
                ]
            );

            return false;
        }

        // check that txn_id has not been previously processed
        $sTxnId = (array_key_exists('txn_id', $aURLParameter)) ? ($aURLParameter['txn_id']) : ('');
        $oPaymentParameterList = $oOrder->GetFieldShopOrderPaymentMethodParameterList();
        $oPaymentParameterList->AddFilterString("`shop_order_payment_method_parameter`.`name` LIKE 'IPN%payment_status' OR `shop_order_payment_method_parameter`.`name` = 'PAYMENTINFO_0_PAYMENTSTATUS'");
        if ($oPaymentParameterList->Length() > 0) {
            while ($oPaymentParameter = $oPaymentParameterList->Next()) {
                if ('Completed' === $oPaymentParameter->fieldValue) {
                    $logger->info(
                        'PayPal IPN: the txn has been processed before and was already set to completed.',
                        [
                            'order_id' => $oOrder->id,
                            'order_number' => $oOrder->fieldOrdernumber,
                            'txn_id' => $sTxnId,
                            'url_parameters' => $aURLParameter,
                            'data' => $sData,
                        ]
                    );

                    return false;
                }
            }
        }

        // save the data from paypal with the order
        // save IPN data to order
        foreach ($aURLParameter as $sParamName => $sParamKey) {
            $aInfo = ['shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s').': '.$sParamName, 'value' => $sParamKey];
            $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
            $oPaymentInfo->AllowEditByAll(true);
            $oPaymentInfo->Save();
        }

        // check the payment_status is Completed
        $bIsComplete = (array_key_exists('payment_status', $aURLParameter) && 0 === strcasecmp($aURLParameter['payment_status'], 'Completed'));

        if (false === $bIsComplete) {
            // some other response other than is paid - we log the info
            $logger->error(
                'PayPal IPN: returned payment status not recognized.',
                [
                    'order_id' => $oOrder->id,
                    'order_number' => $oOrder->fieldOrdernumber,
                    'url_parameters' => $aURLParameter,
                    'data' => $sData,
                ]
            );

            return false;
        }

        $sPaymentCurrency = (array_key_exists('mc_currency', $aURLParameter)) ? ($aURLParameter['mc_currency']) : ('');

        $oCurrency = null;
        if (method_exists($oOrder, 'GetFieldPkgShopCurrency')) {
            $oCurrency = $oOrder->GetFieldPkgShopCurrency();
        }
        $sCurrency = $this->GetCurrencyIdentifier($oCurrency);

        if (0 !== strcasecmp($sCurrency, $sPaymentCurrency)) {
            $logger->error(
                'PayPal IPN: invalid currency in request.',
                [
                    'order_id' => $oOrder->id,
                    'order_number' => $oOrder->fieldOrdernumber,
                    'url_parameters' => $aURLParameter,
                ]
            );
            $aInfo = ['shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s'), 'value' => 'invalid currency: '.$sPaymentCurrency];
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
        $aInfo = ['shop_order_id' => $oOrder->id, 'name' => 'IPN '.date('Y-m-d H:i:s').': PaymentInOrderCurrency', 'value' => $dPaymentValue];
        $oPaymentInfo = TdbShopOrderPaymentMethodParameter::GetNewInstance($aInfo);
        $oPaymentInfo->AllowEditByAll(true);
        $oPaymentInfo->Save();

        if ('1' !== $this->GetConfigParameter('bAcceptPartialPayments')) {
            // no partial payments so make sure the amount/currency matches
            if ($oOrder->fieldValueTotal <= $dPaymentValue) {
                $bPaymentOk = true;
                $bPaymentCompleted = true;
            } else {
                $logger->error(
                    'PayPal IPN: invalid amount paid.',
                    [
                        'order_id' => $oOrder->id,
                        'order_number' => $oOrder->fieldOrdernumber,
                        'amount_required' => $oOrder->fieldValueTotal,
                        'amount_payed' => $dPaymentValue,
                        'url_parameters' => $aURLParameter,
                    ]
                );
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
                    $dTotalPaid += (float) $oParam->fieldValue;
                }
                if ($oOrder->fieldValueTotal === $dTotalPaid) {
                    $bPaymentCompleted = true;
                }
            }
        }

        if (false === $bPaymentOk) {
            $logger->error(
                'PayPal IPN: payment invalid.',
                [
                    'order_id' => $oOrder->id,
                    'order_number' => $oOrder->fieldOrdernumber,
                    'url_parameters' => $aURLParameter,
                ]
            );
        } else {
            if ($bPaymentCompleted) {
                $oOrder->SetStatusPaid(true);
            }
            // payment is ok - we need to call the "IPN Payment Received hook"
            $this->IPNPaymentReceivedHook($oOrder, $dPaymentValue);
        }

        return true;
    }

    private function sendRequest(string $host, string $path, string $data): string
    {
        $data = str_replace('&amp;', '&', $data); // remove encoding

        $sendToHostHandler = new TPkgCmsCoreSendToHost();

        try {
            $sendToHostHandler
                ->setHost($host)
                ->setMethod('POST')
                ->setPath($path)
                ->setPayloadFromQueryString($data)
                ->setSendUserAgent(false)
                ->setContentType('application/x-www-form-urlencoded')
                ->setUseSSL(true)
                ->setUser(null)
                ->setPassword(null);

            return $sendToHostHandler->executeRequest();
        } catch (TPkgCmsException_Log $e) {
            return '';
        }
    }

    /**
     * method is called after an IPN (instant payment notification) for an order about a payment has been received and validated)
     * if the payment was enough to pay for the complete order, the order has already been marked as paid.
     * only payments in the orders currency are accepted - so you can assume the currency of the payment to be ok.
     *
     * @param TdbShopOrder $oOrder
     * @param float $dPaymentValue
     *
     * @return void
     */
    protected function IPNPaymentReceivedHook($oOrder, $dPaymentValue)
    {
    }

    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerPayPal_PayViaLink';
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
    public function ExtractPayPalNVPResponse($nvpstr)
    {
        $initial = 0;
        $nvpArray = [];

        while (strlen($nvpstr)) {
            // postion of Key
            $keyPosition = strpos($nvpstr, '=');
            // position of value
            $valuePosition = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);

            /* getting the Key and Value values and storing in a Associative Array */
            $keyValue = substr($nvpstr, $initial, $keyPosition);
            $valValue = substr($nvpstr, $keyPosition + 1, $valuePosition - $keyPosition - 1);
            // decoding the respose
            $nvpArray[urldecode($keyValue)] = urldecode($valValue);
            $nvpstr = substr($nvpstr, $valuePosition + 1, strlen($nvpstr));
        }

        return $nvpArray;
    }

    /**
     * return a config parameter for the payment handler.
     *
     * @param string $sParameterName - the system name of the handler
     *
     * @return string|false
     *
     * @throws ChameleonSystem\ShopBundle\Exception\ConfigurationException
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

    protected function getShopPaymentConfigLoader(): ShopPaymentConfigLoaderInterface
    {
        return ServiceLocator::get('chameleon_system_shop.payment.config_loader');
    }

    private function getUrlUtilService(): UrlUtil
    {
        return ServiceLocator::get('chameleon_system_core.util.url');
    }

    private function getPaypalLogger(): LoggerInterface
    {
        return ServiceLocator::get('monolog.logger.order');
    }
}
