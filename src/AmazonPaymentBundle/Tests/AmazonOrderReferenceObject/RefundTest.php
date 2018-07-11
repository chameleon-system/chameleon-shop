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
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

class RefundTest extends AbstractAmazonOrderReference
{
    const FIXTURE_LOCAL_REFUND_REFERENCE_ID = 'LOCAL-REFUND-REFERENCE-ID';

    const FIXTURE_SELLER_REFUND_NOTE = 'SELLER REFUND NOTE';

    const FIXTURE_SELLER_SOFT_DESCRIPTOR = 'SELLER SOFT DESCRIPTOR';

    const FIXTURE_VALUE = 1234.56;

    const FIXTURE_AMAZON_CAPTURE_ID = 'AMAZON-CAPTURE-ID';

    const FIXTURE_MY_SELLER_ID = 'MY-SELLER-ID';

    public function test_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::refund('success.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $response = $orderRefObject->refund(
            $this->order,
            self::FIXTURE_AMAZON_CAPTURE_ID,
            self::FIXTURE_LOCAL_REFUND_REFERENCE_ID,
            self::FIXTURE_VALUE,
            self::FIXTURE_SELLER_SOFT_DESCRIPTOR,
            self::FIXTURE_SELLER_REFUND_NOTE);

        $this->assertEquals($expectedResponse->getRefundResult()->getRefundDetails(), $response);
    }

    public function test_Declined_AmazonRejected()
    {
        $expectedException = new AmazonRefundDeclinedException(AmazonRefundDeclinedException::REASON_CODE_AMAZON_REJECTED, array(
            'reasonCode' => 'AmazonRejected',
            'reasonDescription' => 'REASON DESCRIPTION',
        ));
        $expectedResponse = AmazonPaymentFixturesFactory::refund('declined-AmazonRejected.xml');

        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->refund(
                $this->order,
                self::FIXTURE_AMAZON_CAPTURE_ID,
                self::FIXTURE_LOCAL_REFUND_REFERENCE_ID,
                self::FIXTURE_VALUE,
                self::FIXTURE_SELLER_SOFT_DESCRIPTOR,
                self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (AmazonRefundDeclinedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
        $this->assertEquals($expectedException->getReasonCode(), $expectedException->getReasonCode());
    }

    public function test_Declined_ProcessingFailure()
    {
        $expectedException = new AmazonRefundDeclinedException(AmazonRefundDeclinedException::REASON_CODE_PROCESSING_FAILURE, array(
            'reasonCode' => 'ProcessingFailure',
            'reasonDescription' => 'REASON DESCRIPTION',
        ));
        $expectedResponse = AmazonPaymentFixturesFactory::refund('declined-ProcessingFailure.xml');

        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->refund(
                $this->order,
                self::FIXTURE_AMAZON_CAPTURE_ID,
                self::FIXTURE_LOCAL_REFUND_REFERENCE_ID,
                self::FIXTURE_VALUE,
                self::FIXTURE_SELLER_SOFT_DESCRIPTOR,
                self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (AmazonRefundDeclinedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
        $this->assertEquals($expectedException->getReasonCode(), $expectedException->getReasonCode());
    }

    public function test_api_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR, array(
            'responseCode' => '123',
            'errorCode' => 'InternalServerError',
            'errorType' => 'Unknown',
            'message' => 'There was an unknown error in the service',
        ));

        $expectedResponse = AmazonPaymentFixturesFactory::refund('success.xml');
        $config = $this->helperGetConfig($expectedResponse, $this->helperGetAmazonApiException('InternalServerError'));
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $exception = null;
        try {
            $response = $orderRefObject->refund(
                $this->order,
                self::FIXTURE_AMAZON_CAPTURE_ID,
                self::FIXTURE_LOCAL_REFUND_REFERENCE_ID,
                self::FIXTURE_VALUE,
                self::FIXTURE_SELLER_SOFT_DESCRIPTOR,
                self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_RefundResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_RefundRequest();
        $expectedApiRequestObject->setSellerId(self::FIXTURE_MY_SELLER_ID);

        $expectedApiRequestObject->setAmazonCaptureId(self::FIXTURE_AMAZON_CAPTURE_ID);
        $expectedApiRequestObject->setRefundAmount(new \OffAmazonPaymentsService_Model_Price());
        $expectedApiRequestObject->getRefundAmount()->setAmount(self::FIXTURE_VALUE);
        $expectedApiRequestObject->getRefundAmount()->setCurrencyCode('EUR');
        $expectedApiRequestObject->setRefundReferenceId(self::FIXTURE_LOCAL_REFUND_REFERENCE_ID);
        $expectedApiRequestObject->setSellerRefundNote(self::FIXTURE_SELLER_REFUND_NOTE);
        $expectedApiRequestObject->setSoftDescriptor(self::FIXTURE_SELLER_SOFT_DESCRIPTOR);

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('refund')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('refund')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue(self::FIXTURE_MY_SELLER_ID));
        $config->expects($this->any())
            ->method('getSoftDescriptor')
            ->with($this->equalTo($this->order), $this->equalTo(self::FIXTURE_SELLER_SOFT_DESCRIPTOR))
            ->will($this->returnValue(self::FIXTURE_SELLER_SOFT_DESCRIPTOR));

        return $config;
    }
}
