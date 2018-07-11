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

class CaptureOrder_PayOnShipmentTest extends AbstractAmazonPaymentCaptureOrder
{
    protected function setUp()
    {
        parent::setUp();

        $this->getConfig()->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(true));
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
        $order = $this->createOrderMock(false, true);
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
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')->will($this->returnValue($expectedTransactionData));
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $mockTransactionResponse->fieldSequenceNumber = 123;
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );
        $transactionManager->expects($this->once())->method('confirmTransaction')->with($this->equalTo(123), $this->anything())->will($this->returnValue($mockTransactionResponse));

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success-synchronous.xml')->getAuthorizeResult()->getAuthorizationDetails()));

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

        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $mockLocalId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        //$amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalId));
        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $amazonReferenceIdManagerMock->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo('LOCAL-AUTH-ID'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));
        $amazonReferenceIdManagerMock->expects($this->exactly(2))->method('persist');
        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->setDb(self::$dbal);
        $transaction = $amazonPayment->captureOrder($transactionManager, $order);
        $this->assertEquals($mockTransactionResponse, $transaction);
    }

    /**
     * we expect to get an authorize only.
     */
    public function test_capture_physical_products_only()
    {
        $order = $this->createOrderMock(true, false);
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
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction', 'confirmTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');
        $transactionManager->expects($this->never())->method('confirmTransaction');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorize')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal), // $amount
            $this->equalTo(false) // $synchronous
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');
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
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $mockLocalId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->exactly(2))->method('persist');

        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->setDb(self::$dbal);
        $transaction = $amazonPayment->captureOrder($transactionManager, $order);
        $this->assertNull($transaction);
    }

    public function test_capture_mixed_basket()
    {
        $order = $this->createOrderMock(true, true);
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
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction', 'confirmTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
        $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('amazon auth+capture on order completion (only downloads or pay on order completion)'));
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $mockTransactionResponse->fieldSequenceNumber = 123;
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );
        $transactionManager->expects($this->once())->method('confirmTransaction')->with($this->equalTo(123), $this->anything())->will($this->returnValue($mockTransactionResponse));

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), // $localAuthorizationReferenceId
            $this->equalTo($expectedTransactionData->getTotalValue()), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success-synchronous.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $amazonOrderRef->expects($this->once())->method('authorize')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal - $expectedTransactionData->getTotalValue()), // $amount
            $this->equalTo(false) // $synchronous
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

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
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $mockLocalId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $mockLocalWithCaptureId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID-WITH-CAPTURE'));
        $mockLocalWithCaptureId->expects($this->once())->method('setAmazonId')->with($this->equalTo('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalWithCaptureId));
        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $amazonReferenceIdManagerMock->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));
        $amazonReferenceIdManagerMock->expects($this->exactly(4))->method('persist');

        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->setDb(self::$dbal);
        $transaction = $amazonPayment->captureOrder($transactionManager, $order);
        $this->assertEquals($mockTransactionResponse, $transaction);
    }

    // edge cases

    public function test_amazon_api_error_downloads_only()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(false, true);

        // there should be no transaction
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
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
        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalId));

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

    public function test_amazon_api_error_physical_only()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, false);

        // there should be no transaction
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');
        // throw the exception
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')
            ->will(
                $this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()
                )
            );

        $amazonOrderRef->expects($this->once())->method('authorize')->will($this->throwException($expectedException));
        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));

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

    public function test_amazon_api_error_mixed()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

        // there should be no transaction
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
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

        $amazonOrderRef->expects($this->any())->method('authorizeAndCapture')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->any())->method('authorize')->will($this->throwException($expectedException));

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalId));

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

    /**
     * a mixed basket may generate an auth + capture and an auth without capture. if the aut without capture fails, but
     * the auth with capture does not, then the order should NOT be marked as canceled.
     */
    public function test_amazon_api_error_on_auth_without_capture_on_mixed_basket()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

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
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
        $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('amazon auth+capture on order completion (only downloads or pay on order completion)'));
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), // $localAuthorizationReferenceId
            $this->equalTo($expectedTransactionData->getTotalValue()), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success-synchronous.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $amazonOrderRef->expects($this->once())->method('authorize')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal - $expectedTransactionData->getTotalValue()), // $amount
            $this->equalTo(false) // $synchronous
        )->will($this->throwException($expectedException));

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
        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID'));
        $mockLocalWithCaptureId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID-WITH-CAPTURE'));
        $mockLocalWithCaptureId->expects($this->once())->method('setAmazonId')->will($this->returnValue('P01-1234567-1234567-0000001'));
        $amazonReferenceIdManagerMock = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->disableOriginalConstructor()->getMock();
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceId')->will($this->returnValue($mockLocalId));
        $amazonReferenceIdManagerMock->expects($this->any())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->will($this->returnValue($mockLocalWithCaptureId));
        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $amazonReferenceIdManagerMock->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));
        $amazonReferenceIdManagerMock->expects($this->exactly(3))->method('persist');

        $this->getConfig()->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($amazonReferenceIdManagerMock));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->never())->method('cancelOrder');
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

    /**
     * the capture + auth fails - since this is always called first, we expect the order to be canceled after this exception.
     */
    public function test_amazon_api_error_on_auth_with_capture_on_mixed_basket()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

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
        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $expectedTransactionData = $this->helperGetTransactionDataForOrder($order, false, true);
        $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('amazon auth+capture on order completion (only downloads or pay on order completion)'));
        $mockTransactionResponse = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->disableOriginalConstructor()->getMock();
        $mockTransactionResponse->id = 'MOCK-TRANSACTION';
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue(
                $mockTransactionResponse
            )
        );

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();

        // - an auth + capture for the complete order
        // \TdbShopOrder $order, $localAuthorizationReferenceId, $amount, $synchronous, $invoiceNumber
        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID'), // $localAuthorizationReferenceId
            $this->equalTo($expectedTransactionData->getTotalValue()), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->throwException($expectedException));

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
