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
use Symfony\Component\HttpFoundation\Request;

/**
 * @psalm-suppress UndefinedClass - financegate* classes do not exist
 */
class TShopPaymentHandlerPayOne extends TdbShopPaymentHandler
{
    /**
     * MessageManager listener name.
     */
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerPayOneMSG';

    /**
     * System constant to identify 3DSREDIRECT.
     */
    const URL_PARAMETER_3D_REDIRECT = '3dsredirect';

    /**
     * if true the credit card expiration date will be submitted to the shop
     * note: maybe this isn`t allowed by the credit card company (PCI).
     *
     * @var bool
     */
    protected $bSubmitExpireDateToShop = false;

    /**
     * if true, the credit card validation is extended by a manual secure code check
     * (redirect to credit card company gateway).
     *
     * @var bool - default = true
     */
    protected $bUse3DSecure = true;

    /**
     * Get path to view location.
     *
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerPayOne';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['creditCardType'] = '';
        $aData['creditCardNumber'] = '';

        $oUser = TdbDataExtranetUser::GetInstance();
        $oBillingAdr = $oUser->GetBillingAddress();
        $aData['creditCardOwnerName'] = $oBillingAdr->fieldFirstname.' '.$oBillingAdr->fieldLastname;
        $aData['creditCardValidToMonth'] = '';
        $aData['creditCardValidToYear'] = '';
        $aData['creditCardChecksum'] = '';

        $aData['pseudocardpan'] = '';
        $aData['truncatedcardpan'] = '';

        return $aData;
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

        if ($bIsValid) {
            $oMsgManager = TCMSMessageManager::GetInstance();

            $sField = 'pseudocardpan';
            if (!array_key_exists($sField, $this->aPaymentUserData) || empty($this->aPaymentUserData[$sField])) {
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME.'-'.$sField, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bIsValid = false;
            }
        }

        return $bIsValid;
    }

    /**
     * store user payment data in order.
     *
     * @param int $iOrderId
     *
     * @return void
     */
    public function SaveUserPaymentDataToOrder($iOrderId)
    {
        $aUserPaymentData = $this->GetUserPaymentData();
        if (isset($_SESSION['PayOneResponse']) && is_array($_SESSION['PayOneResponse'])) {
            $aUserPaymentData = array_merge($aUserPaymentData, $_SESSION['PayOneResponse']);
        }
        if (isset($_SESSION['PayOneRedirectParams']) && is_array($_SESSION['PayOneRedirectParams'])) {
            $aUserPaymentData = array_merge($aUserPaymentData, $_SESSION['PayOneRedirectParams']);
        }

        if (is_array($aUserPaymentData)) {
            $query = "DELETE FROM `shop_order_payment_method_parameter` WHERE `shop_order_id` = '".MySqlLegacySupport::getInstance()->real_escape_string($iOrderId)."'";
            MySqlLegacySupport::getInstance()->query($query);
            foreach ($aUserPaymentData as $keyId => $keyVal) {
                //save only if not empty
                //save only allowed parameters
                $aNotAllowedParams = array('CREDITCARDNUMBER');
                if (!$this->bSubmitExpireDateToShop) {
                    $aNotAllowedParams[] = 'CREDITCARDVALIDTOMONTH';
                    $aNotAllowedParams[] = 'CREDITCARDVALIDTOYEAR';
                }

                if ((!in_array(strtoupper(trim($keyId)), $aNotAllowedParams)) && (strlen($keyVal) > 0)) {
                    $oPaymentParameter = TdbShopOrderPaymentMethodParameter::GetNewInstance();
                    /** @var $oPaymentParameter TdbShopOrderPaymentMethodParameter */
                    $aTmpData = array('shop_order_id' => $iOrderId, 'name' => $keyId, 'value' => $keyVal);
                    $oPaymentParameter->AllowEditByAll(true);
                    $oPaymentParameter->LoadFromRow($aTmpData);
                    $oPaymentParameter->Save();
                }
            }
        }
    }

