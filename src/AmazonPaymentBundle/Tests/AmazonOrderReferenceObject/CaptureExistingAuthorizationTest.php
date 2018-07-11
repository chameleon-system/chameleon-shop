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

class CaptureExistingAuthorizationTest extends AbstractAmazonOrderReference
{
    public function test_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::capture('success.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $response = $orderRefObject->captureExistingAuthorization(
            $this->order,
            'AMAZON-AUTHORIZATION-ID',
            'LOCAL-CAPTURE-REFERENCE-ID',
            123.45,
            'SOFT-DESCRIPTOR'
        );

        $this->assertEquals($expectedResponse->getCaptureResult()->getCaptureDetails(), $response);
    }

    public function test_declined_AmazonRejected()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::capture('declined-AmazonRejected.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->captureExistingAuthorization(
                $this->order,
                'AMAZON-AUTHORIZATION-ID',
                'LOCAL-CAPTURE-REFERENCE-ID',
                123.45,
                'SOFT-DESCRIPTOR'
            );
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals(AmazonPayment::ERROR_CAPTURE_DECLINED, $exception->getMessageCode());
        $this->assertEquals(
            array(
                'reasonCode' => 'AmazonRejected',
                'reasonDescription' => 'Amazon has rejected the capture. You should only retry the capture if the authorization is in the Open state.',
            ),
            $exception->getAdditionalData()
        );
    }

    public function test_declined_ProcessingFailure()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::capture('declined-ProcessingFailure.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->captureExistingAuthorization(
                $this->order,
                'AMAZON-AUTHORIZATION-ID',
                'LOCAL-CAPTURE-REFERENCE-ID',
                123.45,
                'SOFT-DESCRIPTOR'
            );
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals(AmazonPayment::ERROR_CAPTURE_DECLINED, $exception->getMessageCode());
        $this->assertEquals(
            array(
                'reasonCode' => 'ProcessingFailure',
                'reasonDescription' => 'Amazon could not process the transaction due to an internal processing error. You should only retry the capture if the authorization is in the Open state. Otherwise, you should request a new authorization and then call Capture on it.',
            ),
            $exception->getAdditionalData()
        );
    }

    public function test_api_error()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::capture('success.xml');
        $amazonApiException = $this->helperGetAmazonApiException();
        $config = $this->helperGetConfig($expectedResponse, $amazonApiException);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->captureExistingAuthorization(
                $this->order,
                'AMAZON-AUTHORIZATION-ID',
                'LOCAL-CAPTURE-REFERENCE-ID',
                123.45,
                'SOFT-DESCRIPTOR'
            );
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_CaptureResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_CaptureRequest();
        $expectedApiRequestObject->setSellerId('MY-SELLER-ID');

        $expectedApiRequestObject->setAmazonAuthorizationId('AMAZON-AUTHORIZATION-ID');
        $expectedApiRequestObject->setCaptureReferenceId('LOCAL-CAPTURE-REFERENCE-ID');
        $expectedApiRequestObject->setCaptureAmount(new \OffAmazonPaymentsService_Model_Price());
        $expectedApiRequestObject->getCaptureAmount()->setAmount(123.45);
        $expectedApiRequestObject->getCaptureAmount()->setCurrencyCode('EUR');

        $expectedApiRequestObject->setSellerCaptureNote('SELLER-CAPTURE-NOTE');
        $expectedApiRequestObject->setSoftDescriptor('SOFT-DESCRIPTOR');

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('capture')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('capture')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue('MY-SELLER-ID'));
        $config->expects($this->any())->method('getSellerOrderNote')->will($this->returnValue('SELLER-CAPTURE-NOTE'));
        $config->expects($this->any())->method('getSoftDescriptor')->will($this->returnValue('SOFT-DESCRIPTOR'));

        return $config;
    }
}
