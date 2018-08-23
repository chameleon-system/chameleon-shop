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
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundAmazonAPIException;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonRefundDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class RefundTest extends AbstractAmazonPayment
{
    const FIXTURE_INVOICE_NUMBER = 'INVOICE-NUMBER';
    const FIXTURE_REFUND_VALUE = 94.50;
    const FIXTURE_AMAZON_ORDER_REFERENCE = 'AMAZON-ORDER-REFERENCE';
    const FIXTURE_AMAZON_REFUND_REFERENCE_ID = 'P01-1234567-1234567-0000003';
    const FIXTURE_SELLER_REFUND_NOTE = 'SELLER-REFUND-NOTE';

    /**
     * GIVEN I have a transactionManager
     * AND I have an order
     * AND I have a refundValue "1234.56"
     * AND I have an invoiceNumber "INVOICE"
     * WHEN I call "refund"
     * THEN I should get a response "Pending".
     */
    public function test_pending()
    {
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID',
                'response' => AmazonPaymentFixturesFactory::refund('pending-1.xml')->getRefundResult()->getRefundDetails(),
            ),
        );

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);
        $transactionManager = $this->helperAddConfirmTransactionToTransactionManager($transactionManager, $expectedTransactionList, $expectedTransactions);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------
        $amazonPayment = new AmazonPayment($config);
        $transactionList = $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);

        $this->assertEquals($expectedTransactionList, $transactionList);
        // -------------------------------------------------------------------------------------------------------------
    }

    public function test_pending_with_orderItemList()
    {
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID',
                'response' => AmazonPaymentFixturesFactory::refund('pending-1.xml')->getRefundResult()->getRefundDetails(),
            ),
        );

        $refundItemList = array('SHOP-ORDER-ITEM-ID-1' => 1);

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager, $refundItemList);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------
        $amazonPayment = new AmazonPayment($config);
        $transactionList = $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE, $refundItemList);

        $this->assertEquals($expectedTransactionList, $transactionList);
        // -------------------------------------------------------------------------------------------------------------
    }

    public function test_multi_capture_refund()
    {
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID-1',
                'response' => AmazonPaymentFixturesFactory::refund('pending-1.xml')->getRefundResult()->getRefundDetails(),
            ),
            'TRANSACTION-ID-2' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID-2',
                'response' => AmazonPaymentFixturesFactory::refund('pending-2.xml')->getRefundResult()->getRefundDetails(),
            ),
        );

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------
        $amazonPayment = new AmazonPayment($config);
        $transactionList = $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);

        $this->assertEquals($expectedTransactionList, $transactionList);
        // -------------------------------------------------------------------------------------------------------------
    }

    public function test_completed()
    {
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID',
                'response' => AmazonPaymentFixturesFactory::refund('success.xml')->getRefundResult()->getRefundDetails(),
            ),
        );

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);
        $transactionManager = $this->helperAddConfirmTransactionToTransactionManager($transactionManager, $expectedTransactionList, $expectedTransactions);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------
        $amazonPayment = new AmazonPayment($config);
        $transactionList = $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);

        $this->assertEquals($expectedTransactionList, $transactionList);
        // -------------------------------------------------------------------------------------------------------------
    }

    public function test_declined()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_REFUND_DECLINED,
            array(
                'reasonCode' => 'AmazonRejected',
                'reasonDescription' => 'REASON DESCRIPTION',
            )
        );

        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID',
                'response' => AmazonPaymentFixturesFactory::refund('declined-AmazonRejected.xml')->getRefundResult()->getRefundDetails(),
                'exception' => new AmazonRefundDeclinedException(AmazonRefundDeclinedException::REASON_CODE_AMAZON_REJECTED, array(
                        'reasonCode' => 'AmazonRejected',
                        'reasonDescription' => 'REASON DESCRIPTION',
                    )),
            ),
        );

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------

        $exception = null;
        try {
            $amazonPayment = new AmazonPayment($config);
            $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (AmazonRefundDeclinedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
        $this->assertEquals(0, count($exception->getSuccessfulTransactionList()));
    }

    public function test_decline_with_one_success()
    {
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID-1',
                'response' => AmazonPaymentFixturesFactory::refund('pending-1.xml')->getRefundResult()->getRefundDetails(),
            ),
            'TRANSACTION-ID-2' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID-2',
                'response' => AmazonPaymentFixturesFactory::refund('declined-AmazonRejected.xml')->getRefundResult()->getRefundDetails(),
                'exception' => new AmazonRefundDeclinedException(AmazonRefundDeclinedException::REASON_CODE_AMAZON_REJECTED, array(
                        'reasonCode' => 'AmazonRejected',
                        'reasonDescription' => 'REASON DESCRIPTION',
                    )),
            ),
        );
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_REFUND_DECLINED,
            array(
                'reasonCode' => 'AmazonRejected',
                'reasonDescription' => 'REASON DESCRIPTION',
            )
        );

        $refundValue = 0;
        foreach ($expectedTransactions as $details) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $details['response'];
            $refundValue += round($response->getRefundAmount()->getAmount());
        }

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList, $refundValue);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        $expectedSuccessfulTransactions = array($expectedTransactionList[0]);

        // -------------------------------------------------------------------------------------------------------------

        $exception = null;
        try {
            $amazonPayment = new AmazonPayment($config);
            $amazonPayment->refund($transactionManager, $order, $refundValue, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (AmazonRefundDeclinedException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
        $this->assertEquals(1, count($exception->getSuccessfulTransactionList()));
        $this->assertEquals($expectedSuccessfulTransactions, $exception->getSuccessfulTransactionList());
    }

    public function test_api_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR, array(
            'responseCode' => '123',
            'errorCode' => 'InternalServerError',
            'errorType' => 'Unknown',
            'message' => 'There was an unknown error in the service',
        ));
        $expectedTransactions = array(
            'TRANSACTION-ID-1' => array(
                'amazonCaptureId' => 'AMAZON-CAPTURE-ID',
                'response' => AmazonPaymentFixturesFactory::refund('success.xml')->getRefundResult()->getRefundDetails(),
                'exception' => $expectedException,
            ),
        );

        // -------------------------------------------------------------------------------------------------------------
        $config = $this->getConfig();
        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonIdManager = $this->helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager);

        $expectedTransactionList = $this->helperGetExpectedTransactionList($expectedTransactions);

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();
        $transactionManager = $this->helperAddExpectedAddTransactionCallsToManager($expectedTransactionList, $order, $transactionManager);

        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->once())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef = $this->helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList);
        $amazonOrderRef = $this->helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order);

        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));

        // -------------------------------------------------------------------------------------------------------------

        $exception = null;
        try {
            $amazonPayment = new AmazonPayment($config);
            $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (AmazonRefundAmazonAPIException $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
        $this->assertEquals(0, count($exception->getSuccessfulTransactionList()));
    }

    public function test_no_matching_capture_found()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_NO_CAPTURE_FOUND_FOR_REFUND, array('refundValue' => self::FIXTURE_REFUND_VALUE, 'amazonIdListChecked' => array()));
        $config = $this->getConfig();
        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderRef->expects($this->once())->method('findBestCaptureMatchForRefund')->will($this->returnValue(array()));

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();

        $amazonIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonCaptureList = new AmazonReferenceIdList(self::FIXTURE_AMAZON_ORDER_REFERENCE, IAmazonReferenceId::TYPE_CAPTURE);
        $amazonIdManager->expects($this->any())->method('getListOfCaptures')->will($this->returnValue($amazonCaptureList));

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();

        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderRef));
        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonIdManager));

        $exception = null;
        try {
            $amazonPayment = new AmazonPayment($config);
            $amazonPayment->refund($transactionManager, $order, self::FIXTURE_REFUND_VALUE, self::FIXTURE_INVOICE_NUMBER, self::FIXTURE_SELLER_REFUND_NOTE);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
    }

    /**
     * @param $expectedTransactionList
     * @param $order
     * @param \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject $transactionManager
     *
     * @return \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function helperAddExpectedAddTransactionCallsToManager(
        $expectedTransactionList,
        $order,
        $transactionManager,
        $refundItemList = null
    ) {
        $expectedTransactionDataBase = new \TPkgShopPaymentTransactionData($order, \TPkgShopPaymentTransactionData::TYPE_CREDIT);
        if (null === $refundItemList) {
            $transactionManager->expects($this->never())->method('getTransactionDataFromOrder');
        } else {
            $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
                ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_CREDIT), $this->equalTo($refundItemList))
                ->will($this->returnValue($expectedTransactionDataBase));
        }
        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $tmp = array();
        foreach ($expectedTransactionList as $index => $expectedTransaction) {
            $tmp[] = $expectedTransaction;
            /** @var $expectedTransactionData \TPkgShopPaymentTransactionData|\PHPUnit_Framework_MockObject_MockObject */
            $expectedTransactionData = clone $expectedTransactionDataBase;
            $expectedTransactionData->setTotalValue(-1 * $expectedTransaction->fieldAmount);
            $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('refund for invoice '.self::FIXTURE_INVOICE_NUMBER.' with note '.self::FIXTURE_SELLER_REFUND_NOTE));
        }
        $transactionManager->expects($this->any())->method('addTransaction')->will($this->returnCallback(function ($arg) use ($expectedTransactionList) {
            static $count = 0;

            return $expectedTransactionList[$count++];
        }));

        return $transactionManager;
    }

    /**
     * @param \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject $transactionManager
     * @param $expectedTransactionList
     * @param $expectedTransactions
     */
    protected function helperAddConfirmTransactionToTransactionManager($transactionManager, $expectedTransactionList, $expectedTransactions)
    {
        $confirmTransactionMap = array();
        $index = 0;
        $callList = array();
        foreach ($expectedTransactions as $transactionId => $transactionDetails) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $transactionDetails['response'];

            if (AmazonOrderReferenceObject::STATUS_REFUND_COMPLETED !== $response->getRefundStatus()->getState()) {
                continue;
            }
            // find transaction
            reset($expectedTransactionList);
            /** @var $expectedTransaction \TdbPkgShopPaymentTransaction */
            foreach ($expectedTransactionList as $expectedTransaction) {
                if ($expectedTransaction->id === $transactionId) {
                    $expectedTransaction->fieldConfirmed = true;
                    $callList[] = $expectedTransaction;
                }
            }
            reset($expectedTransactionList);

            ++$index;
        }

        $transactionManager->expects($this->any())->method('confirmTransaction')
            ->will($this->returnCallback(function ($arg, $arg2) use ($callList) {
                static $index = 0;

                return $callList[$index++];
            }));

        return $transactionManager;
    }

    /**
     * @param $expectedTransactions
     * @param AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject $amazonIdManager
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function helperAddIdManagerExpectations($expectedTransactions, $amazonIdManager)
    {
        $index = 0;
        $countExceptedPersists = 0;
        $responseMap = array();
        foreach ($expectedTransactions as $transactionId => $transactionDetails) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $transactionDetails['response'];
            $throwsException = (isset($transactionDetails['exception']));
            $localId = $this->getMockBuilder('ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')
                ->setConstructorArgs(
                    array(
                        IAmazonReferenceId::TYPE_REFUND,
                        $response->getRefundReferenceId(),
                        round($response->getRefundAmount()->getAmount(), 2),
                        $transactionId,
                    )
                )
                ->setMethods(array('setAmazonId'))->getMock();

            ++$countExceptedPersists;
            if (null == $throwsException) {
                $localId->expects($this->once())->method('setAmazonId')->with(
                    $this->equalTo($response->getAmazonRefundId())
                );
                ++$countExceptedPersists;
            }
            $responseMap[] = array(round($response->getRefundAmount()->getAmount(), 2), $transactionId, $localId);
            ++$index;
        }
        $amazonIdManager->expects($this->any())->method('createLocalRefundReferenceId')
            //$amazonIdManager->expects($this->any())->method('createLocalRefundReferenceId')
            ->will($this->returnCallback(
                    function ($amount, $transactionId) use ($responseMap) {
                        static $index = -1;
                        ++$index;
                        if ($responseMap[$index][0] !== $amount) {
                            return false;
                        }
                        if ($responseMap[$index][1] !== $transactionId) {
                            return false;
                        }

                        return $responseMap[$index][2];
                    }
                )
            );

        if ($countExceptedPersists > 0) {
            $amazonIdManager->expects($this->exactly($countExceptedPersists))->method('persist');
        } else {
            $amazonIdManager->expects($this->never())->method('persist');
        }
        reset($expectedTransactions);

        return $amazonIdManager;
    }

    /**
     * @param $expectedTransactions
     *
     * @return array
     */
    protected function helperGetExpectedTransactionList($expectedTransactions)
    {
        $expectedTransactionList = array();
        $index = 0;
        foreach ($expectedTransactions as $transactionId => $transactionDetails) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $transactionDetails['response'];
            /** @var $transaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
            $transaction = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
            $transaction->fieldAmount = round(-1 * $response->getRefundAmount()->getAmount(), 2);
            $transaction->id = $transactionId;
            $transaction->fieldSequenceNumber = $index;
            if (AmazonOrderReferenceObject::STATUS_REFUND_COMPLETED === $response->getRefundStatus()->getState()) {
                $transaction->fieldConfirmed = true;
            }
            $expectedTransactionList[] = $transaction;

            // stop execution if one transaction fails
            if (isset($transactionDetails['exception']) || AmazonOrderReferenceObject::STATUS_REFUND_DECLINED === $response->getRefundStatus()->getState()) {
                break;
            }
            ++$index;
        }
        reset($expectedTransactions);

        return $expectedTransactionList;
    }

    /**
     * @param IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject $amazonOrderRef
     * @param array                                                                $expectedTransactions
     * @param array                                                                $amazonCaptureList
     *
     * @return IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject $amazonOrderRef
     */
    private function helperAddExpectedCaptureList($amazonOrderRef, $expectedTransactions, $amazonCaptureList, $refundValue = self::FIXTURE_REFUND_VALUE)
    {
        $expectedCaptureList = array();
        foreach ($expectedTransactions as $transactionId => $transactionDetails) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $transactionDetails['response'];
            $expectedCaptureList[$transactionDetails['amazonCaptureId']] = round($response->getRefundAmount()->getAmount(), 2);
        }
        reset($expectedTransactions);

        $amazonOrderRef->expects($this->once())->method('findBestCaptureMatchForRefund')
            ->with($this->equalTo($amazonCaptureList), $this->equalTo($refundValue))
            ->will($this->returnValue($expectedCaptureList));

        return $amazonOrderRef;
    }

    /**
     * @param IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject $amazonOrderRef
     * @param array                                                                $expectedTransactions
     * @param \TdbShopOrder                                                        $order
     *
     * @return IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject $amazonOrderRef
     */
    private function helperAddExpectedRefundCallsToOrderRefObject($amazonOrderRef, $expectedTransactions, $order)
    {
        $index = 0;
        $refundMap = array();
        $responseMap = array();
        foreach ($expectedTransactions as $transactionId => $transactionDetails) {
            /** @var $response \OffAmazonPaymentsService_Model_RefundDetails */
            $response = $transactionDetails['response'];
            $exception = (isset($transactionDetails['exception'])) ? $transactionDetails['exception'] : null;
            if (null === $exception) {
                $refundMap[] = array(
                    $order,
                    $transactionDetails['amazonCaptureId'],
                    $response->getRefundReferenceId(),
                    round($response->getRefundAmount()->getAmount(), 2),
                    self::FIXTURE_INVOICE_NUMBER,
                    self::FIXTURE_SELLER_REFUND_NOTE,
                    $response,
                );
            } else {
                $refundMap[] = array(
                    $order,
                    $transactionDetails['amazonCaptureId'],
                    $response->getRefundReferenceId(),
                    round($response->getRefundAmount()->getAmount(), 2),
                    self::FIXTURE_INVOICE_NUMBER,
                    self::FIXTURE_SELLER_REFUND_NOTE,
                    $exception,
                );
            }

            ++$index;
        }
        $amazonOrderRef->expects($this->any())->method('refund')
            ->will($this->returnCallback(function ($order, $amazonCaptureId, $refundReferenceId, $value, $invoiceNumber, $sellerNote) use ($refundMap) {
                static $index = -1;
                ++$index;
                if ($refundMap[$index][0] != $order) {
                    return false;
                }
                if ($refundMap[$index][1] != $amazonCaptureId) {
                    return false;
                }
                if ($refundMap[$index][2] != $refundReferenceId) {
                    return false;
                }
                if ($refundMap[$index][3] != $value) {
                    return false;
                }
                if ($refundMap[$index][4] != $invoiceNumber) {
                    return false;
                }
                if ($refundMap[$index][5] != $sellerNote) {
                    return false;
                }
                if ($refundMap[$index][6] instanceof \TPkgCmsException_LogAndMessage) {
                    throw $refundMap[$index][6];
                }

                return $refundMap[$index][6];
            }
                )
            );

        return $amazonOrderRef;
    }
}
