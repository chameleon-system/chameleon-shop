<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * util class maps amazon data to local representations.
 */

namespace ChameleonSystem\AmazonPaymentBundle;

use InvalidArgumentException;
use OffAmazonPaymentsService_Model_Address;
use TdbDataCountry;

class AmazonDataConverter
{
    const ORDER_ADDRESS_TYPE_BILLING = 1;
    const ORDER_ADDRESS_TYPE_SHIPPING = 2;

    /**
     * @param int   $targetAddressType - one of ORDER_ADDRESS_TYPE_*
     * @param array $localAddress
     *
     * @return array
     */
    public function convertLocalToOrderAddress($targetAddressType, array $localAddress)
    {
        $prefix = (self::ORDER_ADDRESS_TYPE_BILLING === $targetAddressType) ? 'adr_billing_' : 'adr_shipping_';
        $orderAdr = array(
            $prefix.'company' => '',
            $prefix.'salutation_id' => '',
            $prefix.'firstname' => '',
            $prefix.'lastname' => '',
            $prefix.'street' => '',
            $prefix.'streetnr' => '',
            $prefix.'city' => '',
            $prefix.'postalcode' => '',
            $prefix.'country_id' => '',
            $prefix.'telefon' => '',
            $prefix.'fax' => '',
            $prefix.'additional_info' => '',
        );
        foreach ($localAddress as $field => $value) {
            if ('data_country_id' === $field) {
                $field = 'country_id';
            }
            if ('address_additional_info' === $field) {
                $field = 'additional_info';
            }
            $orderAdr[$prefix.$field] = $value;
        }

        return $orderAdr;
    }

    /**
     * @param array $address
     * @param int   $targetAddressType
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function convertAddressFromAmazonToLocal(array $address, $targetAddressType)
    {
        $mapping = array(
            'Name' => 'lastname',
            'AddressLine1' => 'company',
            'AddressLine2' => 'street',
            'AddressLine3' => 'address_additional_info',
            'County' => null,
            'District' => null,
            'StateOrRegion' => null,
            'City' => 'city',
            'PostalCode' => 'postalcode',
            'Phone' => 'telefon',
            'CountryCode' => 'data_country_id',
        );

        foreach (array_keys($address) as $field) {
            if (false === array_key_exists($field, $mapping)) {
                throw new InvalidArgumentException(
                    'invalid field '.$field.' (valid fields are: "'.implode(
                        '", "',
                        array_keys($mapping)
                    ).'")'
                );
            }
        }

        $target = array();
        foreach ($address as $field => $value) {
            if (null === $mapping[$field]) {
                continue;
            }
            if ('CountryCode' !== $field) {
                $target[$mapping[$field]] = $value;
            } else {
                $target[$mapping[$field]] = $this->getCountryIdFromAmazonCountryCode($value, $targetAddressType);
            }
        }
        $target = $this->convertAddressLineData($target, $address);

        return $target;
    }

    /**
     * code from amazon to convert address lines to company postbox and street
     * for countries AT nad DE only.
     *
     * @param array $localAddressData
     * @param array $address
     *
     * @return array
     */
    protected function convertAddressLineData($localAddressData, array $address)
    {
        if ('AT' == $address['CountryCode'] || 'DE' == $address['CountryCode']) {
            $addressLine1 = $address['AddressLine1'];
            $addressLine2 = $address['AddressLine2'];
            $addressLine3 = $address['AddressLine3'];
            $postBox = '';
            $company = '';
            $street = '';
            if ('' != $addressLine3) {
                $street = $addressLine3;
                if (true === is_numeric($addressLine1) ||
                    true === strstr($addressLine1.' '.$addressLine2, 'Packstation')
                ) {
                    $postBox = $addressLine1.' '.$addressLine2;
                } else {
                    $company = $addressLine1.' '.$addressLine2;
                }
            } else {
                if ('' != $addressLine2) {
                    $street = $addressLine2;
                    if (true === is_numeric($addressLine1) || true === strstr($addressLine1, 'Packstation')) {
                        $postBox = $addressLine1;
                    } else {
                        $company = $addressLine1;
                    }
                } else {
                    if ('' != $addressLine1) {
                        $street = $addressLine1;
                    }
                }
            }
            $localAddressData['company'] = $company;
            $localAddressData['address_additional_info'] = $postBox;
            $localAddressData['street'] = $street;
        }

        return $localAddressData;
    }

    /**
     * @param OffAmazonPaymentsService_Model_Address $address
     * @param int                                    $targetAddressType
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function convertAddressFromAmazonObjectToLocal(OffAmazonPaymentsService_Model_Address $address, $targetAddressType)
    {
        return $this->convertAddressFromAmazonToLocal(
            array(
                'Name' => $address->getName(),
                'AddressLine1' => $address->getAddressLine1(),
                'AddressLine2' => $address->getAddressLine2(),
                'AddressLine3' => $address->getAddressLine3(),
                'County' => $address->getCounty(),
                'District' => $address->getDistrict(),
                'StateOrRegion' => $address->getStateOrRegion(),
                'City' => $address->getCity(),
                'PostalCode' => $address->getPostalCode(),
                'Phone' => $address->getPhone(),
                'CountryCode' => $address->getCountryCode(),
            ),
            $targetAddressType
        );
    }

    /**
     * @param string $amazonCountryCode
     * @param int    $targetAddressType
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function getCountryIdFromAmazonCountryCode($amazonCountryCode, $targetAddressType)
    {
        $countryObject = TdbDataCountry::GetInstanceForIsoCode($amazonCountryCode);
        if (null === $countryObject || false === $this->isCountryForAddressTypeActive($countryObject, $targetAddressType)) {
            throw new InvalidArgumentException('country code '.$amazonCountryCode.' is not supported');
        }

        return $countryObject->id;
    }

    /**
     * Check if country is active if address is not a billing address.
     *
     * @param TdbDataCountry $country
     * @param int            $targetAddressType
     *
     * @return bool
     */
    private function isCountryForAddressTypeActive(TdbDataCountry $country, $targetAddressType)
    {
        if (self::ORDER_ADDRESS_TYPE_BILLING === $targetAddressType) {
            return true;
        }

        return $country->isActive();
    }
}
