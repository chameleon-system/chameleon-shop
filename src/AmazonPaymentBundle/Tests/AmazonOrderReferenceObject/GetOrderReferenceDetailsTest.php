<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\AmazonOrderReferenceObject;

use ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

class GetOrderReferenceDetailsTest extends AbstractAmazonOrderReference
{
    public function test_success_before_order_was_confirmed()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('partial.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $orderReferenceDetails = $object->getOrderReferenceDetails(
            array(
                IAmazonOrderReferenceObject::CONSTRAINT_AMOUNT_NOT_SET,
                IAmazonOrderReferenceObject::CONSTRAINT_PAYMENT_PLAN_NOT_SET,
            )
        );

        $this->assertEquals($expectedResponse->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails(), $orderReferenceDetails);
    }

    public function test_success_after_order_was_confirmed()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $orderReferenceDetails = $object->getOrderReferenceDetails();

        $this->assertEquals($expectedResponse->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails(), $orderReferenceDetails);
    }

    public function test_constraint_no_address()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('Constraint-ShippingAddressNotSet.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $exception = null;
        try {
            $object->getOrderReferenceDetails();
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a constraint exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS, $exception->getMessageCode());
    }

    public function test_constraint_no_amount()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('Constraint-AmountNotSet.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $exception = null;
        try {
            $object->getOrderReferenceDetails();
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a constraint exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_NO_AMOUNT_SET, $exception->getMessageCode());
    }

    public function test_constraint_no_payment_method()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('Constraint-PaymentPlanNotSet.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $exception = null;
        try {
            $object->getOrderReferenceDetails();
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a constraint exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_NO_PAYMENT_PLAN_SET, $exception->getMessageCode());
    }

    public function test_constraint_unknown()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('Constraint-Unknown.xml');
        $config = $this->helperGetConfig($expectedResponse, null);

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $exception = null;
        try {
            $object->getOrderReferenceDetails();
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a constraint exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_UNKNOWN_CONSTRAINT, $exception->getMessageCode());
    }

    public function test_api_error()
    {
        $apiException = $this->helperGetAmazonApiException();
        $expectedResponse = AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('Constraint-Unknown.xml');
        $config = $this->helperGetConfig($expectedResponse, $apiException);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $exception = null;
        try {
            $amazonOrderReferenceObject->getOrderReferenceDetails();
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_invalid_constraint_in_ignore_list()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();

        $object = new AmazonOrderReferenceObject($config, 'ORDER-REFERENCE-ID');

        $object->getOrderReferenceDetails(
            array(
                'some-invalid-constraint',
            )
        );
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
        $expectedApiRequestObject->setAmazonOrderReferenceId('ORDER-REFERENCE-ID');
        $expectedApiRequestObject->setSellerId('MY-SELLER-ID');

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('getOrderReferenceDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('getOrderReferenceDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue('MY-SELLER-ID'));

        return $config;
    }
}
