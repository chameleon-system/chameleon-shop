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

class GetCaptureDetailsTest extends AbstractAmazonOrderReference
{
    const FIXTURE_AMAZON_CAPTURE_ID = 'AMAZON-CAPTURE-ID';

    const FIXTURE_ORDER_REFERENCE_ID = 'ORDER-REFERENCE-ID';

    const FIXTURE_MY_SELLER_ID = 'MY-SELLER-ID';

    public function test_success()
    {
        $expectedResponse = AmazonPaymentFixturesFactory::getCaptureDetails('success.xml');
        $config = $this->helperGetConfig($expectedResponse);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, self::FIXTURE_ORDER_REFERENCE_ID);

        $response = $amazonOrderReferenceObject->getCaptureDetails(self::FIXTURE_AMAZON_CAPTURE_ID);

        $this->assertEquals($expectedResponse->getGetCaptureDetailsResult()->getCaptureDetails(), $response);
    }

    public function test_api_error()
    {
        $apiException = $this->helperGetAmazonApiException();
        $expectedResponse = AmazonPaymentFixturesFactory::getCaptureDetails('success.xml');
        $config = $this->helperGetConfig($expectedResponse, $apiException);

        $amazonOrderReferenceObject = new AmazonOrderReferenceObject($config, self::FIXTURE_ORDER_REFERENCE_ID);

        $exception = null;
        try {
            $amazonOrderReferenceObject->getCaptureDetails(self::FIXTURE_AMAZON_CAPTURE_ID);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'we expect an exception to be thrown');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    private function helperGetConfig(\OffAmazonPaymentsService_Model_GetCaptureDetailsResponse $expectedResponse, \OffAmazonPaymentsService_Exception $exception = null)
    {
        $expectedApiRequestObject = new \OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $expectedApiRequestObject->setAmazonCaptureId(self::FIXTURE_AMAZON_CAPTURE_ID);
        $expectedApiRequestObject->setSellerId(self::FIXTURE_MY_SELLER_ID);

        $api = $this->getMockBuilder('OffAmazonPaymentsService_Client')->disableOriginalConstructor()->getMock();
        if (null === $exception) {
            $api->expects($this->once())
                ->method('getCaptureDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->returnValue($expectedResponse));
        } else {
            $api->expects($this->once())
                ->method('getCaptureDetails')
                ->with($this->equalTo($expectedApiRequestObject))
                ->will($this->throwException($exception));
        }

        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION))->getMock();
        $config->expects($this->once())->method('getAmazonAPI')->will($this->returnValue($api));

        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue(self::FIXTURE_MY_SELLER_ID));

        return $config;
    }
}
