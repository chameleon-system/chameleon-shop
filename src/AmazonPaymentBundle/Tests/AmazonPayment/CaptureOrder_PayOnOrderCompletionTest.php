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

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonAuthorizationDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPaymentCaptureOrder;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonPaymentCaptureOrder.php';

class CaptureOrder_PayOnOrderCompletionTest extends AbstractAmazonPaymentCaptureOrder
{
    protected function setUp()
    {
        parent::setUp();

        $this->getConfig()->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(false));
    }

    /**
     * we expect the same results for every basket type, so create on helper method to cover all 3 cases.
     *
     * @param bool $physicalProducts
     * @param bool $downloads
     */
    private function helperCaptureSuccess($physicalProducts, $downloads)
    {
        $order = $this->createOrderMock($physicalProducts, $downloads);
        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->once())->method('SaveFieldsFast')->with(
            $this->equalTo(
                array(
                    'user_email' => 'amazonpayment@rn.esono.de',
                    'adr_billing_lastname' => 'Mr. Dev',
                    'adr_billing_telefon' => '+49 761 15 18 28 0',
                    'adr_shipping_use_billing' => '0',
                    'adr_shipping_salutation_id' => '',
                    'adr_shipping_company' => 'ESONO AG',
                    'adr_shipping_additional_info' => '2 OG',
                    'adr_shipping_firstname' => '',
                    'adr_shipping_lastname' => 'Mr. Dev',
                    'adr_shipping_street' => 'Grünwälderstr. 10-14',
                    'adr_shipping_streetnr' => '',
                    'adr_shipping_city' => 'Freiburg',
                    'adr_shipping_postalcode' => '79098',
                    'adr_shipping_country_id' => '1',
                    'adr_shipping_telefon' => '0761 15 18 28 0',
                )
            )
        );

        // - one transaction for the complete amount
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction', 'getTransactionDataFromOrder', 'confirmTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, $physicalProducts, $downloads);

        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')->will($this->returnValue($expectedTransactionData));
        /** @var $mockTransactionResponse \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $mockTransactionResponse->fieldSequenceNumber = 123;
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );

        // the sync part should have its transaction confirmed
        $transactionManager->expects($this->once())->method('confirmTransaction')->with($this->equalTo(123), $this->anything())->will($this->returnValue($mockTransactionResponse));

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success-synchronous.xml')->getAuthorizeResult()->getAuthorizationDetails()));
        $amazonOrderRef->expects($this->never())->method('authorize');

        // mock amazonOrderRef Object methods so we get the expected response
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')
            ->will(
                $this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()
                )
            );

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $mockLocalWithCaptureId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID-WITH-CAPTURE'));
        $mockLocalWithCaptureId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalWithCaptureId));
        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $amazonReferenceIdManagerMock->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));
        $amazonReferenceIdManagerMock->expects($this->exactly(2))->method('persist');

        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        // expect a transaction t

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->setDb(self::$dbal);
        $transaction = $amazonPayment->captureOrder($transactionManager, $order);
        $this->assertEquals($mockTransactionResponse, $transaction);
    }

    /**
     * we expect the same results for every basket type, so create on helper method to cover all 3 cases.
     *
     * @param bool $physicalProducts
     * @param bool $downloads
     */
    public function test_CaptureSuccessButSynchronousAuthReturnsPending()
    {
        $physicalProducts = false;
        $downloads = true;
        $order = $this->createOrderMock($physicalProducts, $downloads);
        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->once())->method('SaveFieldsFast')->with(
            $this->equalTo(
                array(
                    'user_email' => 'amazonpayment@rn.esono.de',
                    'adr_billing_lastname' => 'Mr. Dev',
                    'adr_billing_telefon' => '+49 761 15 18 28 0',
                    'adr_shipping_use_billing' => '0',
                    'adr_shipping_salutation_id' => '',
                    'adr_shipping_company' => 'ESONO AG',
                    'adr_shipping_additional_info' => '2 OG',
                    'adr_shipping_firstname' => '',
                    'adr_shipping_lastname' => 'Mr. Dev',
                    'adr_shipping_street' => 'Grünwälderstr. 10-14',
                    'adr_shipping_streetnr' => '',
                    'adr_shipping_city' => 'Freiburg',
                    'adr_shipping_postalcode' => '79098',
                    'adr_shipping_country_id' => '1',
                    'adr_shipping_telefon' => '0761 15 18 28 0',
                )
            )
        );

        // - one transaction for the complete amount
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction', 'getTransactionDataFromOrder', 'confirmTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, $physicalProducts, $downloads);

        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')->will($this->returnValue($expectedTransactionData));
        /** @var $mockTransactionResponse \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $mockTransactionResponse->fieldSequenceNumber = 123;
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );

        // the sync part should have its transaction confirmed
        $transactionManager->expects($this->never())->method('confirmTransaction');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('pending.xml')->getAuthorizeResult()->getAuthorizationDetails()));
        $amazonOrderRef->expects($this->never())->method('authorize');

        // mock amazonOrderRef Object methods so we get the expected response
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')
            ->will(
                $this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()
                )
            );

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $mockLocalWithCaptureId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID-WITH-CAPTURE'));
        $mockLocalWithCaptureId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalWithCaptureId));
        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $amazonReferenceIdManagerMock->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));

        $amazonReferenceIdManagerMock->expects($this->exactly(2))->method('persist');

        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        // expect a transaction t

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->setDb(self::$dbal);
        $transaction = $amazonPayment->captureOrder($transactionManager, $order);
        $this->assertEquals($mockTransactionResponse, $transaction);
    }

    /**
     * we always expect the order details to be set and the order reference to be confirmed.
     *
     * the order contains only downloads, so we expect
     * - one transaction for the complete amount
     * - an auth + capture for the complete order
     * - the orders shipping address to be updated
     * - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
     */
    public function test_capture_downloads_only()
    {
        $this->helperCaptureSuccess(false, true);
    }

    public function test_capture_physical_products_only()
    {
        $this->helperCaptureSuccess(true, false);
    }

    public function test_capture_mixed_basket()
    {
        $this->helperCaptureSuccess(true, true);
    }

    // edge cases

    private function helperAmazonApiErrorOnAuthorize($physicalProducts, $downloads)
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock($physicalProducts, $downloads);

        // there should be no transaction
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, $physicalProducts, $downloads);
        $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('amazon auth+capture on order completion (only downloads or pay on order completion)'));

        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );

        // throw the exception
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')
            ->will(
                $this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()
                )
            );

        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('authorize');
        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->once())->method('persist');
        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));
        $amazonPayment->setDb(self::$dbal);

        $exception = null;
        try {
            $amazonPayment->captureOrder($transactionManager, $order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException, $exception);
    }

    public function test_amazon_api_error_downloads_only()
    {
        $this->helperAmazonApiErrorOnAuthorize(false, true);
    }

    public function test_amazon_api_error_physical_only()
    {
        $this->helperAmazonApiErrorOnAuthorize(true, false);
    }

    public function test_amazon_api_error_mixed()
    {
        $this->helperAmazonApiErrorOnAuthorize(true, true);
    }

    public function test_synchronous_auth_declined_invalidPaymentMethod()
    {
        $this->helperSynchronousAuthRejected(AmazonAuthorizationDeclinedException::REASON_CODE_INVALID_PAYMENT_METHOD, AmazonPayment::ERROR_AUTHORIZATION_DECLINED);
    }

    public function test_synchronous_auth_declined_amazonRejected()
    {
        $this->helperSynchronousAuthRejected(AmazonAuthorizationDeclinedException::REASON_CODE_AMAZON_REJECTED, AmazonPayment::ERROR_AUTHORIZATION_DECLINED);
    }

    public function test_synchronous_auth_declined_processingFailure()
    {
        $this->helperSynchronousAuthRejected(AmazonAuthorizationDeclinedException::REASON_CODE_PROCESSING_FAILURE, AmazonPayment::ERROR_AUTHORIZATION_DECLINED);
    }

    public function test_synchronous_auth_declined_transactionTimedOut()
    {
        $this->helperSynchronousAuthRejected(AmazonAuthorizationDeclinedException::REASON_CODE_TRANSACTION_TIMED_OUT, AmazonPayment::ERROR_AUTHORIZATION_DECLINED);
    }

    /**
     * we expect the order to be canceled and otherwise unchanged. There should be no transaction, and neither auth nor auth+capture calls.
     */
    public function test_amazon_api_error_on_set_order_reference_details()
    {
        parent::test_amazon_api_error_on_set_order_reference_details(); // expect same as paymentOnShipment
    }

    /**
     * we expect the order to be canceled but otherwise unchanged. there should be no auth or auth+capture and no transactions.
     */
    public function test_amazon_api_error_on_confirm_order_reference()
    {
        parent::test_amazon_api_error_on_confirm_order_reference(); // expect same as paymentOnShipment
    }

    /**
     * the order reference was confirmed but not used. so we expect
     * - the order ref object to be canceled
     * - the order to be canceled and unchanged
     * - no calls to auth or auth+capture
     * - no transactions.
     */
    public function test_amazon_api_error_on_get_order_reference_details()
    {
        parent::test_amazon_api_error_on_get_order_reference_details(); // expect same as paymentOnShipment
    }

    /**
     * the error should be thrown when trying to setOrderReferenceDetails, so we expect the method to cancel the order.
     */
    public function test_shippingAddressNotSet_constraint_error()
    {
        parent::test_shippingAddressNotSet_constraint_error(); // expect same as paymentOnShipment
    }

    /**
     * expect the same as test_shippingAddressNotSet_constraint_error.
     */
    public function test_paymentPlanNotSet_constraint_error()
    {
        parent::test_paymentPlanNotSet_constraint_error(); // expect same as paymentOnShipment
    }

    /**
     * expect order to be canceled.
     */
    public function test_AmountNotSet_constraint_error()
    {
        parent::test_AmountNotSet_constraint_error(); // expect same as paymentOnShipment
    }

    /**
     * expect order to be canceled.
     */
    public function test_Unknown_constraint_error()
    {
        parent::test_Unknown_constraint_error(); // expect same as paymentOnShipment
    }

    /**
     * expect order to be canceled.
     */
    public function test_InvalidCountry_error()
    {
        parent::test_InvalidCountry_error(); // expect same as paymentOnShipment
    }
}
