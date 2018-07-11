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
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonCaptureDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class CaptureShipmentTest extends AbstractAmazonPayment
{
    const FIXTURE_AMAZON_ORDER_REFERENCE_ID = 'AMAZON-ORDER-REFERENCE-ID';
    const FIXTURE_SHOP_ORDER_ID = 'SHOP-ORDER-ID';

    /**
     * a transaction is created and returned [will wait for the ipn for confirmation].
     */
    public function test_capture_valid_existing_authorization()
    {
        $fixtureAmazonCaptureApiResponse = AmazonPaymentFixturesFactory::capture('pending.xml')->getCaptureResult()->getCaptureDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = $fixtureAmazonCaptureApiResponse->getCaptureAmount()->getAmount();
        $fixtureCaptureReferenceId = 'CAPTURE-REFERENCE-ID';
        $fixtureAmazonCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureCaptureReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->once())
            ->method('captureExistingAuthorization')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAmazonAuthorizationId),
                $this->equalTo($fixtureCaptureReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo($fixtureSoftDescription))
            ->will($this->returnValue($fixtureAmazonCaptureApiResponse));

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    /**
     * the capture returns the state immediately - so we don't need to wait for the ipn confirm, but can confirm the transaction
     * immediately.
     */
    public function test_capture_success_with_completed_status()
    {
        $fixtureAmazonCaptureApiResponse = AmazonPaymentFixturesFactory::capture('success.xml')->getCaptureResult()->getCaptureDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = $fixtureAmazonCaptureApiResponse->getCaptureAmount()->getAmount();
        $fixtureCaptureReferenceId = 'CAPTURE-REFERENCE-ID';
        $fixtureAmazonCaptureId = 'P01-1234567-1234567-0000002';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureCaptureReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $amazonOrderRef->expects($this->once())
            ->method('captureExistingAuthorization')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAmazonAuthorizationId),
                $this->equalTo($fixtureCaptureReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo($fixtureSoftDescription))
            ->will($this->returnValue($fixtureAmazonCaptureApiResponse));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));
        //confirmTransaction($iSequenceNumber, $iConfirmedDate)
        $expectedConfirmedTransaction = $expectedTransaction;
        $expectedConfirmedTransaction->fieldConfirmed = true;

        $transactionManager->expects($this->once())
            ->method('confirmTransaction')
            ->with($this->equalTo($expectedTransaction->fieldSequenceNumber), $this->greaterThanOrEqual(time()))->will($this->returnValue($expectedConfirmedTransaction));
        $order->expects($this->once())->method('Load')->with($this->equalTo($order->id))->will($this->returnValue(true)); // if the transaction is confirmed, then the order will be refreshed

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedConfirmedTransaction, $transaction);
    }

    /**
     * same as test_capture_valid_existing_authorization but the items should be connected to the transaction.
     */
    public function test_capture_with_product_item_list()
    {
        $fixtureAmazonCaptureApiResponse = AmazonPaymentFixturesFactory::capture('pending.xml')->getCaptureResult()->getCaptureDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';
        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = $fixtureAmazonCaptureApiResponse->getCaptureAmount()->getAmount();
        $fixtureCaptureReferenceId = 'CAPTURE-REFERENCE-ID';
        $fixtureAmazonCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';
        $fixtureCaptureProductList = array(
            'ORDER-ITEM-ID-1' => 2,
            'ORDER-ITEM-ID-2' => 1,
        );

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue, $fixtureCaptureProductList);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureCaptureReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->once())
            ->method('captureExistingAuthorization')
            ->with($this->equalTo($order), $this->equalTo($fixtureAmazonAuthorizationId), $this->equalTo($fixtureCaptureReferenceId), $this->equalTo($fixtureCaptureValue), $this->equalTo($fixtureSoftDescription))
            ->will($this->returnValue($fixtureAmazonCaptureApiResponse));

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo($fixtureCaptureProductList))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription, $fixtureCaptureProductList);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    public function test_capture_closed_authorization()
    {
        // try to capture - the only auth we find is one that is marked as closed.
        // we expect a new auth to be created
        $fixtureAmazonAuthorizeApiResponse = AmazonPaymentFixturesFactory::authorize('closed-MaxCapturesProcessed.xml')->getAuthorizeResult()->getAuthorizationDetails();

        $fixtureAmazonAuthWithCaptureApiResponse = AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureAuthorizeReferenceId = 'AUTHORIZE-REFERENCE-ID';
        $fixtureAmazonAuthorizationWithCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerCreateAuthWithCaptureMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureAuthorizeReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonAuthorizationWithCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue($fixtureAmazonAuthorizeApiResponse));

        $amazonOrderRef->expects($this->once())
            ->method('authorizeAndCapture')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAuthorizeReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo(false),
                $this->equalTo($fixtureSoftDescription)
            )
            ->will($this->returnValue($fixtureAmazonAuthWithCaptureApiResponse));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    /**
     * an authorization with capture now is created (in asynchronous mode).
     */
    public function test_capture_expired_authorization()
    {
        $fixtureAmazonAuthorizeApiResponse = AmazonPaymentFixturesFactory::authorize('declined-TransactionTimedOut.xml')->getAuthorizeResult()->getAuthorizationDetails();

        $fixtureAmazonAuthWithCaptureApiResponse = AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureAuthorizeReferenceId = 'AUTHORIZE-REFERENCE-ID';
        $fixtureAmazonAuthorizationWithCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerCreateAuthWithCaptureMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureAuthorizeReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonAuthorizationWithCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue($fixtureAmazonAuthorizeApiResponse));

        $amazonOrderRef->expects($this->once())
            ->method('authorizeAndCapture')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAuthorizeReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo(false),
                $this->equalTo($fixtureSoftDescription)
            )
            ->will($this->returnValue($fixtureAmazonAuthWithCaptureApiResponse));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    /**
     * an authorization with capture now is created (in asynchronous mode).
     */
    public function test_capture_pending_authorization()
    {
        $fixtureAmazonAuthorizeApiResponse = AmazonPaymentFixturesFactory::authorize('pending.xml')->getAuthorizeResult()->getAuthorizationDetails();

        $fixtureAmazonAuthWithCaptureApiResponse = AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureAuthorizeReferenceId = 'AUTHORIZE-REFERENCE-ID';
        $fixtureAmazonAuthorizationWithCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerCreateAuthWithCaptureMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureAuthorizeReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonAuthorizationWithCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue($fixtureAmazonAuthorizeApiResponse));

        $amazonOrderRef->expects($this->once())
            ->method('authorizeAndCapture')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAuthorizeReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo(false),
                $this->equalTo($fixtureSoftDescription)
            )
            ->will($this->returnValue($fixtureAmazonAuthWithCaptureApiResponse));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    /**
     * an authorization with capture now is created (in asynchronous mode).
     */
    public function test_capture_no_existing_authorization()
    {
        $fixtureAmazonAuthWithCaptureApiResponse = AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails();

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureAuthorizeReferenceId = 'AUTHORIZE-REFERENCE-ID';
        $fixtureAmazonAuthorizationWithCaptureId = 'P01-1234567-1234567-0000001';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerCreateAuthWithCaptureNoAuthFoundMock(
            $fixtureAuthorizeReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonAuthorizationWithCaptureId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);

        $amazonOrderRef->expects($this->never())
            ->method('getAuthorizationDetails');

        $amazonOrderRef->expects($this->once())
            ->method('authorizeAndCapture')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAuthorizeReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo(false),
                $this->equalTo($fixtureSoftDescription)
            )
            ->will($this->returnValue($fixtureAmazonAuthWithCaptureApiResponse));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        $this->assertEquals($expectedTransaction, $transaction);
    }

    /**
     * the auth object is ok, but when we try to capture, we receive an error.
     */
    public function test_capture_auth_ok_but_capture_declined()
    {
        $fixtureAmazonCaptureApiResponse = AmazonPaymentFixturesFactory::capture('declined-AmazonRejected.xml')->getCaptureResult()->getCaptureDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = $fixtureAmazonCaptureApiResponse->getCaptureAmount()->getAmount();
        $fixtureCaptureReferenceId = 'CAPTURE-REFERENCE-ID';
        $fixtureAmazonCaptureId = 'P01-1234567-1234567-0000002';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerForFailedCapturesMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureCaptureReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->once())
            ->method('captureExistingAuthorization')
            ->with($this->equalTo($order), $this->equalTo($fixtureAmazonAuthorizationId), $this->equalTo($fixtureCaptureReferenceId), $this->equalTo($fixtureCaptureValue), $this->equalTo($fixtureSoftDescription))
            ->will($this->throwException(new AmazonCaptureDeclinedException(AmazonCaptureDeclinedException::REASON_CODE_AMAZON_REJECTED)));

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $exception = null;
        try {
            $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a TPkgCmsException_LogAndMessage exception');
        $this->assertEquals(AmazonPayment::ERROR_CAPTURE_DECLINED, $exception->getMessageCode());
    }

    /**
     * expects
     *  - info mail sent to shop owner that the capture failed
     *  - no transaction
     *  - no new authorization.
     */
    public function test_api_error_on_getAuthorizationDetails()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $fixtureAmazonCaptureApiResponse = AmazonPaymentFixturesFactory::capture('pending.xml')->getCaptureResult()->getCaptureDetails();
        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = $fixtureAmazonCaptureApiResponse->getCaptureAmount()->getAmount();
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        // setup local id manager
        $item = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, $fixtureAuthorizationReferenceId, $fixtureAuthorizationValue, null);
        $item->setAmazonId($fixtureAmazonAuthorizationId);
        $idList = new AmazonReferenceIdList($fixtureAmazonOrderRefId, IAmazonReferenceId::TYPE_AUTHORIZE);
        $idList->addItem($item);

        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalCaptureReferenceId', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))

            ->getMock();

        $idManager->expects($this->never())->method('createLocalCaptureReferenceId');
        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue($idList));
        $idManager->expects($this->never())->method('persist');

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->never())
            ->method('captureExistingAuthorization');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');
        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->throwException($expectedException));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);
        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));

        $transactionManager->expects($this->never())->method('addTransaction');

        $payment = new AmazonPayment($config);
        $exception = null;
        try {
            $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a TPkgCmsException_LogAndMessage exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    public function test_api_error_on_captureExistingAuthorization()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $fixtureAmazonAuthorizationId = 'AMAZON-AUTHORIZATION-ID';
        $fixtureAuthorizationReferenceId = 'AUTHORIZATION-REFERENCE-ID';

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureCaptureReferenceId = 'CAPTURE-REFERENCE-ID';
        $fixtureAmazonCaptureId = 'P01-1234567-1234567-0000002';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerForFailedCapturesMock(
            $fixtureAmazonAuthorizationId,
            $fixtureAuthorizationReferenceId,
            $fixtureCaptureReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonCaptureId,
            $fixtureAuthorizationValue,
            $fixtureAmazonOrderRefId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);
        $amazonOrderRef->expects($this->once())
            ->method('captureExistingAuthorization')
            ->with($this->equalTo($order), $this->equalTo($fixtureAmazonAuthorizationId), $this->equalTo($fixtureCaptureReferenceId), $this->equalTo($fixtureCaptureValue), $this->equalTo($fixtureSoftDescription))
            ->will($this->throwException($expectedException));

        $amazonOrderRef->expects($this->once())
            ->method('getAuthorizationDetails')
            ->with($this->equalTo($fixtureAmazonAuthorizationId))
            ->will($this->returnValue(AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails()));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->once())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $exception = null;
        try {
            $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a TPkgCmsException_LogAndMessage exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    public function test_api_error_on_authorizeAndCapture()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);
        $fixtureAmazonAuthWithCaptureApiResponse = AmazonPaymentFixturesFactory::authorize('success.xml')->getAuthorizeResult()->getAuthorizationDetails();

        $fixtureOrderValue = 200.99;
        $fixtureAuthorizationValue = 150.99;
        $fixtureCaptureValue = 100;
        $fixtureAuthorizeReferenceId = 'AUTHORIZE-REFERENCE-ID';
        $fixtureAmazonAuthorizeId = 'P01-1234567-1234567-0000002';
        $fixtureAmazonOrderRefId = self::FIXTURE_AMAZON_ORDER_REFERENCE_ID;
        $fixtureSoftDescription = 'SOFT-DESCRIPTION';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->fixtureOrderMock($fixtureOrderValue);

        /** @var $expectedTransaction \TdbPkgShopPaymentTransaction|\PHPUnit_Framework_MockObject_MockObject */
        $expectedTransaction = $this->fixtureTransactionMock();

        $expectedTransactionData = $this->fixtureTransactionDataMock($order, $fixtureCaptureValue);

        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->fixtureIdManagerCreateAuthWithCaptureNoAuthFoundAndFailedAuthMock(
            $fixtureAuthorizeReferenceId,
            $fixtureCaptureValue,
            $expectedTransaction,
            $fixtureAmazonAuthorizeId
        );

        $amazonOrderRef = $this->fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId);

        $amazonOrderRef->expects($this->never())
            ->method('getAuthorizationDetails');

        $amazonOrderRef->expects($this->once())
            ->method('authorizeAndCapture')
            ->with(
                $this->equalTo($order),
                $this->equalTo($fixtureAuthorizeReferenceId),
                $this->equalTo($fixtureCaptureValue),
                $this->equalTo(false),
                $this->equalTo($fixtureSoftDescription)
            )
            ->will($this->throwException($expectedException));

        $config = $this->fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager);

        $transactionManager = $this->getTransactionManager($order);
        $transactionManager->expects($this->once())->method('getTransactionDataFromOrder')
            ->with($this->equalTo(\TPkgShopPaymentTransactionData::TYPE_PAYMENT), $this->equalTo(null))
            ->will($this->returnValue($expectedTransactionData));
        $transactionManager->expects($this->any())->method('addTransaction')->with($this->equalTo($expectedTransactionData))->will($this->returnValue($expectedTransaction));

        $payment = new AmazonPayment($config);

        $exception = null;
        try {
            $transaction = $payment->captureShipment($transactionManager, $order, $fixtureCaptureValue, $fixtureSoftDescription);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception, 'we expect a TPkgCmsException_LogAndMessage exception');
        $this->assertEquals(AmazonPayment::ERROR_CODE_API_ERROR, $exception->getMessageCode());
    }

    /**
     * @param $fixtureOrderValue
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureOrderMock($fixtureOrderValue)
    {
        $order = $this->getMockBuilder('TdbShopOrder')->getMock();
        $order->id = self::FIXTURE_SHOP_ORDER_ID;
        $order->fieldValueTotal = $fixtureOrderValue;

        return $order;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureTransactionMock()
    {
        $expectedTransaction = $this->getMockBuilder('TdbPkgShopPaymentTransaction')->getMock();
        $expectedTransaction->id = 'NEW-TRANSACTION-ID';

        return $expectedTransaction;
    }

    /**
     * @param \TdbShopOrder $order
     * @param $fixtureCaptureValue
     * @param array $orderItemList
     *
     * @return \TPkgShopPaymentTransactionData
     */
    protected function fixtureTransactionDataMock(\TdbShopOrder $order, $fixtureCaptureValue, array $orderItemList = null)
    {
        $expectedTransactionData = new \TPkgShopPaymentTransactionData($order, \TPkgShopPaymentTransactionData::TYPE_PAYMENT);
        $expectedTransactionData->setContext(new \TPkgShopPaymentTransactionContext('via capture on shipment'));
        $expectedTransactionData->setTotalValue($fixtureCaptureValue);
        if (null !== $orderItemList) {
            foreach ($orderItemList as $shopOrderItemId => $quantity) {
                $item = new \TPkgShopPaymentTransactionItemData();
                $item->setAmount($quantity)->setOrderItemId($shopOrderItemId)->setType(\TPkgShopPaymentTransactionItemData::TYPE_PRODUCT)->setValue('xxx');
                $expectedTransactionData->addItem($item);
            }
        }

        return $expectedTransactionData;
    }

    /**
     * @param $fixtureCaptureReferenceId
     * @param $fixtureCaptureValue
     * @param $expectedTransaction
     * @param $fixtureAmazonCaptureId
     * @param $fixtureAuthorizationValue
     * @param $fixtureAmazonOrderRefId
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureIdManagerMock(
        $fixtureAmazonAuthorizationId,
        $fixtureAuthorizationReferenceId,
        $fixtureCaptureReferenceId,
        $fixtureCaptureValue,
        $expectedTransaction,
        $fixtureAmazonCaptureId,
        $fixtureAuthorizationValue,
        $fixtureAmazonOrderRefId
    ) {
        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $expectedLocalCaptureIdObject = $this->getMockBuilder(
            '\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId'
        )->setConstructorArgs(
            array(
                IAmazonReferenceId::TYPE_CAPTURE,
                $fixtureCaptureReferenceId,
                $fixtureCaptureValue,
                $expectedTransaction->id,
            )
        )
            ->setMethods(array('setAmazonId'))
            ->getMock();
        $expectedLocalCaptureIdObject->expects($this->once())->method('setAmazonId')->with($this->equalTo($fixtureAmazonCaptureId));

        // setup local id manager
        $item = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, $fixtureAuthorizationReferenceId, $fixtureAuthorizationValue, null);
        $item->setAmazonId($fixtureAmazonAuthorizationId);
        $idList = new AmazonReferenceIdList($fixtureAmazonOrderRefId, IAmazonReferenceId::TYPE_AUTHORIZE);
        $idList->addItem($item);

        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalCaptureReferenceId', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))
            ->getMock();

        //$idManager->expects($this->any())->method('getAmazonOrderReferenceId')->will($this->returnValue())
        $idManager->expects($this->once())->method('createLocalCaptureReferenceId')->with(
            $this->equalTo($fixtureCaptureValue),
            $this->equalTo($expectedTransaction->id)
        )->will($this->returnValue($expectedLocalCaptureIdObject));

        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue($idList));

        $idManager->expects($this->exactly(2))->method('persist');

        return $idManager;
    }

    /**
     * @param $fixtureCaptureReferenceId
     * @param $fixtureCaptureValue
     * @param $expectedTransaction
     * @param $fixtureAmazonCaptureId
     * @param $fixtureAuthorizationValue
     * @param $fixtureAmazonOrderRefId
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureIdManagerForFailedCapturesMock(
        $fixtureAmazonAuthorizationId,
        $fixtureAuthorizationReferenceId,
        $fixtureCaptureReferenceId,
        $fixtureCaptureValue,
        $expectedTransaction,
        $fixtureAmazonCaptureId,
        $fixtureAuthorizationValue,
        $fixtureAmazonOrderRefId
    ) {
        /** @var $expectedLocalCaptureIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $expectedLocalCaptureIdObject = $this->getMockBuilder(
            '\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId'
        )->setConstructorArgs(
                array(
                    IAmazonReferenceId::TYPE_CAPTURE,
                    $fixtureCaptureReferenceId,
                    $fixtureCaptureValue,
                    $expectedTransaction->id,
                )
            )
            ->setMethods(array('setAmazonId'))
            ->getMock();
        $expectedLocalCaptureIdObject->expects($this->never())->method('setAmazonId');

        // setup local id manager
        $item = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, $fixtureAuthorizationReferenceId, $fixtureAuthorizationValue, null);
        $item->setAmazonId($fixtureAmazonAuthorizationId);
        $idList = new AmazonReferenceIdList($fixtureAmazonOrderRefId, IAmazonReferenceId::TYPE_AUTHORIZE);
        $idList->addItem($item);

        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalCaptureReferenceId', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))
            ->getMock();

        //$idManager->expects($this->any())->method('getAmazonOrderReferenceId')->will($this->returnValue())
        $idManager->expects($this->once())->method('createLocalCaptureReferenceId')->with(
            $this->equalTo($fixtureCaptureValue),
            $this->equalTo($expectedTransaction->id)
        )->will($this->returnValue($expectedLocalCaptureIdObject));

        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue($idList));

        $idManager->expects($this->once())->method('persist');

        return $idManager;
    }

    /**
     * @param $fixtureAuthorizeReferenceId
     * @param $fixtureCaptureValue
     * @param $expectedTransaction
     * @param $fixtureAmazonAuthorizeWithCaptureId
     * @param $fixtureAuthorizationValue
     * @param $fixtureAmazonOrderRefId
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureIdManagerCreateAuthWithCaptureMock(
        $fixtureAmazonAuthorizationId,
        $fixtureAuthorizationReferenceId,
        $fixtureAuthorizeReferenceId,
        $fixtureCaptureValue,
        $expectedTransaction,
        $fixtureAmazonAuthorizeWithCaptureId,
        $fixtureAuthorizationValue,
        $fixtureAmazonOrderRefId
    ) {
        // create the auth with capture request that will be created
        /** @var $expectedLocalAuthorizationIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $expectedLocalAuthorizationIdObject = $this->getMockBuilder(
            '\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId'
        )->setConstructorArgs(
                array(
                    IAmazonReferenceId::TYPE_AUTHORIZE,
                    $fixtureAuthorizeReferenceId,
                    $fixtureCaptureValue,
                    $expectedTransaction->id,
                )
            )
            ->setMethods(array('setAmazonId'))
            ->getMock();
        $expectedLocalAuthorizationIdObject->setCaptureNow(true);
        $expectedLocalAuthorizationIdObject->expects($this->once())->method('setAmazonId')->with($this->equalTo($fixtureAmazonAuthorizeWithCaptureId));

        // create the existing auth
        $item = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, $fixtureAuthorizationReferenceId, $fixtureAuthorizationValue, null);
        $item->setAmazonId($fixtureAmazonAuthorizationId);

        $idList = new AmazonReferenceIdList($fixtureAmazonOrderRefId, IAmazonReferenceId::TYPE_AUTHORIZE);
        $idList->addItem($item);
        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalAuthorizationReferenceIdWithCaptureNow', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))
            ->getMock();
        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue($idList));

        // $requestMode, $value, $transactionId
        $idManager->expects($this->once())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->with(
            $this->equalTo(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS),
            $this->equalTo($fixtureCaptureValue),
            $this->equalTo($expectedTransaction->id)
        )->will($this->returnValue($expectedLocalAuthorizationIdObject));

        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $idManager->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo($fixtureAuthorizeReferenceId), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));

        $idManager->expects($this->exactly(2))->method('persist');

        return $idManager;
    }

    /**
     * @param $fixtureAuthorizeReferenceId
     * @param $fixtureCaptureValue
     * @param $expectedTransaction
     * @param $fixtureAmazonAuthorizeId
     * @param $fixtureAuthorizationValue
     * @param $fixtureAmazonOrderRefId
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureIdManagerCreateAuthWithCaptureNoAuthFoundMock(
        $fixtureAuthorizeReferenceId,
        $fixtureCaptureValue,
        $expectedTransaction,
        $fixtureAmazonAuthorizeId
    ) {
        // create the auth with capture request that will be created
        /** @var $expectedLocalAuthorizationIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $expectedLocalAuthorizationIdObject = $this->getMockBuilder(
            '\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId'
        )->setConstructorArgs(
                array(
                    IAmazonReferenceId::TYPE_AUTHORIZE,
                    $fixtureAuthorizeReferenceId,
                    $fixtureCaptureValue,
                    $expectedTransaction->id,
                )
            )
            ->setMethods(array('setAmazonId'))
            ->getMock();
        $expectedLocalAuthorizationIdObject->setLocalId($fixtureAuthorizeReferenceId);
        $expectedLocalAuthorizationIdObject->setCaptureNow(true);
        $expectedLocalAuthorizationIdObject->expects($this->once())->method('setAmazonId')->with(
            $this->equalTo($fixtureAmazonAuthorizeId)
        );

        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalAuthorizationReferenceIdWithCaptureNow', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))
            ->getMock();
        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue(null));

        // $requestMode, $value, $transactionId
        $idManager->expects($this->once())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->with(
            $this->equalTo(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS),
            $this->equalTo($fixtureCaptureValue),
            $this->equalTo($expectedTransaction->id)
        )->will($this->returnValue($expectedLocalAuthorizationIdObject));

        $mockLocalWithCaptureAuthId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalWithCaptureAuthId->expects($this->once())->method('setAmazonId')->with($this->equalTo('AMAZON-CAPTURE-ID'));
        $idManager->expects($this->once())->method('findFromLocalReferenceId')->with($this->equalTo($fixtureAuthorizeReferenceId), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($mockLocalWithCaptureAuthId));

        $idManager->expects($this->exactly(2))->method('persist');

        return $idManager;
    }

    /**
     * @param $fixtureAuthorizeReferenceId
     * @param $fixtureCaptureValue
     * @param $expectedTransaction
     * @param $fixtureAmazonAuthorizeId
     * @param $fixtureAuthorizationValue
     * @param $fixtureAmazonOrderRefId
     *
     * @return AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureIdManagerCreateAuthWithCaptureNoAuthFoundAndFailedAuthMock(
        $fixtureAuthorizeReferenceId,
        $fixtureCaptureValue,
        $expectedTransaction,
        $fixtureAmazonAuthorizeId
    ) {
        // create the auth with capture request that will be created
        /** @var $expectedLocalAuthorizationIdObject AmazonReferenceId|\PHPUnit_Framework_MockObject_MockObject */
        $expectedLocalAuthorizationIdObject = $this->getMockBuilder(
            '\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId'
        )->setConstructorArgs(
                array(
                    IAmazonReferenceId::TYPE_AUTHORIZE,
                    $fixtureAuthorizeReferenceId,
                    $fixtureCaptureValue,
                    $expectedTransaction->id,
                )
            )
            ->setMethods(array('setAmazonId'))
            ->getMock();
        $expectedLocalAuthorizationIdObject->setLocalId($fixtureAuthorizeReferenceId);
        $expectedLocalAuthorizationIdObject->setCaptureNow(true);
        $expectedLocalAuthorizationIdObject->expects($this->never())->method('setAmazonId');

        /** @var $idManager AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject */
        $idManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')
            ->setMethods(array('persist', 'createLocalAuthorizationReferenceIdWithCaptureNow', 'getListOfAuthorizations', 'findFromLocalReferenceId'))
            ->setConstructorArgs(array(self::FIXTURE_AMAZON_ORDER_REFERENCE_ID, self::FIXTURE_SHOP_ORDER_ID))
            ->getMock();
        $idManager->expects($this->once())->method('getListOfAuthorizations')->will($this->returnValue(null));

        // $requestMode, $value, $transactionId
        $idManager->expects($this->once())->method('createLocalAuthorizationReferenceIdWithCaptureNow')->with(
            $this->equalTo(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS),
            $this->equalTo($fixtureCaptureValue),
            $this->equalTo($expectedTransaction->id)
        )->will($this->returnValue($expectedLocalAuthorizationIdObject));
        $idManager->expects($this->once())->method('persist');

        return $idManager;
    }

    /**
     * @param $fixtureAmazonOrderRefId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureAmazonOrderReferenceObjectMock($fixtureAmazonOrderRefId)
    {
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(
            array($this->getConfig(), $fixtureAmazonOrderRefId)
        )->setMethods(array('captureExistingAuthorization', 'getAuthorizationDetails', 'authorizeAndCapture'))
            ->getMock();

        return $amazonOrderRef;
    }

    /**
     * @param $fixtureAmazonOrderRefId
     * @param $amazonOrderRef
     * @param $idManager
     *
     * @return \ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function fixtureAmazonPaymentConfigMock($fixtureAmazonOrderRefId, $amazonOrderRef, $idManager)
    {
        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with(
            $this->equalTo($fixtureAmazonOrderRefId)
        )->will($this->returnValue($amazonOrderRef));
        $config->expects($this->any())->method('amazonReferenceIdManagerFactory')->will($this->returnValue($idManager));

        return $config;
    }
}
