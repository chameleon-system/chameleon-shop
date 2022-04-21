<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopDhlPackstation_DataExtranetUser extends TPkgShopDhlPackstation_DataExtranetUserAutoParent
{
    /**
     * set address as new billing address... will check if the address belongs to the user.
     *
     * @param $sAddressId
     *
     * @return TdbDataExtranetUserAddress|null|false
     */
    public function SetAddressAsBillingAddress($sAddressId)
    {
        $oNewBillingAdr = null;
        if (0 != strcmp($this->fieldDefaultBillingAddressId, $sAddressId)) {
            $oAdr = TdbDataExtranetUserAddress::GetNewInstance();
            if ($oAdr->LoadFromFields(
                array('id' => $sAddressId, 'data_extranet_user_id' => $this->id, 'is_dhl_packstation' => '0')
            )
            ) {
                $this->SaveFieldsFast(array('default_billing_address_id' => $sAddressId));
                $oNewBillingAdr = $this->GetBillingAddress(true);
            }
        } else {
            $oNewBillingAdr = $this->GetBillingAddress();
        }

        return $oNewBillingAdr;
    }

    /**
     * clear field for type change after updating address.
     *
     * @param array $aAddressData
     *
     * @return bool
     */
    public function UpdateShippingAddress($aAddressData)
    {
        $aAddressData = $this->fillPackStationFieldValue($aAddressData);
        $bChangeDHLPackStation = $this->DHLPackStationStatusChanged($aAddressData);
        $bIsDHLPackStation = ('0' == $aAddressData['is_dhl_packstation']) ? false : true;
        $bUpdated = parent::UpdateShippingAddress($aAddressData);
        if (true === $bUpdated && true === $bChangeDHLPackStation) {
            $oShippingAddress = $this->GetShippingAddress();
            if (false === is_null($oShippingAddress)) {
                $oShippingAddress->SetIsDhlPackstation($bIsDHLPackStation, false);
            }
        }

        return $bUpdated;
    }

    /**
     * add pack station field if missing in address data.
     *
     * @param array $aAddressData
     *
     * @return array
     */
    public function fillPackStationFieldValue($aAddressData)
    {
        if (is_array($aAddressData) && false === isset($aAddressData['is_dhl_packstation'])) {
            $aAddressData['is_dhl_packstation'] = 0;
        }

        return $aAddressData;
    }

    /**
     * check if address type status changed.
     *
     * @param array $aAddressData
     *
     * @return bool
     */
    public function DHLPackStationStatusChanged($aAddressData)
    {
        $bChangeDHLPackStation = false;
        $aAddressData = $this->fillPackStationFieldValue($aAddressData);
        $bIsDHLPackStation = ('0' == $aAddressData['is_dhl_packstation']) ? false : true;
        $oOldAddress = clone $this->GetShippingAddress();
        if ($bIsDHLPackStation != $oOldAddress->fieldIsDhlPackstation) {
            $bChangeDHLPackStation = true;
        }

        return $bChangeDHLPackStation;
    }
}
