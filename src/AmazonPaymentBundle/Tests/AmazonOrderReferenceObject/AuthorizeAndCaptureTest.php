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
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonAuthorizationDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

class AuthorizeAndCaptureTest extends AbstractAmazonOrderReference
{
    //  authorize($localAuthorizationReferenceId, $amount, $synchronous)

    public function test_asynchronous_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::authorize('success.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

        $response = $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false);

        $this->assertEquals($expectedResponse->getAuthorizeResult()->getAuthorizationDetails(), $response);
    }

    public function test_synchronous_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::authorize('success-synchronous.xml');
        $config = $this->helperGetConfig($expectedResponse, null, true);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

        $response = $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false);

        $this->assertEquals($expectedResponse->getAuthorizeResult()->getAuthorizationDetails(), $response);
    }

    public function test_asynchronous_success_with_invoice_number()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::authorize('success.xml');
        $config = $this->helperGetConfig($expectedResponse, null);
        $config->expects($this->any())->method('getSoftDescriptor')->with($this->equalTo($this->order), $this->equalTo('INVOICE NUMBER'))->will($this->returnValue('SOME SOFT DESCRIPTOR'));

        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

        $response = $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false, 'INVOICE NUMBER');

        $this->assertEquals($expectedResponse->getAuthorizeResult()->getAuthorizationDetails(), $response);
    }

    public function test_synchronous_success_with_invoice_number()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::authorize('success-synchronous.xml');
        $config = $this->helperGetConfig($expectedResponse, null, true);
        $config->expects($this->any())->method('getSoftDescriptor')->with($this->equalTo($this->order), $this->equalTo('INVOICE NUMBER'))->will($this->returnValue('SOME SOFT DESCRIPTOR'));
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

        $response = $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false, 'INVOICE NUMBER');

        $this->assertEquals($expectedResponse->getAuthorizeResult()->getAuthorizationDetails(), $response);
    }

    public function test_synchronous_declined_InvalidPaymentMethod()
    {
        $declineList = array(
            'declined-AmazonRejected.xml' => AmazonAuthorizationDeclinedException::REASON_CODE_AMAZON_REJECTED,
            'declined-InvalidPaymentMethod.xml' => AmazonAuthorizationDeclinedException::REASON_CODE_INVALID_PAYMENT_METHOD,
            'declined-ProcessingFailure.xml' => AmazonAuthorizationDeclinedException::REASON_CODE_PROCESSING_FAILURE,
            'declined-TransactionTimedOut.xml' => AmazonAuthorizationDeclinedException::REASON_CODE_TRANSACTION_TIMED_OUT,
        );

        foreach ($declineList as $fixture => $expectedExceptionCode) {
            $expectedResponse = AmazonPaymentFixturesFactory::authorize($fixture);
            $config = $this->helperGetConfig($expectedResponse, null);
            $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

            $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

            $exception = null;
            try {
                $response = $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false);
            } catch (AmazonAuthorizationDeclinedException $e) {
                $exception = $e;
            }

            $this->assertNotNull($exception, 'expecting an exception for '.$fixture);
            $this->assertEquals($expectedExceptionCode, $exception->getReasonCode());
        }
    }

    public function test_api_error()
    {
        $amazonApiException = $this->helperGetAmazonApiException();
        $expectedResponse = AmazonPaymentFixturesFactory::authorize('success.xml');
        $config = $this->helperGetConfig($expectedResponse, $amazonApiException);
        $orderRefObject = new AmazonOrderReferenceObject($config, $this->order->getAmazonOrderReferenceId());

        $authRefId = 'LOCAL-AUTH-REFERENCE-ID';

        $exception = null;
        try {
            $orderRefObject->authorizeAndCapture($this->order, $authRefId, 1234.56, false);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_AuthorizeResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null, $synchronousMode = false)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_AuthorizeRequest();
        $expectedApiRequestObject->setSellerId('MY-SELLER-ID');

        $expectedApiRequestObject->setAmazonOrderReferenceId('ORDER-REFERENCE-ID');
        $expectedApiRequestObject->setAuthorizationReferenceId('LOCAL-AUTH-REFERENCE-ID');

        $expectedApiRequestObject->setAuthorizationAmount(new \OffAmazonPaymentsService_Model_Price());
        $expectedApiRequestObject->getAuthorizationAmount()->setAmount(1234.56);
        $expectedApiRequestObject->getAuthorizationAmount()->setCurrencyCode('EUR');
        $expectedApiRequestObject->setCaptureNow(true);
        $expectedApiRequestObject->setSoftDescriptor('SOME SOFT DESCRIPTOR');
        if ($synchronousMode) {
            $expectedApiRequestObject->setTransactionTimeout(0);
        }

        $expectedApiRequestObject->setSellerAuthorizationNote('SELLER AUTH NOTE');

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('authorize')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('authorize')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue('MY-SELLER-ID'));
        $config->expects($this->any())->method('getSellerAuthorizationNote')->will($this->returnValue('SELLER AUTH NOTE'));
        $config->expects($this->any())->method('getSoftDescriptor')->will($this->returnValue('SOME SOFT DESCRIPTOR'));

        return $config;
    }
}
