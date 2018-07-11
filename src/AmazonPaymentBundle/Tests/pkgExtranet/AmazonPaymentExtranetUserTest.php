<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\pkgExtranet;

use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class AmazonPaymentExtranetUserTest extends AbstractAmazonPayment
{
    private $originalAddressData = array(
        'withLogin' => array(
            'shippingAddress' => array(
                'id' => 'unittestadrid',
                'company' => 'ESONO AG',
                'firstname' => 'Mr.',
                'lastname' => 'Dev',
                'street' => 'Grünwälderstr',
                'streetnr' => '10-14',
                'city' => 'Freiburg',
                'postalcode' => '79098',
                'telefon' => '07611518280',
                'data_country_id' => '1',
            ),
            'billingAddress' => array(
                'id' => 'unittestadridbill',
                'company' => 'ESONO AGO',
                'firstname' => 'Mister',
                'lastname' => 'Devvv',
                'street' => 'Grünwälderstraaasse',
                'streetnr' => '10-144',
                'city' => 'Freiburger',
                'postalcode' => '79198',
                'telefon' => '17611518280',
                'data_country_id' => '2',
            ),
        ),
        'noLogin' => array(
            'shippingAddress' => array(
                'company' => 'ESONO AG',
                'firstname' => 'Mr.',
                'lastname' => 'Dev',
                'street' => 'Grünwälderstr',
                'streetnr' => '10-14',
                'city' => 'Freiburg',
                'postalcode' => '79098',
                'telefon' => '07611518280',
                'data_country_id' => '1',
            ),
            'billingAddress' => array(
                'company' => 'ESONO AGO',
                'firstname' => 'Mister',
                'lastname' => 'Devvv',
                'street' => 'Grünwälderstraaasse',
                'streetnr' => '10-144',
                'city' => 'Freiburger',
                'postalcode' => '79198',
                'telefon' => '17611518280',
                'data_country_id' => '2',
            ),
        ),
        'noShippingAddress' => array(
            'shippingAddress' => array(
                'data_extranet_user_id' => '',
            ),
            'billingAddress' => array(
                'data_extranet_user_id' => '',
            ),
        ),
    );

    public function testGetShippingAddress()
    {
        /**
         * have a user with a real address set, but user is set to amazon payment user and has an amazon payment shipping address
         * we expect the amazon shipping address to be returned.
         */
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $this->assertTrue($user->GetShippingAddress()->getIsAmazonShippingAddress());
            $this->assertEquals($amazonShippingAddressObject, $user->GetShippingAddress());
            $this->assertTrue($user->ShipToBillingAddress());
        }
    }

    public function testGetShippingAddressFromUserWithBillingNotEqualToShipping()
    {
        /**
         * have a user with a real address set, but user is set to amazon payment user and has an amazon payment shipping address
         * we expect the amazon shipping address to be returned.
         */
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser(true);
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $this->assertTrue($user->ShipToBillingAddress());
            $this->assertTrue($user->GetShippingAddress()->getIsAmazonShippingAddress());
            $this->assertEquals($amazonShippingAddressObject->sqlData, $user->GetShippingAddress()->sqlData);

            $this->assertTrue($user->GetBillingAddress()->getIsAmazonShippingAddress());
            $this->assertEquals($amazonShippingAddressObject->sqlData, $user->GetBillingAddress()->sqlData);
        }
    }

    public function testHelperGetUser()
    {
        $userList = $this->helperGetUser(true);
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            if ('noShippingAddress' === $type) {
                continue;
            }
            $this->assertFalse($user->ShipToBillingAddress());
            $this->assertNotEquals($user->GetShippingAddress()->sqlData, $user->GetBillingAddress()->sqlData);
        }
    }

    private function helperGetUser($setBillingAdr = false)
    {
        $testUserList = array();

        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noShippingAddress';
        $testUserList[$type] = $user;

        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noLogin';
        $user->UpdateShippingAddress($this->originalAddressData[$type]['shippingAddress']);
        if ($setBillingAdr) {
            $user->UpdateBillingAddress($this->originalAddressData[$type]['billingAddress']);
        } else {
            $user->UpdateBillingAddress($this->originalAddressData[$type]['shippingAddress']);
        }
        $testUserList['noLogin'] = $user;

        $type = 'withLogin';
        $originalShippingAddressData = $this->originalAddressData[$type]['shippingAddress'];
        $originalBillingAddressData = $this->originalAddressData[$type]['billingAddress'];
        unset($originalShippingAddressData['id']);
        unset($originalBillingAddressData['id']);
        $aUserData = array(
            'id' => 'unit'.substr(\TTools::GetUUID(), 0, -4),
            'login' => 'tmp__'.\TTools::GetUUID(),
            'confirmed' => '1',
            'default_billing_address_id' => 'unittestadrid',
            'default_shipping_address_id' => 'unittestadrid',
        );
        if ($setBillingAdr) {
            $aUserData['default_billing_address_id'] = 'unittestadridbill';
        }
        /** @var \PHPUnit_Framework_MockObject_MockObject|\TdbDataExtranetUser $userWithLogin */
        $userWithLogin = $this->getMockBuilder('\TdbDataExtranetUser')->setMethods(array('IsLoggedIn', 'IsLoggedInAndConfirmed', 'Save'))->getMock();
        $userWithLogin->expects($this->any())->method('IsLoggedIn')->will($this->returnValue(true));
        $userWithLogin->expects($this->any())->method('IsLoggedInAndConfirmed')->will($this->returnValue(true));
//        /$userWithLogin->expects($this->any())->method('Save');

        $userWithLogin->UpdateShippingAddress($originalShippingAddressData);
        $userWithLogin->GetShippingAddress()->id = 'unittestadrid';
        $userWithLogin->GetShippingAddress()->sqlData['id'] = 'unittestadrid';
        if ($setBillingAdr) {
            $userWithLogin->UpdateBillingAddress($originalBillingAddressData);
            $userWithLogin->GetBillingAddress()->id = 'unittestadridbill';
            $userWithLogin->GetBillingAddress()->sqlData['id'] = 'unittestadridbill';
        } else {
            $userWithLogin->UpdateBillingAddress($originalShippingAddressData);
            $userWithLogin->GetBillingAddress()->id = 'unittestadrid';
            $userWithLogin->GetBillingAddress()->sqlData['id'] = 'unittestadrid';
        }

        $userWithLogin->LoadFromRow($aUserData);
        $testUserList['withLogin'] = $userWithLogin;

        return $testUserList;
    }

    public function testGetBillingAddressOfUserWithShippingEqualToBilling()
    {
        // change the shipping address data and expect the changed data to be returned, the user to remain in the
        // amazon shipping user state, and the returned address not getting an id (ie not being saved)
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $this->assertEquals($amazonShippingAddressObject->sqlData, $user->GetBillingAddress()->sqlData);
            $this->assertTrue($user->GetBillingAddress()->getIsAmazonShippingAddress());
        }
    }

    public function testUpdateShippingAddress()
    {
        // change the shipping address data and expect the changed data to be returned, the user to remain in the
        // amazon shipping user state, and the returned address not getting an id (ie not being saved)
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $newShippingData = array(
                'company' => 'Amazon ESONO AG',
                'firstname' => '',
                'lastname' => 'Amazon Mr. Dev',
                'street' => 'Amazon Grünwälderstr 10-14',
                'streetnr' => '',
                'city' => 'Freiburg',
                'postalcode' => '79098',
                'telefon' => '07611518280',
                'data_country_id' => '1',
            );

            $user->UpdateShippingAddress($newShippingData);

            $expectedShippingAdr = \TdbDataExtranetUserAddress::GetNewInstance($newShippingData);

            $this->assertEquals($expectedShippingAdr->sqlData, $user->GetShippingAddress()->sqlData);
            $this->assertTrue($user->GetShippingAddress()->getIsAmazonShippingAddress());
            $this->assertNull($user->GetShippingAddress()->id);

            $this->assertTrue($user->isAmazonPaymentUser());
        }
    }

    public function testUpdateBillingAddress()
    {
        // change the shipping address data and expect the changed data to be returned, the user to remain in the
        // amazon shipping user state, and the returned address not getting an id (ie not being saved)
        $amazonBillingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonBillingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonBillingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonBillingAddressObject);

            $newBillingData = array(
                'company' => 'Amazon ESONO AG',
                'firstname' => '',
                'lastname' => 'Amazon Mr. Dev',
                'street' => 'Amazon Grünwälderstr 10-14',
                'streetnr' => '',
                'city' => 'Freiburg',
                'postalcode' => '79098',
                'telefon' => '07611518280',
                'data_country_id' => '1',
            );

            $user->UpdateBillingAddress($newBillingData);

            $expectedBillingAdr = \TdbDataExtranetUserAddress::GetNewInstance($newBillingData);

            $this->assertEquals($expectedBillingAdr->sqlData, $user->GetBillingAddress()->sqlData);
            $this->assertTrue($user->GetBillingAddress()->getIsAmazonShippingAddress());
            $this->assertNull($user->GetBillingAddress()->id);

            $this->assertTrue($user->isAmazonPaymentUser());
        }
    }

    public function testSetAmazonPaymentEnabledToFalse()
    {
        /**
         * we have a user with an amazon and a real shipping address. we expect the real shipping address being
         * returned when changing the user to a non-amazon user.
         */
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetShippingAddress()->sqlData);
            $this->assertTrue($user->ShipToBillingAddress());
        }
    }

    public function testSetAmazonPaymentEnabledToFalseAndFalseAgain()
    {
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser();
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetShippingAddress()->sqlData);
            $this->assertTrue($user->ShipToBillingAddress());
        }
    }

    public function testSetAmazonPaymentEnabledNoneAmazonUserSetToTrueThenFalse()
    {
        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noLogin';
        $adrExpected = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateShippingAddress($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateBillingAddress($this->originalAddressData[$type]['shippingAddress']);

        $adr = $user->GetShippingAddress();

        $user->setAmazonPaymentEnabled(true);
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
        $this->assertTrue($user->isAmazonPaymentUser());

        $adr = $user->GetShippingAddress();
        $user->setAmazonPaymentEnabled(false);
        $this->assertFalse($user->isAmazonPaymentUser());
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
        $this->assertTrue($user->ShipToBillingAddress());
    }

    public function testSetAmazonPaymentEnabledNoneAmazonUserSetToFalse()
    {
        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noLogin';
        $adrExpected = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateShippingAddress($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateBillingAddress($this->originalAddressData[$type]['shippingAddress']);

        $adr = $user->GetShippingAddress();

        $user->setAmazonPaymentEnabled(false);
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
        $this->assertFalse($user->isAmazonPaymentUser());
        $this->assertTrue($user->ShipToBillingAddress());
    }

    public function testSetAmazonPaymentEnabledToFalseShippingNotBilling()
    {
        /**
         * we have a user with an amazon and a real shipping address. we expect the real shipping address being
         * returned when changing the user to a non-amazon user.
         */
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser(true);
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetShippingAddress()->sqlData);

            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['billingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetBillingAddress()->sqlData);
            if ('noShippingAddress' === $type) {
                $this->assertTrue($user->ShipToBillingAddress()); // users without a shipping address always have shipping = to billing
            } else {
                $this->assertFalse($user->ShipToBillingAddress());
            }
        }
    }

    public function testSetAmazonPaymentEnabledToFalseAndFalseAgainShippingNotBilling()
    {
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        $userList = $this->helperGetUser(true);
        /** @var \TdbDataExtranetUser $user */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);

            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $user->setAmazonPaymentEnabled(false);
            $this->assertFalse($user->isAmazonPaymentUser());
            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetShippingAddress()->sqlData);

            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['billingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $user->GetBillingAddress()->sqlData);
            if ('noShippingAddress' === $type) {
                $this->assertTrue($user->ShipToBillingAddress()); // users without a shipping address always have shipping = to billing
            } else {
                $this->assertFalse($user->ShipToBillingAddress());
            }
        }
    }

    public function testSetAmazonPaymentEnabledNoneAmazonUserSetToTrueThenFalseShippingNotBilling()
    {
        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noLogin';
        $adrExpected = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateShippingAddress($this->originalAddressData[$type]['shippingAddress']);

        $adr = $user->GetShippingAddress();

        $user->setAmazonPaymentEnabled(true);
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
        $this->assertTrue($user->isAmazonPaymentUser());

        $adr = $user->GetShippingAddress();
        $user->setAmazonPaymentEnabled(false);
        $this->assertFalse($user->isAmazonPaymentUser());
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
    }

    public function testSetAmazonPaymentEnabledNoneAmazonUserSetToFalseShippingNotBilling()
    {
        $aUserData = array('login' => 'bla');
        $user = \TdbDataExtranetUser::GetNewInstance($aUserData);

        $type = 'noLogin';
        $adrExpected = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
        $user->UpdateShippingAddress($this->originalAddressData[$type]['shippingAddress']);

        $adr = $user->GetShippingAddress();

        $user->setAmazonPaymentEnabled(false);
        $this->assertEquals($adrExpected->sqlData, $adr->sqlData);
        $this->assertFalse($user->isAmazonPaymentUser());
    }

    /**
     * @test
     */
    public function it_is_serialized_and_unserialzed()
    {
        // test to make sure the user data survives serialization
        $userList = $this->helperGetUser(true);
        $amazonShippingAddressData = array(
            'company' => '',
            'firstname' => '',
            'lastname' => '',
            'street' => '',
            'streetnr' => '',
            'city' => 'Freiburg',
            'postalcode' => '79098',
            'telefon' => '',
            'data_country_id' => '1',
        );
        $amazonShippingAddressObject = \TdbDataExtranetUserAddress::GetNewInstance($amazonShippingAddressData);

        /** @var $user \TdbDataExtranetUser */
        foreach ($userList as $type => $user) {
            $user->setAmazonPaymentEnabled(true);
            $user->setAmazonShippingAddress($amazonShippingAddressObject);
            $seralized = serialize($user);
            /** @var $unseralizedUser \TdbDataExtranetUser */
            $unseralizedUser = unserialize($seralized);

            $this->assertTrue($unseralizedUser->isAmazonPaymentUser());
            $this->assertEquals($amazonShippingAddressObject, $unseralizedUser->GetShippingAddress());

            // now change to non amazon mode to check if the original billing/shipping survived the serialization

            $unseralizedUser->setAmazonPaymentEnabled(false);
            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['shippingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $unseralizedUser->GetShippingAddress()->sqlData);

            $expectedAddress = \TdbDataExtranetUserAddress::GetNewInstance($this->originalAddressData[$type]['billingAddress']);
            $this->assertEquals($expectedAddress->sqlData, $unseralizedUser->GetBillingAddress()->sqlData);
            if ('noShippingAddress' === $type) {
                $this->assertTrue($unseralizedUser->ShipToBillingAddress()); // users without a shipping address always have shipping = to billing
            } else {
                $this->assertFalse($unseralizedUser->ShipToBillingAddress());
            }
        }
    }
}
