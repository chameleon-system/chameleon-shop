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

/**
 * This is the base class for DataTrans Paymenthandler. If you want to add a new payment method.
/**/
class TShopPaymentHandlerDataTrans extends TdbShopPaymentHandler
{
    /*
    *  constant to identifies data trans notify message
    */
    const URL_IDENTIFIER_NOTIFY = 'datatranspostfeedback';

    /*
    * transaction type for settlement 05 debit , 06 credit
    */
    const PAYMENT_CREDIT_CARD_TRANS_TYPE_ID = '05';

    /**
     * Transaction id for DataTrans authorisation. Needed for settlement authorised transactions.
     *
     * @var bool/string
     */
    protected $sTransactionId = false;

    /**
     * Reference number for each authorisation. Was set after redirecting from DataTrans.
     * Reference number has to be a unique for each authorisation.
     * Reference number was build with crc32 from basket identifier__payment type identifier__count
     * Count was needed because the first to parts are not unique.
     *
     * @var bool/string
     */
    protected $sRefNo = false;

    /**
     * Count for reference number.
     * To Get the reference number unique we add this count at the end.
     *
     * @var int
     */
    protected $sRefNoCount = 0;
    /**
     * Was set after authorisation with the authorised amount.
     * Was used on create order to check if amount has changed.
     *
     * @var bool
     */
    protected $sAuthorisedAmount = false;

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
        $aViewVariables['PaymentHiddenInput'] = $this->GetPaymentParameter();
        $aViewVariables['PaymentHiddenInput']['sign'] = $this->GetSecurityHash($this->GetTestLiveModeParameter('sign'), $aViewVariables['PaymentHiddenInput']['merchantId'], $aViewVariables['PaymentHiddenInput']['amount'], $aViewVariables['PaymentHiddenInput']['currency'], $aViewVariables['PaymentHiddenInput']['refno']);
        $aViewVariables['aUserAddressData'] = $this->GetUserAddressDataParameter();
        $aViewVariables['aPossiblePaymentMethodCreditCardList'] = $this->GetPaymentTypeSpecificParameter();

