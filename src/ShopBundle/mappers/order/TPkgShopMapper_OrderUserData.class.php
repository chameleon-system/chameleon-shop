<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopMapper_OrderUserData extends AbstractViewMapper
{
    public const ADDRESS_TYPE_BILLING = 1;

    public const ADDRESS_TYPE_SHIPPING = 2;

    /**
     * {@inheritdoc}
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements): void
    {
        $oRequirements->NeedsSourceObject('oObject', 'TdbShopOrder', null, true);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(IMapperVisitorRestricted $oVisitor, $bCachingEnabled, IMapperCacheTriggerRestricted $oCacheTriggerManager): void
    {
        /** @var $oOrder TdbShopOrder */
        $oOrder = $oVisitor->GetSourceObject('oObject');

        if (null !== $oOrder) {
            if ($bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oOrder->table, $oOrder->id);
            }
            $oVisitor->SetMappedValue('aBillingAddress', $this->getAddress($oOrder, self::ADDRESS_TYPE_BILLING, $oCacheTriggerManager, $bCachingEnabled));
            $oVisitor->SetMappedValue('aShippingAddress', $this->getAddress($oOrder, self::ADDRESS_TYPE_SHIPPING, $oCacheTriggerManager, $bCachingEnabled));
            $oVisitor->SetMappedValue('bShipToBillingAddress', $oOrder->fieldAdrShippingUseBilling);
        }
    }

    /**
     * get the value map for one address type can be defined by using the class constants.
     *
     * @param int $iAddressType use constants of the class to define the type to be fetched from
     * @param bool $bCachingEnabled
     *
     * @return array
     */
    protected function getAddress(TdbShopOrder $oOrder, $iAddressType, IMapperCacheTriggerRestricted $oCacheTriggerManager, $bCachingEnabled)
    {
        $aAddress = [];

        if (self::ADDRESS_TYPE_BILLING === $iAddressType) {
            $oSalutation = $oOrder->GetFieldAdrBillingSalutation();
            if ($oSalutation && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oSalutation->table, $oSalutation->id);
            }
            $aAddress['sSalutation'] = (null !== $oSalutation) ? ($oSalutation->GetName()) : ('');

            $aAddress['sCompany'] = '';
            if (property_exists($oOrder, 'fieldAdrBillingCompany')) {
                $aAddress['sCompany'] = $oOrder->fieldAdrBillingCompany;
            }
            $aAddress['sFirstName'] = $oOrder->fieldAdrBillingFirstname;
            $aAddress['sLastName'] = $oOrder->fieldAdrBillingLastname;
            $aAddress['sAdditionalInfo'] = $oOrder->fieldAdrBillingAdditionalInfo;
            $aAddress['sAddressStreet'] = $oOrder->fieldAdrBillingStreet;
            $aAddress['sAddressStreetNr'] = $oOrder->fieldAdrBillingStreetnr;
            $aAddress['sAddressZip'] = $oOrder->fieldAdrBillingPostalcode;
            $aAddress['sAddressCity'] = $oOrder->fieldAdrBillingCity;
            $aAddress['sAddressTelephone'] = $oOrder->fieldAdrBillingTelefon;
            $aAddress['sAddressFax'] = $oOrder->fieldAdrBillingFax;

            $oCountry = $oOrder->GetFieldAdrBillingCountry();
            if (null !== $oCountry) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oCountry->table, $oCountry->id);
                }
                $aAddress['sAddressCountry'] = (null !== $oCountry) ? ($oCountry->GetName()) : ('');
            }
        } elseif (self::ADDRESS_TYPE_SHIPPING === $iAddressType) {
            $oSalutation = $oOrder->GetFieldAdrShippingSalutation();
            if ($oSalutation && $bCachingEnabled) {
                $oCacheTriggerManager->addTrigger($oSalutation->table, $oSalutation->id);
            }
            $aAddress['sSalutation'] = (null !== $oSalutation) ? ($oSalutation->GetName()) : ('');

            $aAddress['sCompany'] = '';
            if (property_exists($oOrder, 'fieldAdrShippingCompany')) {
                $aAddress['sCompany'] = $oOrder->fieldAdrShippingCompany;
            }
            $aAddress['sFirstName'] = $oOrder->fieldAdrShippingFirstname;
            $aAddress['sLastName'] = $oOrder->fieldAdrShippingLastname;
            $aAddress['sAdditionalInfo'] = $oOrder->fieldAdrShippingAdditionalInfo;
            $aAddress['sAddressStreet'] = $oOrder->fieldAdrShippingStreet;
            $aAddress['sAddressStreetNr'] = $oOrder->fieldAdrShippingStreetnr;
            $aAddress['sAddressZip'] = $oOrder->fieldAdrShippingPostalcode;
            $aAddress['sAddressCity'] = $oOrder->fieldAdrShippingCity;
            $aAddress['sAddressTelephone'] = $oOrder->fieldAdrShippingTelefon;
            $aAddress['sAddressFax'] = $oOrder->fieldAdrShippingFax;

            $oCountry = $oOrder->GetFieldAdrShippingCountry();
            if (null !== $oCountry) {
                if ($bCachingEnabled) {
                    $oCacheTriggerManager->addTrigger($oCountry->table, $oCountry->id);
                }
                $aAddress['sAddressCountry'] = (null !== $oCountry) ? ($oCountry->GetName()) : ('');
            }
        }

        return $aAddress;
    }
}
