<?php

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @deprecated - you should use TShopStepUserDataV2 instead
/**/
class TShopStepUserDataCore extends TdbShopOrderStep
{
    protected $bShowShippingAddressInput = false;
    protected $oNewsletterSignup = null;
    const SESSION_STATE_NAME = 'TShopStepUserData-sessionstate';
    const INPUT_DATA_NAME = 'aUser';
    const URL_NAME_NEWSLETTER_SIGNUP = 'bSignupNewsletter';

    /**
     * returns true if the user may view the step.
     *
     * @param bool $bRedirectToPreviousPermittedStep
     *
     * @return bool
     */
    protected function AllowAccessToStep($bRedirectToPreviousPermittedStep = false)
    {
        $bAllowAccess = parent::AllowAccessToStep($bRedirectToPreviousPermittedStep);
        $oBasket = TShopBasket::GetInstance();

        if ($oBasket->iTotalNumberOfUniqueArticles <= 0) {
            $bAllowAccess = false;
            if ($bRedirectToPreviousPermittedStep) {
                $oBasketStep = &TdbShopOrderStep::GetStep('basket');
                $this->JumpToStep($oBasketStep);
            }
        }

        return $bAllowAccess;
    }

    /**
     * method is called from the init method of the calling module. here you can check
     * if the step may be viewed, and redirect to another step if the user does not have permission.
     */
    public function Init()
    {
        parent::Init();
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser->IsLoggedIn()) {
            $oBasket = TShopBasket::GetInstance();
            $oBasket->aCompletedOrderStepList[$this->fieldSystemname] = true;
        }

        $this->bShowShippingAddressInput = false;
        if (array_key_exists(self::SESSION_STATE_NAME, $_SESSION)) {
            if ('true' == $_SESSION[self::SESSION_STATE_NAME]) {
                $this->bShowShippingAddressInput = true;
            }
        }

