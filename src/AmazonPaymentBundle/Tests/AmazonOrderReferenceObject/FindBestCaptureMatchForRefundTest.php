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
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonOrderReference;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonOrderReference.php';

class FindBestCaptureMatchForRefundTest extends AbstractAmazonOrderReference
{
    const FIXTURE_MY_SELLER_ID = 'MY-SELLER-ID';

    const FIXTURE_AMAZON_ORDER_REFERENCE_ID = 'AMAZON-ORDER-REFERENCE-ID';

    public function test_refund_matched_in_sum_by_one()
    {
        $expectedItemListFound = array('P01-1234567-1234567-0000004' => 30.00);

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);
        $refundValue = $this->helperGetRefundValue($expectedItemListFound, $captureList);

        $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);

        $this->assertEquals($expectedItemListFound, $itemListFound);
    }

    public function test_refund_matched_in_sum_by_two()
    {
        $expectedItemListFound = array('P01-1234567-1234567-0000004' => 30.00, 'P01-1234567-1234567-0000002' => 94.50);

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);
        $refundValue = $this->helperGetRefundValue($expectedItemListFound, $captureList);

        $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);

        $this->assertEquals($expectedItemListFound, $itemListFound);
    }

    public function test_refund_matched_in_sum_by_one_larger_than_refund()
    {
        $expectedItemListFound = array('P01-1234567-1234567-0000002' => 94.50);

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);
        $refundValue = $this->helperGetRefundValue($expectedItemListFound, $captureList);

        $refundValue = $refundValue - 10;

        $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);

        $this->assertEquals($expectedItemListFound, $itemListFound);
    }

    public function test_refund_matched_in_sum_by_two_larger_than_refund()
    {
        $expectedItemListFound = array('P01-1234567-1234567-0000005' => 50.00, 'P01-1234567-1234567-0000002' => 94.50);

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);
        $refundValue = $this->helperGetRefundValue($expectedItemListFound, $captureList);

        $refundValue = $refundValue - 10;

        $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);

        $this->assertEquals($expectedItemListFound, $itemListFound);
    }

    /**
     * expecting an exception.
     */
    public function test_refund_larger_then_remaining_captures()
    {
        $expectedItemListFound = array();

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);

        $refundValue = 500;

        $expectedException = new \TPkgCmsException_LogAndMessage(
            AmazonPayment::ERROR_NO_CAPTURE_FOUND_FOR_REFUND,
            array('refundValue' => $refundValue, 'amazonIdListChecked' => implode(', ', $itemsInMatchList))
        );
        $exception = null;

        try {
            $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
    }

    public function test_api_error()
    {
        $amazonApiErrorThrown = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR, array(
            'responseCode' => '123',
            'errorCode' => 'InternalServerError',
            'errorType' => 'Unknown',
            'message' => 'There was an unknown error in the service',
        ));

        $expectedItemListFound = array('P01-1234567-1234567-0000004' => 30);

        $itemsInMatchList = array(
            'P01-1234567-1234567-0000001', // pending
            'P01-1234567-1234567-0000002', // success
            'P01-1234567-1234567-0000003', // closed
            'P01-1234567-1234567-0000004', // success-two
            'P01-1234567-1234567-0000005', // success-partial-refund
            'P01-1234567-1234567-0000006', // declined-ProcessingFailure
            'P01-1234567-1234567-0000007', // declined-AmazonRejected.xml
        );

        $captureList = $this->fixtureCaptureList();
        $orderRefObject = $this->helperOrderReferenceObjectFactory($captureList, $itemsInMatchList, $amazonApiErrorThrown);
        $list = $this->helperAmazonReferenceIdList($captureList, $itemsInMatchList);
        $refundValue = $this->helperGetRefundValue($expectedItemListFound, $captureList);

        $expectedException = new \TPkgCmsException_LogAndMessage(
            AmazonPayment::ERROR_NO_CAPTURE_FOUND_FOR_REFUND,
            array('refundValue' => $refundValue, 'amazonIdListChecked' => implode(', ', $itemsInMatchList))
        );
        $exception = null;

        try {
            $itemListFound = $orderRefObject->findBestCaptureMatchForRefund($list, $refundValue);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
    }

    /**
     * @return array
     */
    protected function fixtureCaptureList()
    {
        $captureList = array(
            'P01-1234567-1234567-0000004' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture('success-two.xml')->getCaptureResult(
                    )->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-SUCCESS-TWO',
            ),
            'P01-1234567-1234567-0000005' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture(
                        'success-partial-refund.xml'
                    )->getCaptureResult()->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-SUCCESS-PARTIAL-REFUND',
            ),
            'P01-1234567-1234567-0000003' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture('closed.xml')->getCaptureResult(
                    )->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-CLOSED',
            ),
            'P01-1234567-1234567-0000002' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture('success.xml')->getCaptureResult(
                    )->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-SUCCESS',
            ),
            'P01-1234567-1234567-0000001' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture('pending.xml')->getCaptureResult(
                    )->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-PENDING',
            ),
            'P01-1234567-1234567-0000006' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture(
                        'declined-ProcessingFailure.xml'
                    )->getCaptureResult()->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-DECLINED-PROCESSINGFAILURE',
            ),
            'P01-1234567-1234567-0000007' => array(
                'expectedResponse' => AmazonPaymentFixturesFactory::capture(
                        'declined-AmazonRejected.xml'
                    )->getCaptureResult()->getCaptureDetails(),
                'transactionId' => 'TRANSACTION-ID-DECLINED-AMAZONREJECTED',
            ),
        );

        foreach ($captureList as $amazonCaptureId => $response) {
            /** @var $responseObject \OffAmazonPaymentsService_Model_CaptureDetails */
            $responseObject = $response['expectedResponse'];
            $item = new AmazonReferenceId(
                IAmazonReferenceId::TYPE_CAPTURE,
                $responseObject->getCaptureReferenceId(),
                $responseObject->getCaptureAmount()->getAmount(),
                $response['transactionId']
            );
            $item->setAmazonId($responseObject->getAmazonCaptureId());
            $captureList[$amazonCaptureId]['localObjectId'] = $item;
        }

        return $captureList;
    }

    /**
     * @return AmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function helperOrderReferenceObjectFactory($captureList, $itemsInMatchList, \TPkgCmsException_LogAndMessage $expectedException = null)
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->setConstructorArgs(
            array('environment' => \TPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION)
        )->getMock();
        $config->expects($this->any())->method('getMerchantId')->will($this->returnValue(self::FIXTURE_MY_SELLER_ID));
        /** @var $orderRefObject AmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $orderRefObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')
            ->setConstructorArgs(array($config, $this->order->getAmazonOrderReferenceId()))
            ->setMethods(array('getCaptureDetails'))
            ->getMock();

        if (null !== $expectedException) {
            $orderRefObject->expects($this->once())->method('getCaptureDetails')->will($this->throwException($expectedException));
        } else {
            $index = 0;
            foreach ($itemsInMatchList as $amazonCaptureId) {
                $responseObject = $captureList[$amazonCaptureId]['expectedResponse'];
                $orderRefObject->expects($this->at($index))->method('getCaptureDetails')->with($this->equalTo($amazonCaptureId))
                    ->will($this->returnValue($responseObject));
                ++$index;
            }
        }

        return $orderRefObject;
    }

    /**
     * @param $captureList
     * @param $itemsInMatchList
     *
     * @return AmazonReferenceIdList
     */
    protected function helperAmazonReferenceIdList($captureList, $itemsInMatchList)
    {
        $list = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, IAmazonReferenceId::TYPE_CAPTURE);

        foreach ($itemsInMatchList as $amazonCaptureId) {
            $list->addItem($captureList[$amazonCaptureId]['localObjectId']);
        }

        return $list;
    }

    /**
     * @param $expectedItemListFound
     * @param $captureList
     *
     * @return int
     */
    protected function helperGetRefundValue($expectedItemListFound, $captureList)
    {
        $refundValue = 0;
        foreach ($expectedItemListFound as $amazonCaptureId => $expectedRefundValue) {
            /** @var $responseObject \OffAmazonPaymentsService_Model_CaptureDetails */
            $refundValue = $refundValue + $expectedRefundValue;
        }

        return $refundValue;
    }
}
