<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopDhlPackstation_DataExtranetUserAddress extends TPkgShopDhlPackstation_DataExtranetUserAddressAutoParent
{
    /**
     * @param bool $bIsPackstation
     * @param bool $bSave set to false if you not want to save cleard fiel values
     *
     * @return void
     */
    public function SetIsDhlPackstation($bIsPackstation, $bSave = true)
    {
        $aData = $this->sqlData;
        if ($bIsPackstation) {
            $aClear = ['company', 'address_additional_info', 'streetnr'];
        } else {
            $aClear = ['address_additional_info', 'streetnr', 'street'];
        }
        foreach ($aClear as $sClearField) {
            if (array_key_exists($sClearField, $aData)) {
                $aData[$sClearField] = '';
            }
        }
        $aData['is_dhl_packstation'] = ($bIsPackstation) ? ('1') : ('0');

        $this->LoadFromRow($aData);
        if (true === $bSave && !empty($this->id)) {
            $this->Save();
        }
    }

    /**
     * return array with required fields.
     *
     * @return string[]
     */
    public function GetRequiredFields()
    {
        $aRequiredFields = parent::GetRequiredFields();
        if ($this->fieldIsDhlPackstation) {
            $aRequiredFields[] = 'address_additional_info';
            $sKey = array_search('telefon', $aRequiredFields);
            if (false !== $sKey) {
                unset($aRequiredFields[$sKey]);
            }
            $sKey = array_search('fax', $aRequiredFields);
            if (false !== $sKey) {
                unset($aRequiredFields[$sKey]);
            }
        }

        return $aRequiredFields;
    }
}
