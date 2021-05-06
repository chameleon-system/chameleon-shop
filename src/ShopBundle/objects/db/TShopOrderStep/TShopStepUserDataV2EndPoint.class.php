<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\ExtranetBundle\Interfaces\ExtranetUserProviderInterface;

/**
 * ChangeShipToBillingState - pass a non empty value if you are only changing the state of ship to billing or not
 *                            (this will prevent the method from validating and executing the form.
 *
 *
 * the step can operate in three modes: a) register a new user, b) edit an existing user, c) shop as guest
 * which state is active can be checked via  TShopStepUserDataV2::GetUserMode().
 * you can change the state by calling TShopStepUserDataV2::SetUserMode($sMode)
 * the data is stored in session and the methods static so you can call them from other steps
 *
 * you may also use this step in combination with TShopStepLogin - which will set the mode based on the user selection
 *
 *
 * IMPORTANT: you should always access the class via "TShopStepUserDataV2" (the virtual class entry point)
 * IMPORTANT2: this is a replacement of TShopStepUserDataCore - it is recommended using this newer version
/**/
class TShopStepUserDataV2EndPoint extends TdbShopOrderStep
{
    /**
     * holds the user data passed to the form. default. use the Get* and Set* methods to change/read contents from the array
     * do not access it directly!
     *
     * @var array
     */
    protected $aUserData = array();

    /**
     * set to true if the parameter ChangeShipToBillingState is past with any none-empty value via get/post
     * use IsChangeShipToBillingStateRequest() to check if this is the case.
     *
     * @var bool
     */
    private $bChangeShipToBillingStateRequest = false;

    /**
     * set to true if there is any data send to the step. we need this to handle checkbox fields (they are not submitted
     * at all if they are not set. since it may be necessary to perform some action based on them we need to be able to tell if
     * there was a data request, or if the page was just loaded without data via URL
     * use IsUserDataSubmission() to access the property.
     *
     * @var bool
     */
    private $bUserDataSubmission = false;

    /**
     * if set to true, then the ProcessStep method will not be called.
     * use PreventProcessStepMethodFromExecuting() to access the property.
     *
     * @var bool
     */
    private $bPreventProcessStepMethodFromExecuting = false;

    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $aExternalFunctions = parent::AllowedMethods();
        $aExternalFunctions[] = 'ChangeSelectedAddress';

