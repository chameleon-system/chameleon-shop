<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\AmazonDataConverter;

use ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter;
use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class ConvertAddressFromAmazonToLocalTest extends AbstractAmazonPayment
{
    /**
     * @test
     */
    public function it_converts_partial_amazon_address_to_local()
    {
        $sourceAddress = array(
            'City' => 'Freiburg',
            'PostalCode' => '79098',
            'CountryCode' => 'de',
        );
        $expectedAddress = array(
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'data_country_id' => '1',
        );
        /** @var \PHPUnit_Framework_MockObject_MockObject|AmazonDataConverter $amazonConverter */
        $amazonConverter = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter')->setMethods(
            array('getCountryIdFromAmazonCountryCode')
        )->getMock();
        $amazonConverter->expects($this->any())->method('getCountryIdFromAmazonCountryCode')->will(
            $this->returnValue('1')
        );

        $convertedAddress = $amazonConverter->convertAddressFromAmazonToLocal($sourceAddress, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);

        $this->assertEquals($expectedAddress, $convertedAddress);
    }

    /**
     * @test
     */
    public function it_converts_full_amazon_address_to_local()
    {
        $sourceAddress = array(
            'Name' => 'Mr. Dev',
            'AddressLine1' => 'ESONO AG',
            'AddressLine2' => 'Grünwälderstr. 10-14',
            'AddressLine3' => '2 OG',
            'City' => 'Freiburg',
            'County' => '',
            'District' => '',
            'StateOrRegion' => '',
            'PostalCode' => '79098',
            'CountryCode' => 'de',
            'Phone' => '0761 15 18 28 0',
        );
        $expectedAddress = array(
            'lastname' => 'Mr. Dev',
            'company' => 'ESONO AG',
            'street' => 'Grünwälderstr. 10-14',
            'address_additional_info' => '2 OG',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'data_country_id' => '1',
            'telefon' => '0761 15 18 28 0',
        );
        $amazonPayment = new AmazonPayment($this->getConfig());

        /** @var \PHPUnit_Framework_MockObject_MockObject|AmazonDataConverter $amazonConverter */
        $amazonConverter = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonDataConverter')->setMethods(
            array('getCountryIdFromAmazonCountryCode')
        )->getMock();
        $amazonConverter->expects($this->any())->method('getCountryIdFromAmazonCountryCode')->will(
            $this->returnValue('1')
        );

        $convertedAddress = $amazonConverter->convertAddressFromAmazonToLocal($sourceAddress, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
        $this->assertEquals($expectedAddress, $convertedAddress);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_fails_on_invalid_address()
    {
        $sourceData = array('foo' => 'bar');
        $amazonPayment = new AmazonDataConverter();

        $amazonPayment->convertAddressFromAmazonToLocal($sourceData, AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
    }

    public function test_get_local_from_amazon_address_lines()
    {
        // line1 = company
        // line2 = street+nr
        $sourceList = array(
            array(
                'source' => array(
                    'AddressLine1' => 'ESONO AG',
                    'AddressLine2' => 'Grünwälderstr. 10-14',
                    'AddressLine3' => '2 OG',
                ),
                'target' => array(
                    'company' => 'ESONO AG',
                    'street' => 'Grünwälderstr. 10-14',
                    'address_additional_info' => '2 OG',
                ),
            ),
            array(
                'source' => array(
                    'AddressLine1' => '',
                    'AddressLine2' => 'Grünwälderstr. 10-14',
                    'AddressLine3' => '2 OG',
                ),
                'target' => array(
                    'company' => '',
                    'street' => 'Grünwälderstr. 10-14',
                    'address_additional_info' => '2 OG',
                ),
            ),
            array(
                'source' => array(
                    'AddressLine1' => '',
                    'AddressLine2' => 'Grünwälderstr. 10-14',
                    'AddressLine3' => '',
                ),
                'target' => array(
                    'company' => '',
                    'street' => 'Grünwälderstr. 10-14',
                    'address_additional_info' => '',
                ),
            ),
        );

        foreach ($sourceList as $testCase) {
            $amazonPayment = new AmazonDataConverter();
            $converted = $amazonPayment->convertAddressFromAmazonToLocal($testCase['source'], AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING);
            ksort($converted);
            ksort($testCase['target']);
            $this->assertEquals($testCase['target'], $converted);
        }
    }

    public function test_convert_localToOrderAddressBilling()
    {
        $prefixList = array(
            AmazonDataConverter::ORDER_ADDRESS_TYPE_BILLING => array(
                'adr_billing_salutation_id' => '',
                'adr_billing_firstname' => '',
                'adr_billing_streetnr' => '',
                'adr_billing_fax' => '',
                'adr_billing_lastname' => 'Mr. Dev',
                'adr_billing_company' => 'ESONO AG',
                'adr_billing_street' => 'Grünwälderstr. 10-14',
                'adr_billing_additional_info' => '2 OG',
                'adr_billing_city' => 'Freiburg',
                'adr_billing_postalcode' => '79098',
                'adr_billing_country_id' => '1',
                'adr_billing_telefon' => '0761 15 18 28 0',
            ),
            AmazonDataConverter::ORDER_ADDRESS_TYPE_SHIPPING => array(
                'adr_shipping_salutation_id' => '',
                'adr_shipping_firstname' => '',
                'adr_shipping_streetnr' => '',
                'adr_shipping_fax' => '',
                'adr_shipping_lastname' => 'Mr. Dev',
                'adr_shipping_company' => 'ESONO AG',
                'adr_shipping_street' => 'Grünwälderstr. 10-14',
                'adr_shipping_additional_info' => '2 OG',
                'adr_shipping_city' => 'Freiburg',
                'adr_shipping_postalcode' => '79098',
                'adr_shipping_country_id' => '1',
                'adr_shipping_telefon' => '0761 15 18 28 0',
            ),
        );
        $sourceAddress = array(
            'lastname' => 'Mr. Dev',
            'company' => 'ESONO AG',
            'street' => 'Grünwälderstr. 10-14',
            'address_additional_info' => '2 OG',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'data_country_id' => '1',
            'telefon' => '0761 15 18 28 0',
        );
        $converter = new AmazonDataConverter();
        foreach ($prefixList as $type => $expectedAddress) {
            $result = $converter->convertLocalToOrderAddress($type, $sourceAddress);
            $this->assertEquals($expectedAddress, $result);
        }
    }
}
