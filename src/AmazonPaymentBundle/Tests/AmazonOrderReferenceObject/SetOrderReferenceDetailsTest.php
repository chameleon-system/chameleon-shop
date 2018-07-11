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

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

use ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

class SetOrderReferenceDetailsTest extends AbstractAmazonOrderReference
{
    public function test_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::setOrderReferenceDetailsResponse('success.xml', 1234.56);

        $config = $this->helperGetConfig($expectedResponse);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());
        $response = $amazonOrderReferenceObject->setOrderReferenceDetails($this->order);

        $this->assertEquals($expectedResponse->getSetOrderReferenceDetailsResult()->getOrderReferenceDetails(), $response);
    }

    public function test_with_missing_address()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::setOrderReferenceDetailsResponse('constraintNoShippingAddress.xml', 1234.56);
        $config = $this->helperGetConfig($expectedResponse);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $amazonOrderReferenceObject->setOrderReferenceDetails($this->order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS, $exception->getMessageCode());
    }

    public function test_with_missing_payment()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::setOrderReferenceDetailsResponse('constraintPaymentPlanNotSet.xml', 1234.56);
        $config = $this->helperGetConfig($expectedResponse);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $amazonOrderReferenceObject->setOrderReferenceDetails($this->order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_NO_PAYMENT_PLAN_SET, $exception->getMessageCode());
    }

    public function test_with_address_in_invalid_country()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::setOrderReferenceDetailsResponse('unsupportedCountry.xml', 1234.56);
        $config = $this->helperGetConfig($expectedResponse);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $amazonOrderReferenceObject->setOrderReferenceDetails($this->order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_INVALID_ADDRESS, $exception->getMessageCode());
    }

    /**
     * @test
     */
    public function test_with_amazon_api_error()
    {
        $apiException = $this->helperGetAmazonApiException();
        $expectedResponse = AmazonPaymentFixturesFactory::setOrderReferenceDetailsResponse('unsupportedCountry.xml', 1234.56);
        $config = $this->helperGetConfig($expectedResponse, $apiException);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $amazonOrderReferenceObject->setOrderReferenceDetails($this->order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $sellerAttr = new \OffAmazonPaymentsService_Model_SellerOrderAttributes();
        $sellerAttr->setCustomInformation(serialize(array('order_id' => 'TEST-ORDER-ID')));
        $sellerAttr->setSellerOrderId('TEST-ORDERNUMBER');
        $sellerAttr->setStoreName('test store');

        $expectedAtr = new \OffAmazonPaymentsService_Model_OrderReferenceAttributes();
        $expectedAtr->setOrderTotal(new \OffAmazonPaymentsService_Model_OrderTotal());
        $expectedAtr->getOrderTotal()->setAmount(1234.56);
        $expectedAtr->getOrderTotal()->setCurrencyCode('EUR');

        $expectedAtr->setPlatformId('TEST-PLATFORM-ID');
        $expectedAtr->setSellerNote('TEST seller note');
        $expectedAtr->setSellerOrderAttributes($sellerAttr);

        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $expectedApiRequestObject->setAmazonOrderReferenceId('ORDER-REFERENCE-ID');
        $expectedApiRequestObject->setOrderReferenceAttributes($expectedAtr);
        $expectedApiRequestObject->setSellerId('MY-SELLER-ID');

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('setOrderReferenceDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('setOrderReferenceDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getPlatformId')->will($this->returnValue('TEST-PLATFORM-ID'));
        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue('MY-SELLER-ID'));
        $config->expects($this->any())->method('getSellerOrderNote')->will($this->returnValue('TEST seller note'));

        return $config;
    }
}