        // check if the parameter is passed via get/post
        $oGlobal = TGlobal::instance();
        if ($oGlobal->UserDataExists('bShowShippingAddressInput')) {
            $bShowShippingAddressInput = $oGlobal->GetUserData('bShowShippingAddressInput');
            if ('1' == $bShowShippingAddressInput) {
                $this->bShowShippingAddressInput = true;
                $_SESSION[self::SESSION_STATE_NAME] = 'true';
            } else {
                $this->bShowShippingAddressInput = false;
                $_SESSION[self::SESSION_STATE_NAME] = 'false';
            }
        }
    }

    protected function SetShowShippingAddressInputState($bState)
    {
        $this->bShowShippingAddressInput = $bState;
        $_SESSION[self::SESSION_STATE_NAME] = $this->bShowShippingAddressInput;

        $oUser = TdbDataExtranetUser::GetInstance();
        if (false == $bState) {
            // and set shipping address of user = to billing address
            $oUser->ShipToBillingAddress(true);
        } else {
            // pick a shipping address not equal to the billing address
            $oUser->ShipToAddressOtherThanBillingAddress();
        }
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
    protected function &GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = &parent::GetAdditionalViewVariables($sViewName, $sViewType);
        $oUser = TdbDataExtranetUser::GetInstance();
        $aViewVariables['oUser'] = &$oUser;
        $oAdrBilling = $oUser->GetBillingAddress();
        $aViewVariables['oAdrBilling'] = &$oAdrBilling;

        $oAdrShipping = $oUser->GetShippingAddress();
        $aViewVariables['oAdrShipping'] = &$oAdrShipping;
        $aViewVariables['oExtranetConfig'] = &TdbDataExtranet::GetInstance();

        $aAddress = array();
        $oAddressList = &$oUser->GetUserAddresses();
        $aViewVariables['oAddressList'] = &$oAddressList;

        $oGlobal = TGlobal::instance();

        if (!$oGlobal->UserDataExists(self::URL_NAME_NEWSLETTER_SIGNUP)) {
            $aViewVariables['bSignupNewsletter'] = true;
        } else {
            $aViewVariables['bSignupNewsletter'] = $oGlobal->GetUserData(self::URL_NAME_NEWSLETTER_SIGNUP);
        }

        $aViewVariables['bShowShippingAddressInput'] = $this->bShowShippingAddressInput;

        // info is written into the session in MTExtranetCore::UpdateUserAddress when the user data passed is invalid
        if (array_key_exists(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING, $_SESSION)) {
            $aViewVariables['aAdr'.TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING] = $_SESSION[TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING];
            unset($_SESSION[TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING]);
        }
        if (array_key_exists(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING, $_SESSION)) {
            $aViewVariables['aAdr'.TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING] = $_SESSION[TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING];
            unset($_SESSION[TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING]);
        }

        return $aViewVariables;
    }

    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $externalFunctions = parent::AllowedMethods();
        $externalFunctions[] = 'ShowShippingAddressInput';
        $externalFunctions[] = 'HideShippingAddressInput';

        $externalFunctions[] = 'UpdateUser';
        $externalFunctions[] = 'Register';
        $externalFunctions[] = 'UpdateUserAddress';
        $externalFunctions[] = 'Login';

        $externalFunctions[] = 'SelectBillingAddress';
        $externalFunctions[] = 'SelectShippingAddress';
        $externalFunctions[] = 'DeleteShippingAddress';

        return $externalFunctions;
    }

    public function UpdateUser()
    {
        $this->CallExtranetModuleMethod('UpdateUser');
    }

    public function Register()
    {
        $this->CallExtranetModuleMethod('Register');
    }

    public function UpdateUserAddress()
    {
        $this->CallExtranetModuleMethod('UpdateUserAddress');
    }

    public function Login()
    {
        $this->CallExtranetModuleMethod('Login');
    }

    public function SelectBillingAddress()
    {
        $this->CallExtranetModuleMethod('SelectBillingAddress');
    }

    public function SelectShippingAddress()
    {
        $this->CallExtranetModuleMethod('SelectShippingAddress');
    }

    public function DeleteShippingAddress()
    {
        $this->CallExtranetModuleMethod('DeleteShippingAddress');
    }

    protected function CallExtranetModuleMethod($sMethodName, $aParameter = array())
    {
        $oExtranetConfig = &TdbDataExtranet::GetInstance();
        /** @var MTExtranet $oExtranet */
        $oExtranet = TTools::GetModuleObject('MTExtranet', 'standard', array(), $oExtranetConfig->fieldExtranetSpotName);
        $oExtranet->SetPreventRedirects(true);

        return $oExtranet->_CallMethod($sMethodName, $aParameter);
    }

    /**
     * set state variable to show shipping address input. method returns false, so that
     * the same step is returned.
     */
    public function ShowShippingAddressInput()
    {
        $this->SetShowShippingAddressInputState(true);
        $this->ProcessStep(true);
    }

    /**
     * set state variable to show shipping address input. method returns false, so that
     * the same step is returned.
     */
    public function HideShippingAddressInput()
    {
        $this->SetShowShippingAddressInputState(false);
        $this->ProcessStep(true);
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * we get here only if this is a user who wants to shop without registering
     * so we make sure to update the user data here
     *
     * @param bool $bDoNotValidate - set to true, if you want to skip the validation
     *
     * @return bool
     */
    protected function ProcessStep($bDoNotValidate = false)
    {
        $bContinue = false;
        $oGlobal = TGlobal::instance();

        $oUser = TdbDataExtranetUser::GetInstance();
        if (is_null($oUser->id)) {
            $aData = $oGlobal->GetUserData('aUser');
            if (is_array($aData)) {
                // password is a required field... but since we do not ask for a password yet, we have to simulate its presence
                if (!array_key_exists('password', $aData) || empty($aData['password'])) {
                    $aData['password'] = '-';
                }
                $oUser->LoadFromRowProtected($aData);
                if (!$bDoNotValidate) {
                    $bContinue = $oUser->ValidateData(TdbDataExtranetUser::MSG_FORM_FIELD);
                }
            } else {
                $bContinue = true;
            }
            // we need to update the billing address as well...
            $tmpAdr = $oUser->GetBillingAddress(true); // resets the billing address
            if ($this->bShowShippingAddressInput && $oGlobal->UserDataExists(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING)) {
                $aShippingData = $oGlobal->GetUserData(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
                $oUser->UpdateShippingAddress($aShippingData);
                if ($oUser->ShipToBillingAddress()) {
                    $this->SetShowShippingAddressInputState(false);
                }
                $oShippingAddress = $oUser->GetShippingAddress();
                $bContinue = ($oShippingAddress->ValidateData(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING) && $bContinue);
            } elseif (!$this->bShowShippingAddressInput) {
                // make sure to reset shipping address if we are shipping to the billing address
                $oUser->ShipToBillingAddress(true);
            }
            if ($bContinue && is_array($aData) && array_key_exists('newsletter', $aData)) {
                $this->ProcessNewsletterSignup();
            }
        }

        if ($bContinue) {
            // signup to the newsletter... if requested
            if ($oGlobal->GetUserData(self::URL_NAME_NEWSLETTER_SIGNUP)) {
                $activePortal = $this->getPortalDomainService()->getActivePortal();
                $oSignupUser = TdbPkgNewsletterUser::GetNewInstance();
                $aRow = array('email' => $oUser->fieldName, 'data_extranet_salutation_id' => $oUser->fieldDataExtranetSalutationId, 'lastname' => $oUser->fieldLastname, 'firstname' => $oUser->fieldFirstname, 'signup_date' => date('Y-m-d H:i:s'), 'data_extranet_user_id' => $oUser->id, 'cms_portal_id' => $activePortal->id, 'optin' => '1', 'optin_date' => date('Y-m-d H:i:s'));
                $oSignupUser->LoadFromRowProtected($aRow);
                if (!$oSignupUser->EMailAlreadyRegistered()) {
                    $oSignupUser->AllowEditByAll(true);
                    $oSignupUser->Save();
                }
            }
        }

        return $bContinue;
    }

    /**
     * called by the ExecuteStep Method - place any logic for the standard proccessing of this step here
     * return false if any errors occure (returns the user to the current step for corrections).
     *
     * @return bool
     */
    protected function ProcessNewsletterSignup()
    {
        $this->LoadNewsletterSignup();
        $bContinue = true;
        $oMsgManager = TCMSMessageManager::GetInstance();
        // validate data...
        $aRequiredFields = $this->oNewsletterSignup->GetRequiredFields();
        if (!is_array($this->oNewsletterSignup->sqlData)) {
            $this->oNewsletterSignup->sqlData = array();
        }
        foreach ($aRequiredFields as $sFieldName) {
            $sVal = '';
            if (array_key_exists($sFieldName, $this->oNewsletterSignup->sqlData)) {
                $sVal = trim($this->oNewsletterSignup->sqlData[$sFieldName]);
            }
            if (empty($sVal)) {
                $oMsgManager->AddMessage(self::INPUT_DATA_NAME.'-'.$sFieldName, 'ERROR-USER-REQUIRED-FIELD-MISSING');
                $bContinue = false;
            } else {
                $this->oNewsletterSignup->sqlData[$sFieldName] = $sVal;
            }
        }

        // check format for email field
        if (!empty($this->oNewsletterSignup->fieldEmail) && !TTools::IsValidEMail($this->oNewsletterSignup->fieldEmail)) {
            $oMsgManager->AddMessage(self::INPUT_DATA_NAME.'-email', 'ERROR-E-MAIL-INVALID-INPUT');
            $bContinue = false;
        }

        if ($bContinue) {
            // check email
            if ($this->oNewsletterSignup->EMailAlreadyRegistered()) {
                // someone else has this email...
                $oMsgManager->AddMessage(self::INPUT_DATA_NAME.'-'.$sFieldName, 'ERROR-E-MAIL-ALREADY-REGISTERED');
                $bContinue = false;
            }
        }

        if ($bContinue) {
            // save!
            $this->oNewsletterSignup->AllowEditByAll(true);
            $this->oNewsletterSignup->Save();
            if (empty($this->oNewsletterSignup->sqlData['optin'])) {
                $this->oNewsletterSignup->SendDoubleOptInEMail();
            }
        }

        return $bContinue;
    }

    /**
     * lazzy load newsletter object.
     *
     * @return TdbPkgNewsletterUser
     */
    protected function &LoadNewsletterSignup()
    {
        if (is_null($this->oNewsletterSignup)) {
            $this->oNewsletterSignup = TdbPkgNewsletterUser::GetNewInstance();
            $oNewslettter = &TdbPkgNewsletterUser::GetInstanceForActiveUser();
            if (!is_null($oNewslettter)) {
                $this->oNewsletterSignup = $oNewslettter;
            }

            $oGlobal = TGlobal::instance();
            if ($oGlobal->UserDataExists(self::INPUT_DATA_NAME)) {
                $aData = $oGlobal->GetUserData(self::INPUT_DATA_NAME);
                if (is_array($aData)) {
                    //            $aData['id'] = $this->oNewsletterSignup->id;
                    $aData = $this->CreateNewsletterArry($aData);
                    $this->oNewsletterSignup->LoadFromRowProtected($aData);
                    if ('1' == $aData['optin']) {
                        $this->oNewsletterSignup->sqlData['optin'] = '1';
                        $this->oNewsletterSignup->sqlData['optin_date'] = date('Y-m-d H:i:s');
                        $this->oNewsletterSignup->sqlData['signup_date'] = date('Y-m-d H:i:s');
                    }
                }
            }
        }

        return $this->oNewsletterSignup;
    }

    protected function CreateNewsletterArry($aData)
    {
        $aNewData = array();
        $aNewData['data_extranet_salutation_id'] = $aData['data_extranet_salutation_id'];
        $aNewData['id'] = null;
        $aNewData['email'] = $aData['name'];
        $aNewData['firstname'] = $aData['firstname'];
        $aNewData['lastname'] = $aData['lastname'];
        $oGlobal = TGlobal::instance();
        $sRegister = $oGlobal->GetUserData('Register');
        if (!empty($sRegister)) {
            $aNewData['optin'] = '1';
        } else {
            $aNewData['optin'] = '0';
        }

        return $aNewData;
    }

    /**
     * @return PortalDomainServiceInterface
     */
    private function getPortalDomainService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }
}
