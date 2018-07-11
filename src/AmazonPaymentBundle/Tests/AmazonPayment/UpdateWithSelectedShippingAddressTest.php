<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\AmazonPayment;

use ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class UpdateWithSelectedShippingAddress extends AbstractAmazonPayment
{
    public function testGuestSuccess()
    {
        // we expect the user to be updated with data from the amazon api

        $amazonUserData = array(
            'City' => 'Freiburg', 'StateOrRegion' => 'BaWü', 'PostalCode' => '79098', 'CountryCode' => 'de',
        );
        $expectedUserData = $this->convertAmazonToLocalAddress($amazonUserData, 1);

        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->once())->method('setAmazonShippingAddress')->with($this->callback(
                function (\TdbDataExtranetUserAddress $userShippingAddress) use ($expectedUserData) {
                    if (false === $userShippingAddress->getIsAmazonShippingAddress()) {
                        return false;
                    }
                    if (null !== $userShippingAddress->id) {
                        return false;
                    }

                    return $userShippingAddress->sqlData == $expectedUserData;
                }
            ));

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->with($this->equalTo(
                array(
                    AmazonOrderReferenceObject::CONSTRAINT_AMOUNT_NOT_SET,
                    AmazonOrderReferenceObject::CONSTRAINT_PAYMENT_PLAN_NOT_SET,
                    AmazonOrderReferenceObject::CONSTRAINT_UNKNOWN, )
            )
        )->will($this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('partial.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()));

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $basket = $this->getMockBuilder('TShopBasket')->getMock();
        $basket->expects($this->once())->method('getAmazonOrderReferenceId')->will($this->returnValue('AMAZON-ORDER-REF-ID'));

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->updateWithSelectedShippingAddress($basket, $user);
    }

    /**
     * amazon api error.
     */
    public function testGuestAmazonRemoteError()
    {
        $exception = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($exception));

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $basket = $this->getMockBuilder('TShopBasket')->getMock();
        $basket->expects($this->once())->method('getAmazonOrderReferenceId')->will($this->returnValue('AMAZON-ORDER-REF-ID'));

        $amazonPayment = new AmazonPayment($config);

        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->never())->method('setAmazonShippingAddress');

        $exception = null;
        try {
            $amazonPayment->updateWithSelectedShippingAddress($basket, $user);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        // expect the user to be unchanged

        $this->assertNotNull($exception, 'an exception should have been thrown');
        // expect an exception that indicates, that there is a technical error
        $this->assertEquals($amazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    /**
     * amazon api ok, but user has not selected an address yet.
     */
    public function testGuestNoAddressSet()
    {
        $exception = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS);

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($exception));

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $basket = $this->getMockBuilder('TShopBasket')->getMock();
        $basket->expects($this->once())->method('getAmazonOrderReferenceId')->will($this->returnValue('AMAZON-ORDER-REF-ID'));

        $amazonPayment = new AmazonPayment($config);

        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->never())->method('setAmazonShippingAddress');

        $exception = null;
        try {
            $amazonPayment->updateWithSelectedShippingAddress($basket, $user);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'an exception should have been thrown');
        // expect an exception that indicates that the user needs to select a shipping address
        $this->assertEquals($amazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS, $exception->getMessageCode());
    }

    /**
     * amazon api ok, but user has selected an address with in an unsupported country.
     */
    public function testGuestInvalidAddress()
    {
        $exception = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_INVALID_ADDRESS);

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($exception));

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $basket = $this->getMockBuilder('TShopBasket')->getMock();
        $basket->expects($this->once())->method('getAmazonOrderReferenceId')->will($this->returnValue('AMAZON-ORDER-REF-ID'));

        $amazonPayment = new AmazonPayment($config);

        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->never())->method('setAmazonShippingAddress');

        $exception = null;
        try {
            $amazonPayment->updateWithSelectedShippingAddress($basket, $user);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'an exception should have been thrown');
        // expect an exception that indicates that the user needs to select a shipping address
        $this->assertEquals($amazonPayment::ERROR_CODE_INVALID_ADDRESS, $exception->getMessageCode());
    }
}
