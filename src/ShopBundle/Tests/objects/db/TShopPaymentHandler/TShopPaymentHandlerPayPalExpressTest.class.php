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
    public function itSwitchesZipAndCityWhenCityIsNumericAndZipIsNot()
    {
        $testCases = [
            [
                'adr' => [
                    'postalcode' => 'Freiburg',
                    'city' => '79098',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => 'Freiburg',
                    'city' => ' 79098',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => 'Freiburg',
                    'city' => '079098',
                ],
                'expectedAdr' => [
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => '123 Freiburg',
                    'city' => '79098',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ],
            ],
        ];

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, [$adrBilling, $adrShipping]);
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }

    /**
     * @test
     */
    public function itDoesNotSwitchesZipAndCityWhenZipIsNumericAndCityIsNot()
    {
        $testCases = [
            [
                'adr' => [
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => ' 79098',
                    'city' => 'Freiburg',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ],
                'expectedAdr' => [
                    'postalcode' => '079098',
                    'city' => 'Freiburg',
                ],
            ],
            [
                'adr' => [
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => '123 Freiburg',
                ],
            ],
        ];

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, [$adrBilling, $adrShipping]);
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }

    /**
     * @test
     */
    public function itDoesNotSwitchZipAndCityWhenBothAreNumeric()
    {
        $testCases = [
            [
                'adr' => [
                    'postalcode' => '79098',
                    'city' => '24234345',
                ],
                'expectedAdr' => [
                    'postalcode' => '79098',
                    'city' => '24234345',
                ],
            ],
        ];

        $reflectionMethod = new \ReflectionMethod('TShopPaymentHandlerPayPalExpress', 'postProcessBillingAndShippingAddress');
        $reflectionMethod->setAccessible(true);
        $object = new \TShopPaymentHandlerPayPalExpress();

        foreach ($testCases as $testCase) {
            $adrBilling = $testCase['adr'];
            $adrShipping = $testCase['adr'];
            $reflectionMethod->invokeArgs($object, [$adrBilling, $adrShipping]);
            $this->assertEquals($testCase['expectedAdr'], $adrBilling, 'billing does not match');
            $this->assertEquals($testCase['expectedAdr'], $adrShipping, 'shipping does not match');
        }
    }
}
