<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Interfaces\FlashMessageServiceInterface;

class TPkgShopDhlPackstation_ShopStepUserDataV2 extends TPkgShopDhlPackstation_ShopStepUserDataV2AutoParent
{
    /**
     * define any methods of the class that may be called via get or post.
     *
     * @return array
     */
    public function AllowedMethods()
    {
        $aExternalFunctions = parent::AllowedMethods();
        $aExternalFunctions[] = 'ChangeShippingAddressIsPackstationState';

        return $aExternalFunctions;
    }

    /**
     * switch the current shipping address from being a packstation address to a normal address and back.
     */
    public function ChangeShippingAddressIsPackstationState()
    {
        $this->SetPreventProcessStepMethodFromExecuting(true);

        $bIsDhlPackstation = false;
        $aShipping = $this->GetShippingAddressData();
        if (is_array($aShipping) && array_key_exists('is_dhl_packstation', $aShipping)) {
            $bIsDhlPackstation = ('1' == $aShipping['is_dhl_packstation']) ? (true) : (false);
        }

        $sShippingAdrId = '';
        if (is_array($aShipping) && array_key_exists('id', $aShipping)) {
            $sShippingAdrId = $aShipping['id'];
        }

        $oUser = TdbDataExtranetUser::GetInstance();
        if (!empty($sShippingAdrId)) {
            $oAdr = TdbDataExtranetUserAddress::GetNewInstance();
            if ($oAdr->LoadFromFields(array('data_extranet_user_id' => $oUser->id, 'id' => $sShippingAdrId))) {
                $oAdr->SetIsDhlPackstation($bIsDhlPackstation);
                $this->SetShippingAddressData($oAdr->sqlData);
                $oUser->GetShippingAddress(true);
            }
        } else {
            $oAdr = TdbDataExtranetUserAddress::GetNewInstance();
            $oAdr->LoadFromRowProtected($aShipping);
            $oAdr->SetIsDhlPackstation($bIsDhlPackstation);
            $this->SetShippingAddressData($oAdr->sqlData);
            $oUser->GetShippingAddress(true, true);
        }

        if ($bIsDhlPackstation) {
            $bChangedBillingAddress = false;
            if ('0' != $this->GetShipToBillingAddress()) {
                $this->SetShipToBillingAddress('0');
                $bChangedBillingAddress = true;
            }
            // if the shipping address is also the billing address, then we need to change the billing address
            $sBillingAdrId = '';
            $aBilling = $this->GetBillingAddressData();
            if (is_array($aBilling) && array_key_exists('id', $aBilling)) {
                $sBillingAdrId = $aBilling['id'];
            }
            if (!empty($sBillingAdrId) && !empty($aShipping) && 0 == strcmp($sBillingAdrId, $sShippingAdrId)) {
                $bChangedBillingAddress = true;
                $aNewBilling = array('selectedAddressId' => $this->GetAnotherAddressFromUser($sBillingAdrId, TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING));
                $this->SetBillingAddressData($aNewBilling);
            }

            if ($bChangedBillingAddress) {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING, 'PkgShopDhlPackstation-ERROR-BILLING-MAY-NOT-BE-PACKSTATION');
            }
        }
    }

    /**
     * set state of ship to billing yes/no.
     *
     * if the user wants shipping=billing, then we can only allow this if the new target
     *
     * @param $sShipToBillingAddress
     */
    protected function SetShipToBillingAddress($sShipToBillingAddress)
    {
        $bAllowChange = true;
        if ('1' == $sShipToBillingAddress) {
            if (TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING == $this->AddressUsedAsPrimaryAddress()) {
                if ('1' == $this->GetShippingAddressData('is_dhl_packstation')) {
                    $bAllowChange = false;
                    $oMsgManager = TCMSMessageManager::GetInstance();
                    $oMsgManager->AddMessage(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING, 'PkgShopDhlPackstation-ERROR-BILLING-MAY-NOT-BE-PACKSTATION');
                }
            }
        }
        if ($bAllowChange) {
            parent::SetShipToBillingAddress($sShipToBillingAddress);
        }
    }

    /**
     * returns an alternative address from the users address book other than the reference id passed.
     *
     * @param $sReferenceAddressId
     * @param string $sForAddressForm - billing or shipping (use TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING)
     *
     * @return int|null|string
     */
    protected function GetAnotherAddressFromUser($sReferenceAddressId, $sForAddressForm = TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING)
    {
        $sAlternativeAddressId = 'new';
        if (TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING == $sForAddressForm) {
            $sAlternativeAddressId = parent::GetAnotherAddressFromUser($sReferenceAddressId, $sForAddressForm);
        } else {
            $oUser = TdbDataExtranetUser::GetInstance();
            if ($oUser && !empty($oUser->id)) {
                $oAdrLis = $oUser->GetFieldDataExtranetUserAddressList();
                $oAdrLis->AddFilterString("`data_extranet_user_address`.`is_dhl_packstation` = '0' AND `data_extranet_user_address`.`id` != '".MySqlLegacySupport::getInstance()->real_escape_string($sReferenceAddressId)."'");
                if ($oAdrLis->Length() > 0) {
                    $sAlternativeAddressId = $oAdrLis->Current()->id;
                }
            }
        }

        return $sAlternativeAddressId;
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
        $bValid = parent::ValidateBillingAddress($aAddress);

        if ($bValid) {
            $oAddress = TdbDataExtranetUserAddress::GetNewInstance($aAddress);
            // make sure this is not a packstation
            if ($oAddress->fieldIsDhlPackstation) {
                $bValid = false;
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING, 'PkgShopDhlPackstation-ERROR-BILLING-MAY-NOT-BE-PACKSTATION');
            }
        }

        return $bValid;
    }

    /**
     * return an array of checkbox fields - since the browser does not transfer these when
     * not set, we need a way to reset them.
     *
     * @return array
     */
    protected function GetShippingAddressCheckboxFields()
    {
        return array('is_dhl_packstation');
    }

    /**
     * Set correct billing address if new selected shipping address is packstation.
     *
     * {@inheritdoc}
     */
    public function ChangeSelectedAddress()
    {
        $oUser = self::getExtranetUserProvider()->getActiveUser();
        if ($oUser->IsLoggedIn()) {
            $newShippingAddressId = $this->GetShippingAddressData('selectedAddressId');
            $newBillingAddressId = $this->GetBillingAddressData('selectedAddressId');
            if ('new' !== $newShippingAddressId) {
                $this->updateStepAddressDataForAddressId($newShippingAddressId, $newBillingAddressId);

                if ($newShippingAddressId === $newBillingAddressId &&
                    true === $this->isShipToBillingAddressAndBillingAddressIsPackstation()
                ) {
                    $this->SetShipToBillingAddress('0');
                    $newBillingAddress = $oUser->GetBillingAddress();
                    $this->SetBillingAddressData($newBillingAddress->sqlData);
                    $this->getFlashMessageService()->addMessage(
                        TdbDataExtranetUserAddress::FORM_DATA_NAME_BILLING,
                        'PkgShopDhlPackstation-ERROR-BILLING-MAY-NOT-BE-PACKSTATION'
                    );
                }
            }
        }
        parent::ChangeSelectedAddress();
    }

    /**
     * @return bool
     */
    private function isShipToBillingAddressAndBillingAddressIsPackstation()
    {
        return '1' === $this->GetBillingAddressData('is_dhl_packstation') &&
                1 === (int) $this->GetShipToBillingAddress();
    }

    /**
     * Loads given addresses and updates step address data.
     * Needed for address selection to load the new selected address.
     *
     * @param string $newShippingAddressId
     * @param string $newBillingAddressId
     */
    private function updateStepAddressDataForAddressId($newShippingAddressId, $newBillingAddressId)
    {
        $newBillingAddress = TdbDataExtranetUserAddress::GetNewInstance();
        if (true === $newBillingAddress->Load($newBillingAddressId)) {
            $this->SetBillingAddressData($newBillingAddress->sqlData);
        }
        if ($newShippingAddressId === $newBillingAddressId) {
            $this->SetShippingAddressData($newBillingAddress->sqlData);
        } else {
            $newShippingAddress = TdbDataExtranetUserAddress::GetNewInstance();
            if (true === $newShippingAddress->Load($newShippingAddressId)) {
                $this->SetShippingAddressData($newShippingAddress->sqlData);
            }
        }
    }

    /**
     * @return FlashMessageServiceInterface
     */
    private function getFlashMessageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.flash_messages');
    }
}