        return $aViewVariables;
    }

    /**
     * Get array with all possible payment identifier.
     * Overwrite this if payment method has sub payment methods like Swiss PostFinance.
     *
     * @return array
     */
    protected function GetPaymentTypeSpecificParameter()
    {
        return array();
    }

    /**
     * Get user address data as array.
     *
     * @return array $aUserAddressData
     */
    protected function GetUserAddressDataParameter()
    {
        $aUserAddressData = array();
        $oActiveUser = TdbDataExtranetUser::GetInstance();
        if ($oActiveUser) {
            $oBillingAddress = $oActiveUser->GetBillingAddress();
            if ($oBillingAddress) {
                $aUserAddressData['uppCustomerDetails'] = 'yes';
                $aUserAddressData['uppCustomerFirstName'] = $oBillingAddress->fieldFirstname;
                $aUserAddressData['uppCustomerLastName'] = $oBillingAddress->fieldLastname;
                $aUserAddressData['uppCustomerStreet'] = $oBillingAddress->fieldStreet.' '.$oBillingAddress->fieldStreetnr;
                $aUserAddressData['uppCustomerZipCode'] = $oBillingAddress->fieldPostalcode;
                $aUserAddressData['uppCustomerCity'] = $oBillingAddress->fieldCity;
                $aUserAddressData['uppCustomerCountry'] = $this->GetBillingCountryISOCode($oBillingAddress);
                $aUserAddressData['uppCustomerPhone'] = $oBillingAddress->fieldTelefon;
                $aUserAddressData['uppCustomerFax'] = $oBillingAddress->fieldFax;
                $aUserAddressData['uppCustomerEmail'] = $oActiveUser->fieldName;
            }
        }

        return $aUserAddressData;
    }

    /**
     * Returns payment id. The Payment Id is a part of the unique reference number.
     * Overwrite this if you add a new DataTrans payment method to get different reference ids for each payment method.
     *
     * @return string
     */
    protected function GetRefNoPaymentId()
    {
        return '';
    }

    /**
     * Returns a new reference number for an authorisation (default).
     * Or returns active reference number for settlement ($bForAuthorisation = false).
     *
     *
     * @param bool $bForAuthorisation
     *
     * @return bool|string
     */
    protected function GetRefNoParameter($bForAuthorisation = true)
    {
        $oShopBasket = TShopBasket::GetInstance();
        if ($this->sRefNo) {
            if ($bForAuthorisation) {
                if ($this->sRefNo == crc32($oShopBasket->sBasketIdentifier.'__'.$this->GetRefNoPaymentId().'__'.$this->sRefNoCount)) {
                    ++$this->sRefNoCount;
                    $sRefNo = crc32($oShopBasket->sBasketIdentifier.'__'.$this->GetRefNoPaymentId().'__'.$this->sRefNoCount);
                } else {
                    $sRefNo = crc32($oShopBasket->sBasketIdentifier.'__'.$this->GetRefNoPaymentId().'__'.$this->sRefNoCount);
                }
            } else {
                $sRefNo = $this->sRefNo;
            }
        } else {
            $sRefNo = crc32($oShopBasket->sBasketIdentifier.'__'.$this->GetRefNoPaymentId().'__'.$this->sRefNoCount);
        }

        return $sRefNo;
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
     * Get hidden field parameter needed for payment.
     *
     * @return array
     */
    protected function GetPaymentParameter()
    {
        $aParameter = array();
        $sCurrency = $this->GetCurrencyIdentifier();
        $oLocal = &TCMSLocal::GetActive();
        $oShopBasket = TShopBasket::GetInstance();
        $aParameter['merchantId'] = $this->GetTestLiveModeParameter('merchantId');
        $aParameter['refno'] = $this->GetRefNoParameter();
        $aParameter['reqtype'] = $this->GetConfigParameter('reqtype');
        $aParameter['amount'] = str_replace(',', '.', $oLocal->FormatNumber($oShopBasket->dCostTotal)) * 100;
        $aParameter['currency'] = $sCurrency;
        $aParameter['hiddenMode'] = $this->GetConfigParameter('hiddenMode');
        $sRedirectULR = $this->GetResponseURL();
        $aParameter['successUrl'] = $sRedirectULR;
        $aParameter['errorUrl'] = $sRedirectULR;
        $aParameter['cancelUrl'] = $sRedirectULR;

        return $aParameter;
    }

    /**
     * method is called after the user selected his payment and submitted the payment page
     * return false if you want to send the user back to the payment selection page.
     *
     * Check response form authorisation call.
     *
     * @param string $sMessageConsumer - the name of the message handler that can display messages if an error occurs (assuming you return false)
     *
     * @return bool
     */
    public function PostSelectPaymentHook($sMessageConsumer)
    {
        $bContinue = parent::PostSelectPaymentHook($sMessageConsumer);
        $this->sTransactionId = false;
        if (true == $bContinue) {
            $bContinue = $this->CheckAuthorisationResponse();
        }

        return $bContinue;
    }

    /**
     * if request to DataTrans was not successfully create a error message.
     *
     * @param string $sMessageConsumer
     *
     * @return bool
     */
    protected function CheckAuthorisationResponse($sMessageConsumer = '')
    {
        $bResponseSuccessful = false;
        if (empty($sMessageConsumer)) {
            $sMessageConsumer = $this->GetMsgManagerName();
        }
        $sReturnMessage = $this->GetErrorCodesFromResponse();
        if (!empty($sReturnMessage)) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, $sReturnMessage);
        } else {
            $bResponseSuccessful = true;
        }

        return $bResponseSuccessful;
    }

    /**
     * Overwrite this to get message manager name for payment.
     *
     * @return string
     */
    public function GetMsgManagerName()
    {
        return '';
    }

    /**
     * Overwrite this to get payment type for payment.
     *
     * @return string
     */
    public function GetPaymentType()
    {
        return '';
    }

    /**
     * Get error message form DataTrans authorisation response.
     *
     * @return string $SReturnMessage
     */
    protected function GetErrorCodesFromResponse()
    {
        $sReturnMessage = false;
        $oGlobal = TGlobal::instance();
        $this->sRefNo = $oGlobal->GetUserData('refno');
        $sReturnState = $oGlobal->GetUserData('status');
        if ($this->bIsCorrectPaymentType()) {
            $oBasket = TShopBasket::GetInstance();
            if ($this->sRefNo == crc32($oBasket->sBasketIdentifier.'__'.$this->GetRefNoPaymentId().'__'.$this->sRefNoCount)) {
                if ('error' == $sReturnState) {
                    $sResponseHash = $this->GetSecurityHash($this->GetTestLiveModeParameter('sign'), $oGlobal->GetUserData('merchantId'), $oGlobal->GetUserData('amount'), $oGlobal->GetUserData('currency'), $oGlobal->GetUserData('refno'));
                    if ($sResponseHash == $oGlobal->GetUserData('sign')) {
                        $sReturnMessage = $oGlobal->GetUserData('errorDetail');
                        $sReturnMessage = $this->TransformDataTransMessages($oGlobal->GetUserData('errorCode'));
                    } else {
                        $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_SECURITY_CHECK_ERROR';
                    }
                } elseif ('cancel' == $sReturnState) {
                    $sResponseHash = $this->GetSecurityHash($this->GetTestLiveModeParameter('sign'), $oGlobal->GetUserData('merchantId'), $oGlobal->GetUserData('amount'), $oGlobal->GetUserData('currency'), $oGlobal->GetUserData('refno'));
                    if ($sResponseHash == $oGlobal->GetUserData('sign')) {
                        $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_PAYMENT_CANCEL';
                    } else {
                        $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_SECURITY_CHECK_ERROR';
                    }
                } else {
                    $sTransactionId = $oGlobal->GetUserData('uppTransactionId');
                    $sAuthorisedAmount = $oGlobal->GetUserData('amount');
                    $sResponseHash = $this->GetSecurityHash($this->GetTestLiveModeParameter('sign2'), $oGlobal->GetUserData('merchantId'), $oGlobal->GetUserData('amount'), $oGlobal->GetUserData('currency'), $oGlobal->GetUserData('uppTransactionId'));
                    if ($sResponseHash == $oGlobal->GetUserData('sign2')) {
                        if (strlen($sTransactionId) <= 0 || strlen($sAuthorisedAmount) <= 0) {
                            $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_MISSING_PARAMETER_IN_RESPONSE';
                            TTools::WriteLogEntrySimple('Payment DataTrans: Beim Autorisierungsresponse wurden benötigte Daten nicht zurückgegeben', 1, __FILE__, __LINE__);
                        } else {
                            $this->sTransactionId = $sTransactionId;
                            $this->sAuthorisedAmount = $sAuthorisedAmount;
                        }
                    } else {
                        $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_SECURITY_CHECK_ERROR';
                    }
                }
            } else {
                $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_REFERENCE_NUMER_NOT_VALID';
            }
        } else {
            $sReturnMessage = 'ERROR_PAYMENT_DATA_TRANS_PAYMEN_SELECTION_NOT_VALID';
        }

        return $sReturnMessage;
    }

    protected function TransformDataTransMessages($sErrorCode)
    {
        $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_DEFAULT_ERROR';
        switch ($sErrorCode) {
            case '1001':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_MISSING_FIELD';
                break;
            case '1400':
            case '1004':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_CARD_NUMBER_INVALID';
                break;
            case '1402':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_CARD_NUMBER_EXPIRED';
                break;
            case '1405':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_CARD_AMOUNT_TO_HIGH';
                break;
            case '1404':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_CARD_BLOCKED';
                break;
            case '1403':
                $sErrorMessage = 'ERROR_PAYMENT_DATA_TRANS_CARD_DECLINED';
                break;
        }

        return $sErrorMessage;
    }

    /**
     * Check after response from DataTrans if the payment method is the one which sent request to DataTrans.
     *
     * @return bool $bIsCorrectIPaymentType
     */
    protected function bIsCorrectPaymentType()
    {
        $bIsCorrectIPaymentType = false;
        $oGlobal = TGlobal::instance();
        $sResponsePaymentType = $this->GetPaymentMethodFormAuthorisationRequest();
        if (!empty($sResponsePaymentType)) {
            $aParameter = $this->GetPaymentTypeSpecificParameter();
            if (!empty($sResponsePaymentType) && array_key_exists("$sResponsePaymentType", $aParameter)) {
                $bIsCorrectIPaymentType = true;
            }
        } else { // if pmethod (return value from DataTrans) not in response check manually send payment type
            $sResponsePaymentType = $oGlobal->GetUserData('paymenttype');
            if ($sResponsePaymentType == $this->GetPaymentType()) {
                $bIsCorrectIPaymentType = true;
            }
        }

        return $bIsCorrectIPaymentType;
    }

    /**
     * Getused payment method form DataTrans authorisation response.
     * Overwrite this if your payment method returns in other parameter.
     *
     * @return mixed
     */
    protected function GetPaymentMethodFormAuthorisationRequest()
    {
        $oGlobal = TGlobal::instance();

        return $oGlobal->GetUserData('pmethod');
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
        return $this->GetTestLiveModeParameter('payment_url');
    }

    /**
     * Get the redirect error url on payment execute.
     *
     * @return string $sURL
     */
    protected function GetResponseURL()
    {
        $sReturnURLBase = $this->getActivePageService()->getActivePage()->GetRealURLPlain(array(), true);
        if ('.html' === substr($sReturnURLBase, -5)) {
            $sReturnURLBase = substr($sReturnURLBase, 0, -5);
        }
        if ('/' !== substr($sReturnURLBase, -1)) {
            $sReturnURLBase .= '/';
        }

        return $sReturnURLBase;
    }

    /**
     * Get payment handler parameter depending on live or test mode.
     *
     * @param $sParameterName
     *
     * @return string
     */
    protected function GetTestLiveModeParameter($sParameterName)
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            $sParameterValue = $this->GetConfigParameter('live_'.$sParameterName);
        } else {
            $sParameterValue = $this->GetConfigParameter($sParameterName);
        }

        return $sParameterValue;
    }

    /**
     * executes payment for order.
     *
     * If amount has changed after authorisation don't execute payment
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
            if ($oOrder->fieldValueTotal * 100 == $this->sAuthorisedAmount) {
                $bPaymentOk = $this->ExecuteDataTransPaymentCall($oOrder);
            } else {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage($sMessageConsumer, 'ERROR_PAYMENT_DATA_TRANS_ORDER_REQUEST_AMOUNT_CHANGE');
                $bPaymentOk = false;
            }
        }

        return $bPaymentOk;
    }

    /**
     * Send settlement for authorised transaction.
     *
     * @param \TdbShopOrder $oOrder
     *
     * @return bool
     */
    protected function ExecuteDataTransPaymentCall(TdbShopOrder &$oOrder)
    {
        $sXMLSettlement = $this->GetXMLSettlement();
        $ch = curl_init();
        $settlementurl = trim($this->GetTestLiveModeParameter('settlementurl'));
        curl_setopt($ch, CURLOPT_URL, $settlementurl);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'xmlRequest='.$sXMLSettlement);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $sResponse = curl_exec($ch);
        $sCurlError = curl_error($ch);
        $bPaymentOk = $this->SettlementResponseSuccess($sResponse);
        if (!$bPaymentOk) {
            if (!empty($sCurlError)) {
                TTools::WriteLogEntrySimple('Payment DataTrans: curl failed to ['.$this->GetConfigParameter('settlementurl')."] using xml [{$sXMLSettlement}] with error: ".$sCurlError, 1, __FILE__, __LINE__);
            }
            $iFailure = $this->GetConfigParameter('failure_settlement');
            if ('1' === $iFailure) {
                if ($this->SendFailureSettlementMail($oOrder)) {
                    $bPaymentOk = true;
                } else {
                    TTools::WriteLogEntrySimple('Payment DataTrans: Es konnte keine Abbuchungsfehlere-mail versand werden', 1, __FILE__, __LINE__);
                }
            }
        }
        curl_close($ch);

        return $bPaymentOk;
    }

    /**
     * Sends email to shop owner to settle transaction manually.
     *
     * @param TdbShopOrder $oOrder
     *
     * @return bool
     */
    protected function SendFailureSettlementMail(TdbShopOrder &$oOrder)
    {
        $bSendMail = false;
        $oMail = TdbDataMailProfile::GetProfile('payment_datatrans_error_settlement');
        if (!is_null($oMail)) {
            $sInfo = TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.payment_data_trans.settlement_failure_mail_info',
                    array(
                        '%ordernumber%' => $oOrder->fieldOrdernumber,
                        '%ref%' => $this->GetRefNoParameter(false),
                    )
                ));
            $oMail->AddData('sInfo', $sInfo);
            $bSendMail = $oMail->SendUsingObjectView('emails', 'Customer');
        }

        return $bSendMail;
    }

    /**
     * Checks the response from settlement request.
     * No sign check on settlement response because DataTrans do not send sign in settlement response.
     *
     * @param $sResponse
     *
     * @return bool
     */
    protected function SettlementResponseSuccess($sResponse)
    {
        $bSettlementResponseSuccess = true;
        $sRefNo = $this->GetRefNoParameter(false);
        $oXML = simplexml_load_string($sResponse);
        if (isset($oXML->body->transaction->response)) {
            TTools::WriteLogEntrySimple('Payment DataTrans: Erfoglreiche XML Settlement Response for transaction'.$this->sTransactionId.'and reference number '.$sRefNo, 3, __FILE__, __LINE__);
        } else {
            if (isset($oXML->body->transaction->error)) {
                $sErrorCode = '';
                $sErrorMessage = '';
                if (isset($oXML->body->transaction->error->errorCode)) {
                    $sErrorCode = strval($oXML->body->transaction->error->errorCode);
                }
                if (isset($oXML->body->transaction->error->errorDetail)) {
                    $sErrorMessage = strval($oXML->body->transaction->error->errorDetail);
                }
                if (isset($oXML->body->transaction->error->errorMessage)) {
                    $sErrorMessage .= strval($oXML->body->transaction->error->errorMessage);
                }
                TTools::WriteLogEntrySimple('Payment DataTrans: Fehlerhafte XML Settlement Response for transaction'.$this->sTransactionId.'and reference number '.$sRefNo.'. CODE('.$sErrorCode.') MESSAGE('.$sErrorMessage.')'.' response: '.$sResponse, 1, __FILE__, __LINE__);
            } else {
                TTools::WriteLogEntrySimple('Payment DataTrans: Fehlerhafte XML Settlement Response for transaction'.$this->sTransactionId.'and reference number '.$sRefNo.' response: '.$sResponse, 1, __FILE__, __LINE__);
            }
            $bSettlementResponseSuccess = false;
        }

        return $bSettlementResponseSuccess;
    }

    /**
     * Returns the xml request valeu for settlement.
     *
     * @return string
     */
    protected function GetXMLSettlement()
    {
        $oShopBasket = TShopBasket::GetInstance();
        $sMerchantId = $this->GetTestLiveModeParameter('merchantId');
        $sRefNo = $this->GetRefNoParameter(false);
        $sCurrency = $this->GetCurrencyIdentifier();
        $oLocal = &TCMSLocal::GetActive();
        $sAmount = str_replace(',', '.', $oLocal->FormatNumber($oShopBasket->dCostTotal)) * 100;
        $sXML = '<?xml version="1.0" encoding="UTF-8" ?>
       <paymentService version="1">
         <body merchantId="'.$this->GetTestLiveModeParameter('merchantId').'">
           <transaction refno="'.$sRefNo.'">
             <request>
               <amount>'.$sAmount.'</amount>
               <currency>'.$sCurrency.'</currency>
               <uppTransactionId>'.$this->sTransactionId.'</uppTransactionId>
               <transtype>'.self::PAYMENT_CREDIT_CARD_TRANS_TYPE_ID.'</transtype>
               <sign>'.$this->GetSecurityHash($this->GetTestLiveModeParameter('sign'), $sMerchantId, $sAmount, $sCurrency, $sRefNo).'</sign>
             </request>
           </transaction>
         </body>
       </paymentService>';

        return $sXML;
    }

    /**
     * hook is called before the payment data is committed to the database. use it to cleanup/filter/add data you may
     * want to include/exclude from the database.
     *
     * Save transaction reference number to order to fin order in DataTrans
     *
     * @param array $aPaymentData
     *
     * @return array
     */
    protected function PreSaveUserPaymentDataToOrderHook($aPaymentData)
    {
        $aPaymentData = parent::PreSaveUserPaymentDataToOrderHook($aPaymentData);
        $aPaymentData['sReferenceNumber'] = $this->GetRefNoParameter(false);

        return $aPaymentData;
    }

    /**
     * Converts hex to string. Was needed for the sign check.
     *
     * @param $hex
     *
     * @return string
     */
    protected function hexstr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }

        return $string;
    }

    protected function hmac($key, $data)
    {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack('H*', md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad;
        $k_opad = $key ^ $opad;

        return md5($k_opad.pack('H*', md5($k_ipad.$data)));
    }

    protected function GetSecurityHash($key, $merchId, $amount, $ccy, $idno)
    {
        $str = $merchId.$amount.$ccy.$idno;
        $key2 = $this->hexstr($key);

        return $this->hmac($key2, $str);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private function getActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
