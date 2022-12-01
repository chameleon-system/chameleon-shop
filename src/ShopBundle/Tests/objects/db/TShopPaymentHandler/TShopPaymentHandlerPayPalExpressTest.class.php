<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace esono\pkgshop\Tests\objects\db\TShopPaymentHandler;

use PHPUnit\Framework\TestCase;

class TShopPaymentHandlerPayPalExpressTest extends TestCase
{
    /**
     * @test
     */
    public function it_switches_zip_and_city_when_city_is_numeric_and_zip_is_not()
    {
        $testCases = array(
            array(
                'adr' => array(
                    'postalcode' => 'Freiburg',
                    'city' => '79098',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => 'Freiburg',
                    'city' => ' 79098',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => 'Freiburg',
                    'city' => '079098',
                ),
                'expectedAdr' => array(
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => '123 Freiburg',
                    'city' => '79098',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ),
            ),
        );

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, array($adrBilling, $adrShipping));
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }

    /**
     * @test
     */
    public function it_does_not_switches_zip_and_city_when_zip_is_numeric_and_city_is_not()
    {
        $testCases = array(
            array(
                'adr' => array(
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => ' 79098',
                    'city' => 'Freiburg',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ),
                'expectedAdr' => array(
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ),
            ),
            array(
                'adr' => array(
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ),
            ),
        );

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, array($adrBilling, $adrShipping));
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }

    /**
     * @test
     */
    public function it_does_not_switch_zip_and_city_when_both_are_numeric()
    {
        $testCases = array(
            array(
                'adr' => array(
                    'postalcode' => '79098',
                    'city' => '24234345',
                ),
                'expectedAdr' => array(
                    'postalcode' => '79098',
                    'city' => '24234345',
                ),
            ),
        );

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, array($adrBilling, $adrShipping));
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }
}
