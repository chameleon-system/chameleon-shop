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

class MTExtranet_PkgShopDhlPackstation extends MTExtranet_PkgShopDhlPackstationAutoParent
{
    /**
     * update the shipping address with the data passed.
     *
     * Set new pack station type before validating
     *
     * @param array $aAddress
     *
     * @return bool
     */
    protected function UpdateShippingAddress($aAddress)
    {
        $oUser = TdbDataExtranetUser::GetInstance();
        if ($oUser->DHLPackStationStatusChanged($aAddress)) {
            $aAddress = $oUser->fillPackStationFieldValue($aAddress);
            if (true === $this->isAddressBillingAddress($aAddress, $oUser)
                && true === $this->isAddressPackstation($aAddress)
            ) {
                $this->getFlashMessage()->addMessage(
                    TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING,
                    'PkgShopDhlPackstation-ERROR-SHIPPING-IS-BILLING-MAY-NOT-BE-PACKSTATION'
                );

                return false;
            }
            $bUpdateOk = $oUser->UpdateShippingAddress($aAddress);
            $oShippingAddress = $oUser->GetShippingAddress();
            if ($oShippingAddress) {
                $bUpdateOk = $oShippingAddress->ValidateData(TdbDataExtranetUserAddress::FORM_DATA_NAME_SHIPPING);
            }
        } else {
            $bUpdateOk = parent::UpdateShippingAddress($aAddress);
        }

        return $bUpdateOk;
    }

    /**
     * @return bool
     */
    private function isAddressBillingAddress(array $addressData, TdbDataExtranetUser $user)
    {
        if (false === isset($addressData['id'])) {
            return false;
        }
        if ($addressData['id'] !== $user->fieldDefaultBillingAddressId) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isAddressPackstation(array $addressData)
    {
        return '1' === $addressData['is_dhl_packstation'];
    }

    /**
     * @return FlashMessageServiceInterface
     */
    private function getFlashMessage()
    {
        return ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.flash_messages');
    }
}