    /**
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes[] = '<script src="https://secure.pay1.de/client-api/js/ajax.js" type="text/javascript"></script>';
        $sTmpJs = $this->GetJsMethods();
        foreach ($sTmpJs as $sK => $sV) {
            $aIncludes[] = $sV;
        }

        return $aIncludes;
    }

    /**
     * Get all PAYONE javascript methods.
     *
     * @return array
     */
    protected function GetJsMethods()
    {
        $aData = array();
        $aData['aid'] = $this->GetConfigParameter('aid');
        $aData['encoding'] = $this->GetConfigParameter('encoding');
        $aData['mid'] = $this->GetConfigParameter('mid');
        $aData['mode'] = $this->GetConfigParameter('mode');
        $aData['portalid'] = $this->GetConfigParameter('portalid');
        $aData['request'] = 'creditcardcheck';
        $aData['storecarddata'] = 'yes';
        ksort($aData);

        $sHashData = implode('', $aData);
        $sHashData .= $this->GetConfigParameter('key');

        $hash = md5($sHashData);

        $sPayOneMessageLanguageISO = $this->GetCurrentLanguageCode();

        $oPaymentMethod = TdbShopPaymentMethod::GetNewInstance();
        /** @var $oPaymentMethod TdbShopPaymentMethod */
        $oPaymentMethod->LoadFromField('name_internal', 'credit_card_payone');

        $sTmpJs = "
        <script type=\"text/javascript\">

        var data = {
          request : 'creditcardcheck',
          mode : '".TGlobal::OutJS($this->GetConfigParameter('mode'))."',
          mid : '".TGlobal::OutJS($this->GetConfigParameter('mid'))."',
          aid : '".TGlobal::OutJS($this->GetConfigParameter('aid'))."',
          portalid : '".TGlobal::OutJS($this->GetConfigParameter('portalid'))."',
          encoding : '".TGlobal::OutJS($this->GetConfigParameter('encoding'))."',
          storecarddata : 'yes',
          hash : '".TGlobal::OutJS($hash)."'
        }

        var options = {
          return_type : 'object',
          callback_function_name : 'processPayoneResponse'
        }
        function processPayoneResponse(response) {
          // if (console != undefined) { console.log(response); }

          switch(response.get('status')) {
            case 'VALID':
              var oCreditCardNumber = document.forms.checkout.elements['aPayment[creditCardNumber]'];
              oCreditCardNumber.value = response.get('truncatedcardpan');

              var oTruncatedcardpan = document.forms.checkout.elements['aPayment[truncatedcardpan]'];
              oTruncatedcardpan.value = response.get('truncatedcardpan');

              var oPseudocardpan = document.forms.checkout.elements['aPayment[pseudocardpan]'];
              oPseudocardpan.value = response.get('pseudocardpan');


              // collect card-type data
              var oCreditCardTypePayone = document.forms.checkout.elements['payone[creditCardType]'];
              for (var i=0; i < oCreditCardTypePayone.length; i++) {
                if (oCreditCardTypePayone[i].checked) {
                  sTmpVal = oCreditCardTypePayone[i].value;
                }
              }

              var oCreditCardType = document.forms.checkout.elements['aPayment[creditCardType]'];
              oCreditCardType.value = sTmpVal;

              // collect card owner
              var oCreditCardOwner = document.forms.checkout.elements['aPayment[creditCardOwnerName]'];
              oCreditCardOwner.value = document.forms.checkout.elements['payone[creditCardOwnerName]'].value;
              ";

        if ($this->bSubmitExpireDateToShop) {
            $sTmpJs .= "
              // collect card-expiration month
              $('#selectList').val();
              var sCreditCardValidToMonthPayone = document.forms.checkout.elements['payone[creditCardValidToMonth]'][document.forms.checkout.elements['payone[creditCardValidToMonth]'].selectedIndex].text;

              var oCreditCardValidToMonth = document.forms.checkout.elements['aPayment[creditCardValidToMonth]'];
              oCreditCardValidToMonth.value = sCreditCardValidToMonthPayone;

              // collect card-expiration year
              var sCreditCardValidToYearPayone = '';
              var sCreditCardValidToYearPayone = document.forms.checkout.elements['payone[creditCardValidToYear]'][document.forms.checkout.elements['payone[creditCardValidToYear]'].selectedIndex].text;

              var oCreditCardValidToYear = document.forms.checkout.elements['aPayment[creditCardValidToYear]'];
              oCreditCardValidToYear.value = sCreditCardValidToYearPayone;
              ";
        }

        $sTmpJs .= "
              // reset payone fields
              document.forms.checkout.elements['payone[creditCardNumber]'].value = '';
              document.forms.checkout.elements['payone[creditCardChecksum]'].value = '';
              document.forms.checkout.elements['payone[creditCardValidToMonth]'].selectedIndex = 0;
              document.forms.checkout.elements['payone[creditCardValidToYear]'].selectedIndex = 0;

              document.forms.checkout.submit();
              break;
            case 'INVALID':
               ".$this->GetJSMessageMethodCall("response.get('customermessage')")."
            case 'ERROR':
              //alert('Response status '+response.get('status') +' '+ response.get('errorcode') +' '+ response.get('errormessage') +' '+ response.get('customermessage') );
              ".$this->GetJSMessageMethodCall("response.get('customermessage')").'
              break;
            default:
              '.$this->GetJSMessageMethodCall("'".TGlobal::Translate('chameleon_system_shop.payment_payone.error_unknown')."'")."
              break;
          }
        }
        function Send() {
          var oForm = document.forms.checkout;

          // check if card validation / pseudocardpan already exists
          if(IsCreditCardPaymentAndCardAlreadyValidated()) {
            oForm.submit();
            return true;
          } else {

            var oCreditCardType = oForm.elements['payone[creditCardType]'];
            var sTmpVal = '';

            //collect card-type data
            for (var i=0; i < oCreditCardType.length; i++) {
              if (oCreditCardType[i].checked) {
                sTmpVal = oCreditCardType[i].value;
              }
            }

            if (sTmpVal.length < 1) {
              ".$this->GetJSMessageMethodCall("'".TGlobal::Translate('chameleon_system_shop.payment_payone.error_no_card_type')."'")."
              return false;
            } else {
              data.cardtype = sTmpVal;
            }

            var oCreditCardOwnerName = oForm.elements['payone[creditCardOwnerName]'];
            if (oCreditCardOwnerName.value.length < 1) {
              ".$this->GetJSMessageMethodCall("'".TGlobal::Translate('chameleon_system_shop.payment_payone.error_no_card_owner')."'")."
              oCreditCardOwnerName.focus();
              return false;
            } else {
              data.cardholder = oCreditCardOwnerName.value;
            }

            var oCreditCardNumber = oForm.elements['payone[creditCardNumber]'];
            if (oCreditCardNumber.value.length < 1) {
              ".$this->GetJSMessageMethodCall("'".TGlobal::Translate('chameleon_system_shop.payment_payone.error_no_card_number')."'")."
              oCreditCardNumber.focus();
              return false;
            } else {
              data.cardpan = oCreditCardNumber.value;
            }

            var oCreditCardValidToYear = oForm.elements['payone[creditCardValidToYear]'];
            var sCreditCardValidToYear = oCreditCardValidToYear.value+'';
            var sShortYear = sCreditCardValidToYear.slice(2, 4);
            var oCreditCardValidToMonth = oForm.elements['payone[creditCardValidToMonth]'];
            var sCreditCardValidToMonth = oCreditCardValidToMonth.value;
            if(sCreditCardValidToMonth.length == 1) sCreditCardValidToMonth = '0' + sCreditCardValidToMonth;

            data.cardexpiredate = sShortYear + sCreditCardValidToMonth;

            var oCreditCardChecksum = oForm.elements['payone[creditCardChecksum]'];
            if (oCreditCardChecksum.value.length < 1) {
              ".$this->GetJSMessageMethodCall("'".TGlobal::Translate('chameleon_system_shop.payment_payone.error_no_card_checksum')."'")."
              oCreditCardChecksum.focus();
              return false;
            } else {
              data.cardcvc2 = oCreditCardChecksum.value;
            }

            data.language = '".TGlobal::OutJS($sPayOneMessageLanguageISO)."';

            var request = new PayoneRequest(data, options);
            request.checkAndStore();
          }
        }

        ";

        $sTmpJs .= $this->GetJSMessageMethod();
        $sTmpJs .= $this->GetJSMethodIsCreditCardPaymentAndCardAlreadyValidated($oPaymentMethod);

        $sJQueryButtonSelector = $this->GetNextButtonCSSSelector();

        $sTmpJs .= "
        $(document).ready(function() {
          $('#checkout').unbind();
          $('#checkout').submit(
              function() {
                Send();
              }
          );

          $('".$sJQueryButtonSelector."').removeAttr('onclick');
          $('".$sJQueryButtonSelector."').unbind();
          $('".$sJQueryButtonSelector."').click(function() { Send(); return false; });
        });

        </script>
      ";

        return explode('\n', $sTmpJs);
    }

