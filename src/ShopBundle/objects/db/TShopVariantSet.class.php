<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TShopVariantSet extends TAdbShopVariantSet
{
    /**
     * return an array of fields that may be edited for variants of this set.
     *
     * @return array
     */
    public function GetChangableFieldNames()
    {
        $aFieldNames = $this->GetFromInternalCache('aChangableFieldNames');
        if (is_null($aFieldNames)) {
            $aFieldNames = [];
            $oFields = $this->GetFieldCmsFieldConfList();
            while ($oField = $oFields->Next()) {
                $aFieldNames[] = $oField->fieldName;
            }
            $oFields->GoToStart();
            $this->SetInternalCache('aChangableFieldNames', $aFieldNames);
        }

        return $aFieldNames;
    }

    /**
     * returns true if the field name passed is allowed to be edtited for variants of this set type.
     *
     * @param string $sFieldName
     *
     * @return bool
     */
    public function AllowEditOfField($sFieldName)
    {
        $aFieldsEditable = $this->GetChangableFieldNames();
        $sFieldName = str_replace('`', '', $sFieldName);

        return in_array($sFieldName, $aFieldsEditable);
    }

    /**
     * Anzeigemanager für die Variantenauswahl im Shop.
     *
     * @return TdbShopVariantDisplayHandler|null
     */
    public function GetFieldShopVariantDisplayHandler()
    {
        /** @var TdbShopVariantDisplayHandler|null $oItem */
        $oItem = $this->GetFromInternalCache('oLookupshop_variant_display_handler_id');

        if (is_null($oItem)) {
            $oItem = TdbShopVariantDisplayHandler::GetInstance($this->fieldShopVariantDisplayHandlerId);
            $this->SetInternalCache('oLookupshop_variant_display_handler_id', $oItem);
        }

        return $oItem;
    }

    /**
     * return the variant type with the identifier.
     *
     * @param string $sVariantTypeIdentifier
     *
     * @return TdbShopVariantType|null
     */
    public function GetVariantTypeForIdentifier($sVariantTypeIdentifier)
    {
        /** @var TdbShopVariantType|null $oType */
        $oType = $this->GetFromInternalCache('VariantTypeForIdentifier'.$sVariantTypeIdentifier);

        if (is_null($oType)) {
            $oType = TdbShopVariantType::GetNewInstance();
            if (!$oType->Loadfromfields(['shop_variant_set_id' => $this->id, 'identifier' => $sVariantTypeIdentifier])) {
                $oType = null;
            }
            $this->SetInternalCache('VariantTypeForIdentifier'.$sVariantTypeIdentifier, $oType);
        }

        return $oType;
    }
}