        return $aExternalFunctions;
    }

    /**
     * returns true if the ProcessStep method is disabled.
     *
     * @return bool
     */
    protected function PreventProcessStepMethodFromExecuting()
    {
        return $this->bPreventProcessStepMethodFromExecuting;
    }

    /**
     * prevent the ProcessStepMethod from executing.
     *
     * @param $bState
     */
    protected function SetPreventProcessStepMethodFromExecuting($bState)
    {
        $this->bPreventProcessStepMethodFromExecuting = $bState;
    }

    /**
     * returns true if the form submit is a change of state from shipping to billing and shipping to a separate address (or vis versa).
     *
     * @return bool
     */
    protected function IsChangeShipToBillingStateRequest()
    {
        return $this->bChangeShipToBillingStateRequest;
    }

    /**
     * return true if there is data submitted to the step (as opposed to a URL call without data).
     *
     * @return bool
     */
    protected function IsUserDataSubmission()
    {
        return $this->bUserDataSubmission;
    }

    /**
     * Set a user mode.
     *
     * @static
     *
     * @param string $sMode - must be one of register, user, or guest
     */
    public static function SetUserMode($sMode)
    {
        $aAllowedModes = array('register', 'user', 'guest');
        if (false == in_array($sMode, $aAllowedModes)) {
            trigger_error('invalid mode requested. please use one of '.implode(', ', $aAllowedModes), E_USER_ERROR);
        }
        $_SESSION['tw_order_umode'] = $sMode;
    }

    /**
     * return the current registration mode... if the user is logged in the mode will be forced to user.
     *
     * @return string
     */
    public static function GetUserMode()
    {
        $umode = 'register';
        if (array_key_exists('tw_order_umode', $_SESSION)) {
            $umode = $_SESSION['tw_order_umode'];
        }
        if ('user' != $umode) {
            // check if the user is signed in... if so, we change the mode to user
            $oUser = self::getExtranetUserProvider()->getActiveUser();
            if ($oUser && $oUser->IsLoggedIn()) {
                $umode = 'user';
                TShopStepUserDataV2::SetUserMode($umode);
            }
        }

        return $umode;
    }

    /**
     * we use the method to populate the state and user data.
     */
    public function Init()
    {
        parent::Init();

        $this->bUserDataSubmission = false;

        $this->InitUserData();

        // primary address should be initialized first
        if (TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING == $this->AddressUsedAsPrimaryAddress()) {
            $this->InitShippingAddress();
            $this->InitBillingAddress();
        } else {
            $this->InitBillingAddress();
            $this->InitShippingAddress();
        }

        // change shipping to billing
        $this->InitChangeShipToBillingState();
    }

    /**
     * initialize user data and set the state of UserDataSubmission to true if submitted
     * if no data was submitted the user data will be loaded by the data of the user object.
     */
    protected function InitUserData()
    {
        $userData = $this->getInputFilterUtil()->getFilteredPostInput('aUser');
        if (null === $userData) {
            $oUser = self::getExtranetUserProvider()->getActiveUser();
            $this->SetUserData($oUser->sqlData);
        } else {
            $this->SetUserData($userData);
            $this->bUserDataSubmission = true;
        }
    }

    /**
     * initialize shipping address data and set the state of UserDataSubmission to true if data was submitted
     * if no data was submitted the shipping address data will be loaded by shipping address of user.
     */
    protected function InitShippingAddress()
    {
        $inputFilterUtil = $this->getInputFilterUtil();
        $shippingAddressData = $inputFilterUtil->getFilteredPostInput(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
        if (null !== $shippingAddressData) {
            $this->SetShippingAddressData($shippingAddressData);
            $this->bUserDataSubmission = true;
        } else {
            $billingAddressData = $inputFilterUtil->getFilteredPostInput(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING);
            if (null !== $billingAddressData && TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) {
                $this->SetShippingAddressData($this->GetBillingAddressData());
            } else {
                $oUser = self::getExtranetUserProvider()->getActiveUser();
                $oShipping = $oUser->GetShippingAddress();
                $aShipping = array();
                if ($oShipping) {
                    $aShipping = $oShipping->sqlData;
                }
                $this->SetShippingAddressData($aShipping);
            }
        }
    }

    /**
     * initialize billing address data and set the state of UserDataSubmission to true if data was submitted
     * if no data was submitted the billing address data will be loaded by billing address of user.
     */
    protected function InitBillingAddress()
    {
        $inputFilterUtil = $this->getInputFilterUtil();
        $billingAddressData = $inputFilterUtil->getFilteredPostInput(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING);
        if (null !== $billingAddressData) {
            $this->SetBillingAddressData($billingAddressData);
            $this->bUserDataSubmission = true;
        } else {
            $shippingAddressData = $inputFilterUtil->getFilteredPostInput(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
            if (null !== $shippingAddressData && TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING == $this->AddressUsedAsPrimaryAddress()) {
                $this->SetBillingAddressData($this->GetShippingAddressData());
            } else {
                $oUser = self::getExtranetUserProvider()->getActiveUser();
                $oBilling = $oUser->GetBillingAddress();
                $aBilling = array();
                if ($oBilling) {
                    $aBilling = $oBilling->sqlData;
                }
                $this->SetBillingAddressData($aBilling);
            }
        }
    }

    /**
     * tries to get the ChangeShipToBillingState - if submitted the bChangeShipToBillingStateRequest will be set to true (value of ChangeShipToBillingState post variable must be 1 or true (!empty check)).
     *
     * the value of bShipToBillingAddress will be used to set the state should be 1 or 0 (true or false)
     *
     * if no data was submitted because the browser will submit nothing if a checkbox is not checked but other post data was submitted
     * we have a submit request so the the SetShipToBillingAddress will be called with value 1 (true) by default if you don't want this - overwrite this method
     */
    protected function InitChangeShipToBillingState()
    {
        $inputFilterUtil = $this->getInputFilterUtil();
        $sChangeShipToBillingState = $inputFilterUtil->getFilteredPostInput('ChangeShipToBillingState');
        if (null === $sChangeShipToBillingState || '' === trim($sChangeShipToBillingState)) {
            return;
        }
        // if the user wants to ship to billing address (or no longer wants to ship to billing address - handle that request here)
        $this->bChangeShipToBillingStateRequest = true;

        // try to get the new state from post data if it exists - otherwise negate the value returned by $this->GetShipToBillingAddress
        $shipToBillingData = $inputFilterUtil->getFilteredPostInput('bShipToBillingAddress');
        if (null === $shipToBillingData) {
            $bShipToBilling = ('1' == $this->GetShipToBillingAddress()) ? (0) : (1);
        } else {
            $bShipToBilling = ('1' == $shipToBillingData) ? (1) : (0);
        }
        if ($bShipToBilling != $this->GetShipToBillingAddress()) {
            $this->SetShipToBillingAddress($bShipToBilling);
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
        $aViewVariables = parent::GetAdditionalViewVariables($sViewName, $sViewType);

        $aUserData = $this->GetUserData();
        if (false == $aUserData) {
            $aUserData = null;
        }
        $aViewVariables['oUserData'] = TdbDataExtranetUser::GetNewInstance($aUserData);

        $aShippingAddress = $this->GetShippingAddressData();
        if (false == $aShippingAddress) {
            $aShippingAddress = null;
        }
        $aViewVariables['oShippingAddress'] = TdbDataExtranetUserAddress::GetNewInstance($aShippingAddress);

        $aBillingAddress = $this->GetBillingAddressData();
        if (false == $aBillingAddress) {
            $aBillingAddress = null;
        }
        $aViewVariables['oBillingAddress'] = TdbDataExtranetUserAddress::GetNewInstance($aBillingAddress);

        $aViewVariables['bShipToBillingAddress'] = $this->GetShipToBillingAddress();

        $aViewVariables['oUser'] = self::getExtranetUserProvider()->getActiveUser();

        $aViewVariables['umode'] = TShopStepUserDataV2::GetUserMode();

        return $aViewVariables;
    }

    /**
     * defines which address (shipping or billing) will be used to sync the profile data (ie. is the primary Addresse).
     *
     * @return string
     */
    protected function AddressUsedAsPrimaryAddress()
    {
        return TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING;
    }

    /**
     * validate user/Address entry using the data passed. if valid, allow user to continue.
     *
     * @return bool
     */
    protected function ProcessStep()
    {
        if ($this->IsChangeShipToBillingStateRequest() || $this->PreventProcessStepMethodFromExecuting()) {
            $bContinue = false;
        } else {
            $bContinue = parent::ProcessStep();

            $bContinue = $this->ValidateStepData() && $bContinue;

            if ($bContinue) {
                $bContinue = $this->UpdateUser($this->GetUserData());
                // if we ship to billing address, we update only the primary address and change the secondary address to match this
                if (false !== $bContinue && '1' == $this->GetShipToBillingAddress()) {
                    $oUser = self::getExtranetUserProvider()->getActiveUser();
                    switch ($this->AddressUsedAsPrimaryAddress()) {
                        case TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING:
                            $this->UpdateShippingAddress($this->GetShippingAddressData());
                            if (false == $oUser->ShipToBillingAddress()) {
                                // we need to set the billing address based on the shipping address
                                if (!empty($oUser->id)) {
                                    $oUser->SetAddressAsBillingAddress($oUser->fieldDefaultShippingAddressId);
                                } else {
                                    $oShippingAddress = $oUser->GetShippingAddress(true);
                                    if (null !== $oShippingAddress) {
                                        $oUser->UpdateBillingAddress($oShippingAddress->sqlData);
                                    }
                                }
                                $oUser->GetBillingAddress(true);
                            }
                            break;
                        default:
                        case TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING:
                            $this->UpdateBillingAddress($this->GetBillingAddressData());
                            // we need to set the billing address based on the shipping address
                            if (!empty($oUser->id)) {
                                $oUser->SetAddressAsShippingAddress($oUser->fieldDefaultBillingAddressId);
                            } else {
                                $oBillingAddress = $oUser->GetBillingAddress(true);
                                if (null !== $oBillingAddress) {
                                    $oUser->UpdateShippingAddress($oBillingAddress->sqlData);
                                }
                            }
                            $oUser->GetShippingAddress(true);
                            break;
                    }
                } else {
                    $this->UpdateBillingAddress($this->GetBillingAddressData());
                    $this->UpdateShippingAddress($this->GetShippingAddressData());
                }
            }
        }

        return $bContinue;
    }

    /**
     * validate the step data... return true on success.... else message will be written to the message manager.
     *
     * @return bool
     */
    protected function ValidateStepData()
    {
        $bContinue = $this->ValidateUser($this->GetUserData());

        // which address do we need to validate? if $this->GetShipToBillingAddress() != 1 then we need to validate both..
        // otherwise one will do...

        if ('1' != $this->GetShipToBillingAddress()) {
            $bContinue = $this->ValidateShippingAddress($this->GetShippingAddressData()) && $bContinue;
            $bContinue = $this->ValidateBillingAddress($this->GetBillingAddressData()) && $bContinue;
        } else {
            switch ($this->AddressUsedAsPrimaryAddress()) {
                case TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING:
                    $bContinue = $this->ValidateShippingAddress($this->GetShippingAddressData()) && $bContinue;
                    break;

                default:
                case TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING:
                    $bContinue = $this->ValidateBillingAddress($this->GetBillingAddressData()) && $bContinue;
                    break;
            }
        }

        return $bContinue;
    }

    /**
     * updates the user data in session / db. if this is a registration request, the user
     * will be registered.
     *
     * @param array $aUserData
     * @return bool
     */
    protected function UpdateUser($aUserData)
    {
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        $oUser->LoadFromRowProtected($aUserData, false);

        switch (TShopStepUserDataV2::GetUserMode()) {
            case 'register':
                if (false === $oUser->Register()) {
                    return false;
                }

                // set the id of the primary address based on the address generated by the user on registration
                if (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) {
                    $aBillingAddress = $this->GetBillingAddressData();
                    $aBillingAddress['id'] = $oUser->fieldDefaultBillingAddressId;
                    $this->SetBillingAddressData($aBillingAddress);
                } else {
                    $aShippingAddress = $this->GetShippingAddressData();
                    $aShippingAddress['id'] = $oUser->fieldDefaultShippingAddressId;
                    $this->SetShippingAddressData($aShippingAddress);
                }

                return true;

            case 'user':
                return $oUser->Save();

            default:
                return true; // this is no validation
        }
    }

    /**
     * update the billing address of the user.
     *
     * @param array $aBillingAddress
     */
    protected function UpdateBillingAddress($aBillingAddress)
    {
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        $oUser->UpdateBillingAddress($aBillingAddress);
    }

    /**
     * update the shipping address of the user.
     *
     * @param array $aShippingAddress
     */
    protected function UpdateShippingAddress($aShippingAddress)
    {
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        $oUser->UpdateShippingAddress($aShippingAddress);
    }

    /**
     * validate the user base data. registration request will also validate user name and password.
     *
     * @param array $aUserData
     *
     * @return bool
     */
    protected function ValidateUser($aUserData)
    {
        $bValid = false;
        $oActiveUser = self::getExtranetUserProvider()->getActiveUser();
        if ($oActiveUser && is_array($oActiveUser->sqlData) && array_key_exists('customer_number', $oActiveUser->sqlData)) {
            $aUserData['customer_number'] = $oActiveUser->sqlData['customer_number'];
        }
        $oUser = TdbDataExtranetUser::GetNewInstance($aUserData);

        $bValid = $oUser->ValidateData();

        if ('register' == TShopStepUserDataV2::GetUserMode()) {
            $bValid = ($oUser->ValidateLoginData($aUserData) && $bValid);
        } elseif ('guest' == TShopStepUserDataV2::GetUserMode()) {
            if (TdbDataExtranet::GetInstance()->fieldLoginIsEmail) {
                $bValid = ($this->validateUserEMail($aUserData['name']) && $bValid);
            } else {
                $bValid = ($this->validateUserEMail($aUserData['email']) && $bValid);
            }
        }

        return $bValid;
    }

    protected function validateUserEMail($mail)
    {
        if (false === TTools::IsValidEMail($mail)) {
            $oMessages = TCMSMessageManager::GetInstance();
            $oMessages->AddMessage(TdbDataExtranetUser::MSG_FORM_FIELD.'-name', 'ERROR-E-MAIL-INVALID-INPUT');

            return false;
        }

        return true;
    }

    /**
     * validate shipping address.
     *
     * @param array $aAddress
     *
     * @return bool
     */
    protected function ValidateShippingAddress($aAddress)
    {
        $oAddress = TdbDataExtranetUserAddress::GetNewInstance($aAddress);

        return $oAddress->ValidateData(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
    }

    /**
     * validate billing address.
     *
     * @param $aAddress
     *
     * @return bool
     */
    protected function ValidateBillingAddress($aAddress)
    {
        $oAddress = TdbDataExtranetUserAddress::GetNewInstance($aAddress);

        return $oAddress->ValidateData(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING);
    }

    /**
     * write user base data (such as login and password... etc) to our user data array.
     *
     * @param $aUserData
     */
    protected function SetUserData($aUserData)
    {
        $this->aUserData['aUser'] = $aUserData;
    }

    /**
     * store shipping address for local access.
     *
     * @param $aAddressData
     */
    protected function SetShippingAddressData($aAddressData)
    {
        $this->aUserData['aShipping'] = $aAddressData;
    }

    /**
     * store billing address for local access.
     *
     * @param $aAddressData
     */
    protected function SetBillingAddressData($aAddressData)
    {
        $this->aUserData['aBilling'] = $aAddressData;
    }

    /**
     * set state of ship to billing yes/no.
     *
     * @param $sShipToBillingAddress
     */
    protected function SetShipToBillingAddress($sShipToBillingAddress)
    {
        $this->aUserData['bShipToBillingAddress'] = $sShipToBillingAddress;
        $_SESSION['shopstepuserdata_bShipToBillingAddress'] = $this->aUserData['bShipToBillingAddress'];
        $oUser = self::getExtranetUserProvider()->getActiveUser();

        // if the user is logged in, we need to update him accordingly
        $bShipToBilling = ('1' == $sShipToBillingAddress) ? (true) : (false);
        if ($oUser && !empty($oUser->id) && $bShipToBilling != $oUser->ShipToBillingAddress() && $oUser->IsLoggedIn()) {
            // we change to ship to billing.... but we have to make sure the correct address is leading
            $sNewPrimaryAddress = '';
            if (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) {
                $sNewPrimaryAddress = $this->GetBillingAddressData('selectedAddressId');
            } else {
                $sNewPrimaryAddress = $this->GetShippingAddressData('selectedAddressId');
            }
            if ($bShipToBilling) {
                // if $sNewPrimaryAddress is empty, we don't have saved the address, yet. So we won't update the user just yet. It will be updated and saved when the step is completed
                if (!empty($sNewPrimaryAddress)) {
                    $oUser->SetAddressAsBillingAddress($sNewPrimaryAddress);
                }
                $oUser->ShipToBillingAddress(true);
            } else {
                // need to pick another billing address
                $sAlternativeFor = (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) ? (TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING) : (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING);
                $sNewAddressId = $this->GetAnotherAddressFromUser($sNewPrimaryAddress, $sAlternativeFor);
                $aAdrData = array();
                $oAdr = TdbDataExtranetUserAddress::GetNewInstance();
                if ($oAdr->LoadFromFields(array('id' => $sNewAddressId, 'data_extranet_user_id' => $oUser->id))) {
                    $aAdrData = $oAdr->sqlData;
                }
                if (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) {
                    $this->SetShippingAddressData($aAdrData);
                } else {
                    $this->SetBillingAddressData($aAdrData);
                }
            }
            //$this->ChangeSelectedAddress();
        }
    }

    /**
     * returns an alternative address from the users address book other than the reference id passed.
     *
     * @param $sReferenceAddressId
     * @param string $sForAddressForm - billing or shipping (use TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING)
     *
     * @return int|string|null
     */
    protected function GetAnotherAddressFromUser($sReferenceAddressId, $sForAddressForm = TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING)
    {
        $sAlternativeAddressId = 'new';
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        if ($oUser && !empty($oUser->id)) {
            $oAdrLis = $oUser->GetFieldDataExtranetUserAddressList();
            $oAdrLis->AddFilterString("`data_extranet_user_address`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($sReferenceAddressId)."'");
            if ($oAdrLis->Length() > 0) {
                $sAlternativeAddressId = $oAdrLis->Current()->id;
            }
        }

        return $sAlternativeAddressId;
    }

    /**
     * return 1 if the user wants to ship to the billing address, else 0.
     *
     * @return int
     */
    protected function GetShipToBillingAddress()
    {
        if (array_key_exists('bShipToBillingAddress', $this->aUserData)) {
            return $this->aUserData['bShipToBillingAddress'];
        }
        if (array_key_exists('shopstepuserdata_bShipToBillingAddress', $_SESSION)) {
            return $_SESSION['shopstepuserdata_bShipToBillingAddress'];
        } else {
            $oUser = self::getExtranetUserProvider()->getActiveUser();

            return $oUser->ShipToBillingAddress() ? (1) : (0);
        }
    }

    /**
     * get user base data. the date will be filtered / update based on the mode.
     *
     * @param string $sReturnThisParameterOnly - if set, then only that field of the address is returned. if that field is not found, we return false
     *
     * @return array|bool
     */
    protected function GetUserData($sReturnThisParameterOnly = null)
    {
        $aReturnVal = false;
        if (array_key_exists('aUser', $this->aUserData)) {
            $aUserData = $this->aUserData['aUser'];
            $oActiveUser = self::getExtranetUserProvider()->getActiveUser();

            // some of the data should be kept if the user has entered it before
            // plus we need additional logic if the user is logged in (prevent a second registration)
            if ($oActiveUser) {
                if (is_array($oActiveUser->sqlData) && array_key_exists('customer_number', $oActiveUser->sqlData)) {
                    $aUserData['customer_number'] = $oActiveUser->sqlData['customer_number'];
                }
                if (!empty($oActiveUser->id)) {
                    $aUserData['id'] = $oActiveUser->id;
                }
            }

            // filter data based on the requested mode
            switch (TShopStepUserDataV2::GetUserMode()) {
                case 'user':
                    $aTmpData = $oActiveUser->sqlData;
                    foreach ($aUserData as $sKey => $sVal) {
                        $aTmpData[$sKey] = $sVal;
                    }
                    $aUserData = $aTmpData;
                    break;
                case 'guest':
                    $aUserData['password'] = TTools::GenerateRandomPassword(20);
                    $aUserData['password2'] = $aUserData['password'];
                    break;
                default:
                case 'register':
                    break;
            }

            $oShop = TdbShop::GetInstance();
            $aUserData['shop_id'] = $oShop->id;

            // add data from the primary address
            $aPrimaryAddress = array();
            if (TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING == $this->AddressUsedAsPrimaryAddress()) {
                $aPrimaryAddress = $this->GetShippingAddressData();
            } else {
                $aPrimaryAddress = $this->GetBillingAddressData();
            }
            $aNotAllowed = array('id', 'name', 'cmsident', 'password', 'session_key', 'login_timestamp', 'login_salt', 'shop_id', 'datecreated', 'tmpconfirmkey', 'confirmed', 'confirmedon', 'reg_email_send');
            foreach ($aPrimaryAddress as $sField => $sValue) {
                if (!in_array($sField, $aNotAllowed)) {
                    $aUserData[$sField] = $sValue;
                }
            }

            $aCheckboxFields = $this->GetUserDataCheckboxFields();
            foreach ($aCheckboxFields as $sFieldName) {
                if (!array_key_exists($sFieldName, $aUserData)) {
                    $aUserData[$sFieldName] = '0';
                }
            }

            if (!is_null($sReturnThisParameterOnly)) {
                if (array_key_exists($sReturnThisParameterOnly, $aUserData)) {
                    $aReturnVal = $aUserData[$sReturnThisParameterOnly];
                }
            } else {
                $aReturnVal = $aUserData;
            }

            return $aReturnVal;
        }

        return $aReturnVal;
    }

    /**
     * return shipping address data from user post.
     *
     * @param string $sReturnThisParameterOnly - if set, then only that field of the address is returned. if that field is not found, we return false
     *
     * @return array|bool
     */
    protected function GetShippingAddressData($sReturnThisParameterOnly = null)
    {
        $aReturnVal = false;
        if (array_key_exists('aShipping', $this->aUserData)) {
            $aAddress = $this->aUserData['aShipping'];
            if (array_key_exists('id', $aAddress) && !array_key_exists('selectedAddressId', $aAddress)) {
                $aAddress['selectedAddressId'] = $aAddress['id'];
            }
            $oActiveUser = self::getExtranetUserProvider()->getActiveUser();
            if ($oActiveUser && !empty($oActiveUser->id)) {
                $aAddress['data_extranet_user_id'] = $oActiveUser->id;
            }
            $aCheckboxFields = $this->GetShippingAddressCheckboxFields();
            foreach ($aCheckboxFields as $sFieldName) {
                if (!array_key_exists($sFieldName, $aAddress)) {
                    $aAddress[$sFieldName] = '0';
                }
            }

            if (!is_null($sReturnThisParameterOnly)) {
                if (array_key_exists($sReturnThisParameterOnly, $aAddress)) {
                    $aReturnVal = $aAddress[$sReturnThisParameterOnly];
                }
            } else {
                $aReturnVal = $aAddress;
            }
        }

        return $aReturnVal;
    }

    /**
     * return billing address data from user post.
     *
     * @param string $sReturnThisParameterOnly - if set, then only that field of the address is returned. if that field is not found, we return false
     *
     * @return array|bool
     */
    protected function GetBillingAddressData($sReturnThisParameterOnly = null)
    {
        $aReturnVal = false;
        if (array_key_exists('aBilling', $this->aUserData)) {
            $aAddress = $this->aUserData['aBilling'];
            if (array_key_exists('id', $aAddress) && !array_key_exists('selectedAddressId', $aAddress)) {
                $aAddress['selectedAddressId'] = $aAddress['id'];
            }
            $oActiveUser = self::getExtranetUserProvider()->getActiveUser();
            if ($oActiveUser && !empty($oActiveUser->id)) {
                $aAddress['data_extranet_user_id'] = $oActiveUser->id;
            }
            $aCheckboxFields = $this->GetBillingAddressCheckboxFields();
            foreach ($aCheckboxFields as $sFieldName) {
                if (!array_key_exists($sFieldName, $aAddress)) {
                    $aAddress[$sFieldName] = '0';
                }
            }
            if (!is_null($sReturnThisParameterOnly)) {
                if (array_key_exists($sReturnThisParameterOnly, $aAddress)) {
                    $aReturnVal = $aAddress[$sReturnThisParameterOnly];
                }
            } else {
                $aReturnVal = $aAddress;
            }
        }

        return $aReturnVal;
    }

    /**
     * the method will update the user with the shipping and billing address IDs passed. calling the
     * method will cause the ProcessStep method from being called.
     */
    public function ChangeSelectedAddress()
    {
        $this->SetPreventProcessStepMethodFromExecuting(true);
        // the method only works if the user is logged in
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        if ($oUser->IsLoggedIn()) {
            $sNewShippingAddressId = $this->GetShippingAddressData('selectedAddressId');
            $sNewBillingAddressId = $this->GetBillingAddressData('selectedAddressId');

            if ('1' == $this->GetShipToBillingAddress()) {
                if (0 != strcmp($sNewBillingAddressId, $sNewShippingAddressId)) {
                    if (TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING == $this->AddressUsedAsPrimaryAddress()) {
                        $sNewShippingAddressId = $sNewBillingAddressId;
                    } else {
                        $sNewBillingAddressId = $sNewShippingAddressId;
                    }
                }
            } else {
                // they were different.. if they are now the same, we should set the new state
                if (0 == strcmp($sNewBillingAddressId, $sNewShippingAddressId)) {
                    $this->SetShipToBillingAddress('1');
                }
            }

            if (!empty($sNewBillingAddressId)) {
                if ('new' != $sNewBillingAddressId) {
                    $oNewBillingAdr = $oUser->SetAddressAsBillingAddress($sNewBillingAddressId);
                    if ($oNewBillingAdr) {
                        $this->SetBillingAddressData($oNewBillingAdr->sqlData);
                    } else {
                        // invalid request...
                        if (false == $this->GetBillingAddressData('selectedAddressId')) {
                            // there was nothing selected... so we get an an alternative
                            $sNewBillingAddressId = $this->GetAnotherAddressFromUser($sNewBillingAddressId, TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING);
                            $oNewBillingAdr = $oUser->SetAddressAsBillingAddress($sNewBillingAddressId);
                            if ($oNewBillingAdr) {
                                $this->SetBillingAddressData($oNewBillingAdr->sqlData);
                            } else {
                                $this->SetBillingAddressData(array('selectedAddressId' => 'new'));
                            }
                        }
                    }
                } else {
                    $this->SetBillingAddressData(array('selectedAddressId' => 'new'));
                }
            }

            if (!empty($sNewShippingAddressId)) {
                if ('new' != $sNewShippingAddressId) {
                    $oNewShippingAdr = $oUser->SetAddressAsShippingAddress($sNewShippingAddressId);
                    if ($oNewShippingAdr) {
                        $this->SetShippingAddressData($oNewShippingAdr->sqlData);
                    } else {
                        // invalid request...
                        if (false == $this->GetShippingAddressData('selectedAddressId')) {
                            // there was nothing selected... so we get an an alternative
                            $sNewShippingAddressId = $this->GetAnotherAddressFromUser($sNewBillingAddressId, TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
                            $oNewShippingAdr = $oUser->SetAddressAsShippingAddress($sNewShippingAddressId);
                            if ($oNewShippingAdr) {
                                $this->SetShippingAddressData($oNewShippingAdr->sqlData);
                            } else {
                                $this->SetShippingAddressData(array('selectedAddressId' => 'new'));
                            }
                        }
                    }
                } else {
                    $this->SetShippingAddressData(array('selectedAddressId' => 'new'));
                }
            }

            // check if shipping != billing AND ShipToBillingAddress() == '1' or the other way around. if that is the case,
            // then we need to adjust ShipToBillingAddress to match the new setting
            if (1 == $this->GetShipToBillingAddress() && $this->GetShippingAddressData('selectedAddressId') != $this->GetBillingAddressData('selectedAddressId')) {
                $this->SetShipToBillingAddress(0);
            } elseif (0 == $this->GetShipToBillingAddress() && $this->GetShippingAddressData('selectedAddressId') == $this->GetBillingAddressData('selectedAddressId')) {
                $this->SetShipToBillingAddress(1);
            }
            TShopBasket::GetInstance()->RecalculateBasket();
        }
    }

    /**
     * return an array of checkbox fields - since the browser does not transfer these when
     * not set, we need a way to reset them.
     *
     * @return array
     */
    protected function GetUserDataCheckboxFields()
    {
        return array();
    }

    /**
     * return an array of checkbox fields - since the browser does not transfer these when
     * not set, we need a way to reset them.
     *
     * @return array
     */
    protected function GetShippingAddressCheckboxFields()
    {
        return array();
    }

    /**
     * return an array of checkbox fields - since the browser does not transfer these when
     * not set, we need a way to reset them.
     *
     * @return array
     */
    protected function GetBillingAddressCheckboxFields()
    {
        return array();
    }

    /**
     * @return ExtranetUserProviderInterface
     */
    protected static function getExtranetUserProvider()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_extranet.extranet_user_provider');
    }
}