    /**
     * returns the JS code for the method: IsCreditCardPaymentAndCardAlreadyValidated().
     *
     * @param TdbShopPaymentMethod $oPaymentMethod
     *
     * @return string
     */
    protected function GetJSMethodIsCreditCardPaymentAndCardAlreadyValidated($oPaymentMethod)
    {
        $sJS = "
        function IsCreditCardPaymentAndCardAlreadyValidated() {
          var oForm = document.forms.checkout;
          var returnVal = true;

          var paymentTypeId = $(\"input[name='aShipping[shop_payment_method_id]']:checked\").val();
          // alert('".$oPaymentMethod->id."' + ' = ' + paymentTypeId);
          if (paymentTypeId == '".$oPaymentMethod->id."') {
            var oPseudocardpan = oForm.elements['aPayment[pseudocardpan]'];

            // check for new credit card and reset pseudocardpan if number found
            var oCreditCardNumber = oForm.elements['payone[creditCardNumber]'];
            if(oCreditCardNumber.value.length > 0) {
              oPseudocardpan.value = '';
            }

            if (oPseudocardpan.value.length > 0) {
              // validation performed pseudocardpan available
              returnVal = true;
            } else {
              // no validation performed
              returnVal = false;
            }
          }

          return returnVal;
        }
        ";

        return $sJS;
    }

    /**
     * if the next button doesn`t have the CSS id "tbtn" you need to set the
     * jQuery selector here.
     *
     * @return string - returns jQuery selector
     */
    protected function GetNextButtonCSSSelector()
    {
        //$sJQueryButtonSelector = '#tbtn';
        $sJQueryButtonSelector = '.buttonCheckout';

        return $sJQueryButtonSelector;
    }

    /**
     * overwrite this method to change the standard alert messages to custom
     * styled error messages.
     *
     * @return string
     */
    protected function GetJSMessageMethod()
    {
        $sJSMethod = '
      function ShowMessage(sMessage) {
        alert(sMessage);
      }';

        return $sJSMethod;
    }

    /**
     * if you changed the name of GetJSMessageMethod() you need to change
     * the call, too.
     *
     * @param string $sMessage - you need to add "'" around your message
     *
     * @return string
     */
    protected function GetJSMessageMethodCall($sMessage)
    {
        $sJSMethodCall = 'ShowMessage('.$sMessage.');';

        return $sJSMethodCall;
    }

    /**
     * executes payment for order.
     *
     * @param TdbShopOrder $oOrder
     * @param string       $sMessageConsumer - send error messages here
     *
     * @return bool
     */
    public function ExecutePayment(TdbShopOrder $oOrder, $sMessageConsumer = '')
    {
        $bPaymentOk = parent::ExecutePayment($oOrder, $sMessageConsumer);

        $aParams = array();
        // payment preauthorization values
        if (isset($_SESSION['3ds_payment'])) {
            $aParams = $_SESSION['3ds_payment'];
            unset($_SESSION['3ds_payment']);
        }

        // execute preauthorization
        $aResponse = $this->PayOnePreauthorization($aParams, $oOrder);
        if ($aResponse['success']) {
            $bPaymentOk = true;
            // reset payOne session
            if (isset($_SESSION['PayOneResponse'])) {
                unset($_SESSION['PayOneResponse']);
            }
        } else {
            $bPaymentOk = false;
        }

        if (!$bPaymentOk) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => $aResponse['errormessage']));
        }

        return $bPaymentOk;
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
        $bSuccess = parent::PostSelectPaymentHook($sMessageConsumer);
        if ($bSuccess) {
            $sDebugMsg = '';
            $bSuccess = false;
            if ($this->bUse3DSecure) {
                $aPayOneResponse = $this->PayOne3dSecure_3dScheck();
                if ($aPayOneResponse['success']) {
                    // Payment is finished - we don't need to redirect to Secure-PIN-URL!
                    // Redirecting to next step...
                    $bSuccess = true;
                } else {
                    // Payment api error?!
                    // $aPayOneResponse['status'];
                    if (isset($aPayOneResponse['errormessage'])) {
                        $sDebugMsg = TGlobal::Translate('chameleon_system_shop.payment_payone.error', array('%errortext%' => $aPayOneResponse['errortext']));
                    }
                    // $aPayOneResponse['errormessage']; // english real error message

                    $oMsgManager = TCMSMessageManager::GetInstance();
                    $oMsgManager->AddMessage($sMessageConsumer, 'ERROR-ORDER-REQUEST-PAYMENT-ERROR', array('errorMsg' => $sDebugMsg));
                }
            } else {
                $bSuccess = true;
            }
        }

        return $bSuccess;
    }

    /**
     * the method is called when an external payment handler returns successfully.
     */
    public function PostProcessExternalPaymentHandlerHook()
    {
        $bPaymentTransmitOk = parent::PostProcessExternalPaymentHandlerHook();
        if ($bPaymentTransmitOk) {
            $oURLData = TCMSSmartURLData::GetActive();
            // If 3D-Secure Authentification fails, the ECI value is empty
            if (!empty($oURLData->aParameters['eci']) && !empty($oURLData->aParameters['xid'])) {
                $_SESSION['3ds_payment']['eci'] = $oURLData->aParameters['eci'];
                $_SESSION['3ds_payment']['xid'] = $oURLData->aParameters['xid'];
                $_SESSION['3ds_payment']['cavv'] = $oURLData->aParameters['cavv'];
                $bPaymentTransmitOk = true;
            } else {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(self::MSG_MANAGER_NAME, 'ERROR-ORDER-RESPONSE-PAYMENTONE-ERROR', array('errorMsg' => TGlobal::Translate('chameleon_system_shop.payment_payone.error_user_abort')));
                $bPaymentTransmitOk = false;
            }
        }

        return $bPaymentTransmitOk;
    }

    /**
     * 3D-Secure - 3dScheck.
     *
     * @return array<string, mixed>
     */
    protected function PayOne3dSecure_3dScheck()
    {
        $aResponse = array();
        $aResponse['success'] = false;

        $oGlobal = TGlobal::instance();
        $oBasket = TShopBasket::GetInstance();

        // load data from POST
        $aUserPaymentData = $this->GetUserPaymentData();

        // Save to session
        $_SESSION['PayOneResponse']['pseudocardpan'] = $aUserPaymentData['pseudocardpan'];
        $_SESSION['PayOneResponse']['truncatedcardpan'] = $aUserPaymentData['truncatedcardpan'];

        // create success URL
        $aSuccessCall = array('module_fnc' => array($oGlobal->GetExecutingModulePointer()->sModuleSpotName => 'PostProcessExternalPaymentHandlerHook'));

        $aExcludes = array_keys($aSuccessCall);
        $aExcludes[] = 'aShipping';
        $aExcludes[] = 'orderstepmethod';
        $aExcludes[] = 'aPayment';
        $aExcludes[] = 'module_fnc';

        $sSuccessURL = urldecode(str_replace('&amp;', '&', $this->getActivePageService()->getLinkToActivePageAbsolute($aSuccessCall, $aExcludes)));
        $sSuccessURL .= '&payrequest=3dsredirect';

        // Init 3D-Secure params
        $frequest = new financegateRequest();
        $fconnect = new financegateConnect();

        $frequest->setRequest('3dscheck');
        $frequest->setClearingType('cc');
        $frequest->setPortalId($this->GetConfigParameter('portalid'));
        $frequest->setKey($this->GetConfigParameter('key'));
        $frequest->setMId($this->GetConfigParameter('mid'));
        $frequest->setAId($this->GetConfigParameter('aid'));
        $frequest->setMode($this->GetConfigParameter('mode'));
        $request = $this->getCurrentRequest();
        $frequest->setIp(null === $request ? '' : $request->getClientIp());

        $frequest->setExitUrl($sSuccessURL);
        $frequest->setAmount($oBasket->dCostTotal * 100);
        $frequest->setCurrency($this->GetCurrency());
        $frequest->setLanguage($this->GetCurrentLanguageCode());

        $frequest->setPseudocardpan($aUserPaymentData['pseudocardpan']);

        $fconnect->setApiUrl($this->GetConfigParameter('serverApiUrlPayOne'));
        $fresponse = $fconnect->processByRequest($frequest);
        //if(_ES_DEBUG) var_dump($fresponse);

        $sStatus = $fresponse->getStatus();

        $aResponse['success'] = true;
        $aResponse['status'] = $sStatus;

        if ('ENROLLED' == $sStatus) {
            $this->ExecuteExternalPayOneCall($fresponse); // REDIRECT TO EXTERNAL SECURE-PIN-URL!
        } else {
            if ('VALID' == $sStatus) {
                // do nothing
            } else {
                $aResponse['success'] = false;
                $aResponse['status'] = $sStatus;
                $aResponse['errormessage'] = $fresponse->getErrorMessage();
                $aResponse['errortext'] = $fresponse->getCustomerMessage();
                //if(_ES_DEBUG) print_form($_REQUEST,$info);
            }
        }

        return $aResponse;
    }

    /**
     * returns the payment currency in ISO 4217 (EUR, USD).
     *
     * @return string|false
     */
    protected function GetCurrency()
    {
        return $this->GetConfigParameter('currency');
    }

    /**
     * return ISO code of current language (default de).
     *
     * @return string
     */
    protected function GetCurrentLanguageCode()
    {
        $oLanguage = static::getLanguageService()->getActiveLanguage();

        $sPayOneMessageLanguageISO = 'en';
        if ('de' === $oLanguage->fieldIso6391) {
            $sPayOneMessageLanguageISO = 'de';
        }

        return $sPayOneMessageLanguageISO;
    }

    /**
     * After 3dscheck exec this function for "preauthorization".
     *
     * @param array<string, mixed> $aParams
     * @param TdbShopOrder $oOrder
     * @return array
     */
    protected function PayOnePreauthorization($aParams, $oOrder)
    {
        $aResponse = array();
        $aResponse['success'] = false;

        $oBasket = TShopBasket::GetInstance();

        $_SESSION['PayOneResponse']['cavv'] = $aParams['cavv'];
        $_SESSION['PayOneResponse']['xid'] = $aParams['xid'];
        $_SESSION['PayOneResponse']['eci'] = $aParams['eci'];

        $frequest = new financegateRequest();
        $fconnect = new financegateConnect();

        $frequest->setRequest('preauthorization');
        $frequest->setClearingType('cc');
        $frequest->setReference(time());

        $frequest->setPortalId($this->GetConfigParameter('portalid'));
        $frequest->setKey($this->GetConfigParameter('key'));
        $frequest->setMId($this->GetConfigParameter('mid'));
        $frequest->setAId($this->GetConfigParameter('aid'));
        $frequest->setMode($this->GetConfigParameter('mode'));

        $frequest->setAmount($oBasket->dCostTotal * 100);
        $frequest->setCurrency($this->GetCurrency());

        $oUser = TdbDataExtranetUser::GetInstance();
        $frequest->setFirstname($oUser->fieldFirstname);
        $frequest->setLastname($oUser->fieldLastname);
        $frequest->setCustomerId($oUser->fieldCustomerNumber);
        $request = $this->getCurrentRequest();
        $frequest->setIp(null === $request ? '' : $request->getClientIp());

        $sIsoCode = '';
        $oCountry = $oUser->GetFieldDataCountry();
        if (is_object($oCountry)) {
            $oTCountry = $oCountry->GetFieldTCountry();
            if (is_object($oTCountry)) {
                $sIsoCode = $oTCountry->fieldIsoCode2;
            }
        }
        $frequest->setCountry($sIsoCode);

        $oShippingAddress = $oUser->GetShippingAddress();

        $frequest->setStreetName($oShippingAddress->fieldStreet);
        $frequest->setStreetNumber($oShippingAddress->fieldStreetnr);
        $frequest->setCity($oShippingAddress->fieldCity);
        $frequest->setZip($oShippingAddress->fieldPostalcode);

        $frequest->setPseudocardpan($_SESSION['PayOneResponse']['pseudocardpan']);

        // If 3D-Secure Authentification fails, the ECI value is empty
        if (!empty($aParams['eci']) && !empty($aParams['xid']) && !empty($aParams['cavv'])) {
            $frequest->setXid($aParams['xid']);
            $frequest->setCavv($aParams['cavv']);
            $frequest->setEci($aParams['eci']);
        }

        $fconnect->setApiUrl($this->GetConfigParameter('serverApiUrlPayOne'));

        $fresponse = $fconnect->processByRequest($frequest);

        if ('APPROVED' == $fresponse->getStatus()) {
            //print_success($fresponse);
            $aResponse['success'] = true;
        } else {
            $aResponse['success'] = false;
            $aResponse['status'] = $fresponse->getStatus();
            $aResponse['errormessage'] = $fresponse->getErrorMessage();
            $aResponse['errortext'] = $fresponse->getCustomerMessage();
            //print_form($_REQUEST,$info);
        }

        return $aResponse;
    }

    /**
     * perform the API redirect to PayOne using Server-API.
     *
     * @param mixed $fresponse
     * @return array $fresponse - params to handle request/redirect
     */
    protected function ExecuteExternalPayOneCall($fresponse)
    {
        $oShop = TShop::GetInstance();
        $sRedirectURL = $oShop->GetLinkToSystemPage('PayOne3DSecureHelper');

        // save 3D Secure Parameter in session to load it in the helper redirect page
        $_SESSION['PayOneRedirectParams']['AcsUrl'] = $fresponse->getAcsUrl();
        $_SESSION['PayOneRedirectParams']['PaReq'] = $fresponse->getPaReq();
        $_SESSION['PayOneRedirectParams']['TermUrl'] = $fresponse->getTermUrl();
        $_SESSION['PayOneRedirectParams']['MD'] = $fresponse->getMd();

        $this->getRedirect()->redirect($sRedirectURL);
    }

    /**
     * converts creditcard system codes to full name of the card e.g. V => Visa.
     *
     * @param string $sCreditCardCode
     *
     * @return string
     */
    public static function GetCreditCardNameByCode($sCreditCardCode)
    {
        $sReturnVal = $sCreditCardCode;
        $aCreditCardNames = array();
        $aCreditCardNames['V'] = 'Visa';
        $aCreditCardNames['M'] = 'Mastercard';
        $aCreditCardNames['A'] = 'Amex';
        $aCreditCardNames['D'] = 'Diners';
        $aCreditCardNames['J'] = 'JCB';
        $aCreditCardNames['O'] = 'Maestro International';
        $aCreditCardNames['U'] = 'Maestro UK';
        $aCreditCardNames['C'] = 'Discover';
        $aCreditCardNames['B'] = 'Carte Bleue';

        if (array_key_exists($sCreditCardCode, $aCreditCardNames)) {
            $sReturnVal = $aCreditCardNames[$sCreditCardCode];
        }

        return $sReturnVal;
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
        if (is_array($this->aPaymentUserData) && array_key_exists('paymentDataId', $this->aPaymentUserData)) {
            $sIdent = $this->aPaymentUserData['paymentDataId'];
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

    /**
     * @return Request|null
     */
    private function getCurrentRequest()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('request_stack')->getCurrentRequest();
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }
}
