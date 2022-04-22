<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopPaymentHandlerOgoneAliasGateway extends TShopPaymentHandlerOgoneBase
{
    const MSG_MANAGER_NAME = 'TShopPaymentHandlerOgoneAliasGatewayMSG';

    /**
     * @return string
     */
    protected function GetViewPath()
    {
        return parent::GetViewPath().'/TShopPaymentHandlerOgoneAliasGateway';
    }

    /**
     * return the default payment data for the handler.
     *
     * @return array<string, string>
     */
    protected function GetDefaultUserPaymentData()
    {
        $aData = parent::GetDefaultUserPaymentData();
        $aData['CN'] = '';
        $aData['CARDNO'] = '';
        $aData['CVC'] = '';
        $aData['ECOM_CARDINFO_EXPDATE_MONTH'] = date('n');
        $aData['ECOM_CARDINFO_EXPDATE_YEAR'] = date('Y');

        return $aData;
    }

    /**
     * Get all needed parameter for first request to.
     *
     * @return array
     */
    protected function GetAllInputFieldParameter()
    {
        $aParameter = array();

        $aParameter['PSPID'] = $this->GetConfigParameter('user_id');
        $aParameter['ACCEPTURL'] = $this->GetSuccessURL();
        $aParameter['EXCEPTIONURL'] = $this->GetErrorURL();
        // if the user already requested an alias we want to update that existing one so we need the given order id (ref from ogone) and the alias for the hashing too
        if (isset($this->aPaymentUserData['ORDERID']) && '' !== $this->aPaymentUserData['ORDERID']) {
            $aParameter['ORDERID'] = $this->aPaymentUserData['ORDERID'];
        }
        if (isset($this->aPaymentUserData['ALIAS']) && '' !== $this->aPaymentUserData['ALIAS']) {
            $aParameter['ALIAS'] = $this->aPaymentUserData['ALIAS'];
        }

        $aHashParameter = $aParameter;
        $aParameter['SHASIGN'] = $this->BuildOutgoingHash($aHashParameter);

        return $aParameter;
    }

    /**
     * defines the success url of the "get alias" call
     * by default we want to return to the shipping step with processing parameters set (execute step, active shipping group and payment method).
     *
     * @param string $sStepName
     *
     * @return bool|string
     */
    protected function GetSuccessURL($sStepName = '')
    {
        if (empty($sStepName)) {
            $sStepName = 'shipping';
        }
        $sURL = false;
        $oShippingStep = TdbShopOrderStep::GetStep($sStepName);
        /** @var $oShippingStep TdbShopOrderStep */
        if (!is_null($oShippingStep)) {
            $sURL = $oShippingStep->GetStepURL(true, true);
            if ('/' == substr($sURL, -1)) {
                $sURL = substr($sURL, 0, -1);
            }

            if ('shipping' == $sStepName) {
                $aResponse = array('module_fnc' => array(TGlobal::instance()->GetExecutingModulePointer()->sModuleSpotName => 'ExecuteStep'), MTShopOrderWizardCore::URL_PARAM_STEP_METHOD => '', 'aShipping' => array('shop_shipping_group_id' => TShopBasket::GetInstance()->GetActiveShippingGroup()->id, 'shop_payment_method_id' => $this->GetOwningPaymentMethodId()));
                $sURL .= '?'.str_replace('&amp;', '&', TTools::GetArrayAsURL($aResponse));
            }
        }

        return $sURL;
    }

    /**
     * defines the error url of the "get alias" call
     * by default we only want to return to the shipping step.
     *
     * @param string $sStepName
     *
     * @return string
     */
    protected function GetErrorURL($sStepName = '')
    {
        if (empty($sStepName)) {
            $sStepName = 'shipping';
        }
        $sURL = false;
        $oShippingStep = TdbShopOrderStep::GetStep($sStepName);
        /** @var $oShippingStep TdbShopOrderStep */
        if (!is_null($oShippingStep)) {
            $sURL = $oShippingStep->GetStepURL(true, true);
            if ('/' == substr($sURL, -1)) {
                $sURL = substr($sURL, 0, -1);
            }
        }

        return $sURL;
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
            $aViewVariables = array();
        }
        $aViewVariables['sAliasGatewayRequestUrl'] = $this->GetRequestURL();
        $aViewVariables['aHiddenInput'] = $this->GetAllInputFieldParameter();

        return $aViewVariables;
    }

    /**
     * @return string
     */
    protected function GetRequestURL()
    {
        if (IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION === $this->getEnvironment()) {
            return $this->GetConfigParameter('sOgoneAliasGatewayURLLive');
        } else {
            return $this->GetConfigParameter('sOgoneAliasGatewayURLTest');
        }
    }

    /**
     * load user payment data
     * overload the payment data with post data or the data of the active payment handler from the basket object if there is one.
     *
     * @return array
     */
    protected function GetUserPaymentData()
    {
        parent::GetUserPaymentData();

        $oGlobal = TGlobal::instance();
        //if no new payment data was submitted try to get the data from the active payment method
        if (!$oGlobal->UserDataExists(TdbShopPaymentHandler::URL_PAYMENT_USER_INPUT) && !isset($this->aPaymentUserData['NCERROR'])) {
            $oBasket = TShopBasket::GetInstance();
            $oPaymentMethod = &$oBasket->GetActivePaymentMethod();
            if (null !== $oPaymentMethod) {
                $oPaymentHandler = $oPaymentMethod->GetFieldShopPaymentHandler();
                if ($oPaymentHandler instanceof self) {
                    $this->aPaymentUserData = $oPaymentHandler->GetUserPaymentDataWithoutLoading();
                }
            }
        }
        $this->MapExpireDateFromAPI();
        $this->HandleError();

        return $this->aPaymentUserData;
    }

    /**
     * the api only returns concatenated date instead of month and year in single fields
     * so we want to map them because we use the single fields for submitting.
     *
     * @return void
     */
    protected function MapExpireDateFromAPI()
    {
        if (isset($this->aPaymentUserData['ED']) && '' !== $this->aPaymentUserData['ED']) {
            $this->aPaymentUserData['ECOM_CARDINFO_EXPDATE_MONTH'] = substr($this->aPaymentUserData['ED'], 0, 2);
            $this->aPaymentUserData['ECOM_CARDINFO_EXPDATE_YEAR'] = '20'.substr($this->aPaymentUserData['ED'], 2, 2);
        }
    }

    /**
     * return true if the user data is valid
     * data is loaded from GetUserPaymentData().
     *
     * @return bool
     */
    public function ValidateUserInput()
    {
        $bValid = parent::ValidateUserInput();

        $bValid = ($bValid && $this->CheckIncomingHash(TGlobal::instance()->GetUserData()));

        return $bValid;
    }

    /**
     * handle error codes from the api.
     *
     * @return void
     */
    protected function HandleError()
    {
        static $bErrorsHandled = false;
        if (!$bErrorsHandled) {
            $bErrorsHandled = true;
            $oMessageManager = TCMSMessageManager::GetInstance();
            $oGlobal = TGlobal::instance();
            $aErrorFields = $this->GetErrorFields();
            $aReturnedParameter = $oGlobal->GetUserData();
            $aTrackedErrors = array();
            $sConsumerName = self::MSG_MANAGER_NAME;
            foreach ($aErrorFields as $sErrorField) {
                if (array_key_exists($sErrorField, $aReturnedParameter) && $aReturnedParameter[$sErrorField] > 0) {
                    if (!in_array($aReturnedParameter[$sErrorField], $aTrackedErrors)) {
                        TTools::WriteLogEntry('OGONE Alias Gateway: error from alias gateway call: '.$sErrorField.'-'.$aReturnedParameter[$sErrorField], 1, __FILE__, __LINE__);
                        $oMessageManager->AddMessage($sConsumerName, 'PAYMENT-HANDLER-OGONE-ALIAS-GATEWAY-ERROR-'.$sErrorField.'-'.$aReturnedParameter[$sErrorField]);
                        $aTrackedErrors[] = $aReturnedParameter[$sErrorField];
                    }
                }
            }
        }
    }

    /**
     * define error fields that will be returned by the api.
     *
     * @return array
     */
    protected function GetErrorFields()
    {
        return array('NCErrorCN', 'NCErrorCardNo', 'NCErrorCVC', 'NCErrorED', 'NCError');
    }
}
