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
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

class CancelOrderReferenceTest extends AbstractAmazonOrderReference
{
    const FIXTURE_MY_SELLER_ID = 'MY-SELLER-ID';
    const FIXTURE_AMAZON_ORDER_REFERENCE_ID = 'AMAZON_ORDER_REFERENCE_ID';

    public function test_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::cancelOrderReference('success.xml');

        $config = $this->helperGetConfig($expectedResponse, 'SOME-CANCEL-REASON');

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, self::FIXTURE_AMAZON_ORDER_REFERENCE_ID);

        $amazonOrderReferenceObject->cancelOrderReference('SOME-CANCEL-REASON');
    }

    public function test_cancellation_reason_too_long()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::cancelOrderReference('success.xml');

        $cancelReason = '';
        for ($i = 0; $i < 2030; ++$i) {
            $cancelReason .= chr(rand(97, 122));
        }
        $config = $this->helperGetConfig($expectedResponse, substr($cancelReason, 0, 1024));

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, self::FIXTURE_AMAZON_ORDER_REFERENCE_ID);

        $amazonOrderReferenceObject->cancelOrderReference($cancelReason);
    }

    public function test_api_error()
    {
        $apiException = $this->helperGetAmazonApiException();
        $expectedResponse = AmazonPaymentFixturesFactory::cancelOrderReference('success.xml');
        $config = $this->helperGetConfig($expectedResponse, 'SOME-CANCEL-REASON', $apiException);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, self::FIXTURE_AMAZON_ORDER_REFERENCE_ID);

        $exception = null;
        try {
            $amazonOrderReferenceObject->cancelOrderReference('SOME-CANCEL-REASON');
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_CancelOrderReferenceResponse $expectedResponse, $cancelReason, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_CancelOrderReferenceRequest();
        $expectedApiRequestObject->setSellerId(self::FIXTURE_MY_SELLER_ID);

        $expectedApiRequestObject->setAmazonOrderReferenceId(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID);
        $expectedApiRequestObject->setCancelationReason($cancelReason);

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('cancelOrderReference')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('cancelOrderReference')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue(self::FIXTURE_MY_SELLER_ID));

        return $config;
    }
}
