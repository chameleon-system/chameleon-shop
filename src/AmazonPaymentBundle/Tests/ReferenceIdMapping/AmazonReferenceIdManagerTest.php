<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\ReferenceIdMapping;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdList;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

class AmazonReferenceIdManagerTest extends AbstractAmazonPayment
{
    private $amazonOrderReferenceId = 'S02-1974231-0772375';
    private $orderId = 'SOME-SHOP-ORDER-ID';

    public function test_create_and_search_new_authorization_reference_id_in_sync_and_asyn_mode()
    {
        $testRequestMode = array(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS, IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS);
        foreach ($testRequestMode as $requestMode) {
            $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);
            $value = 10;
            $transactionId = null;
            /** @var IAmazonReferenceId $item */
            $item = $manager->createLocalAuthorizationReferenceId($requestMode, $value, $transactionId);

            $this->assertEquals($value, $item->getValue());
            $this->assertEquals($transactionId, $item->getTransactionId());
            $this->assertEquals($requestMode, $item->getRequestMode(), 'expecting request mode '.$requestMode);
            $this->assertEquals(IAmazonReferenceId::TYPE_AUTHORIZE, $item->getType());
            $localId = $item->getLocalId();
            $this->assertFalse(empty($localId));

            // make sure the item is really in the manager

            $itemInManager = $manager->findFromLocalReferenceId($localId, IAmazonReferenceId::TYPE_AUTHORIZE);

            $this->assertEquals($item, $itemInManager);
        }
    }

    public function test_consecutive_find_from_local_reference_calls()
    {
        $manager = $this->fixtureGetManagerWithData();

        $localRefund = $manager->findFromLocalReferenceId('REFOUND-2', IAmazonReferenceId::TYPE_REFUND);
        $localAuth = $manager->findFromLocalReferenceId('AUTH-1', IAmazonReferenceId::TYPE_AUTHORIZE);

        $localRefundAgain = $manager->findFromLocalReferenceId('REFOUND-2', IAmazonReferenceId::TYPE_REFUND);

        $this->assertEquals($localRefund, $localRefundAgain);
        $this->assertEquals('REFOUND-2', $localRefund->getLocalId());
        $this->assertEquals('AUTH-1', $localAuth->getLocalId());
        $this->assertEquals('REFOUND-2', $localRefundAgain->getLocalId());
    }

    public function test_create_and_search_new_authorization_reference_id_with_capture_now_in_sync_and_asyn_mode()
    {
        $testRequestMode = array(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS, IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS);
        foreach ($testRequestMode as $requestMode) {
            $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);
            $value = 10;
            $transactionId = null;
            /** @var IAmazonReferenceId $item */
            $item = $manager->createLocalAuthorizationReferenceIdWithCaptureNow($requestMode, $value, $transactionId);

            $this->assertEquals($value, $item->getValue());
            $this->assertEquals($transactionId, $item->getTransactionId());
            $this->assertEquals($requestMode, $item->getRequestMode());
            $this->assertEquals(IAmazonReferenceId::TYPE_AUTHORIZE, $item->getType());
            $this->assertTrue($item->getCaptureNow());
            $localId = $item->getLocalId();
            $this->assertFalse(empty($localId));

            // make sure the item is really in the manager

            $itemInManager = $manager->findFromLocalReferenceId($localId, IAmazonReferenceId::TYPE_AUTHORIZE);

            $this->assertEquals($item, $itemInManager);

            // there should also be a matching capture entry

            $matchingCapture = $manager->findFromLocalReferenceId($localId, IAmazonReferenceId::TYPE_CAPTURE);

            $this->assertEquals($value, $matchingCapture->getValue());
            $this->assertEquals($transactionId, $matchingCapture->getTransactionId());
            $this->assertEquals($requestMode, $matchingCapture->getRequestMode());
            $this->assertEquals(IAmazonReferenceId::TYPE_CAPTURE, $matchingCapture->getType());
            $this->assertTrue($matchingCapture->getCaptureNow());
            $this->assertEquals($localId, $matchingCapture->getLocalId());
        }
    }

    public function test_create_and_search_new_capture_reference_id()
    {
        $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);
        $value = 10;
        $transactionId = null;
        /** @var IAmazonReferenceId $item */
        $item = $manager->createLocalCaptureReferenceId($value, $transactionId);

        $this->assertEquals($value, $item->getValue());
        $this->assertEquals($transactionId, $item->getTransactionId());
        $this->assertEquals(IAmazonReferenceId::TYPE_CAPTURE, $item->getType());
        $this->assertFalse($item->getCaptureNow());
        $localId = $item->getLocalId();
        $this->assertFalse(empty($localId));

        // make sure the item is really in the manager

        $itemInManager = $manager->findFromLocalReferenceId($localId, IAmazonReferenceId::TYPE_CAPTURE);

        $this->assertEquals($item, $itemInManager);
    }

    public function test_create_and_search_new_refund_reference_id()
    {
        $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);
        $value = 10;
        $transactionId = null;
        /** @var IAmazonReferenceId $item */
        $item = $manager->createLocalRefundReferenceId($value, $transactionId);

        $this->assertEquals($value, $item->getValue());
        $this->assertEquals($transactionId, $item->getTransactionId());
        $this->assertEquals(IAmazonReferenceId::TYPE_REFUND, $item->getType());
        $this->assertFalse($item->getCaptureNow());
        $localId = $item->getLocalId();
        $this->assertFalse(empty($localId));

        // make sure the item is really in the manager

        $itemInManager = $manager->findFromLocalReferenceId($localId, IAmazonReferenceId::TYPE_REFUND);

        $this->assertEquals($item, $itemInManager);
    }

    public function test_getListOfAuthorizations()
    {
        $manager = $this->fixtureGetManagerWithData();

        $expected = new AmazonReferenceIdList('AMAZON-ORDER-REF', IAmazonReferenceId::TYPE_AUTHORIZE);
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-1', 100, null);
        $item->setAmazonId('AMAZON-AUTH-1');
        $item->setCaptureNow(false);
        $item->setRequestMode(AmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected->addItem($item);

        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-2', 50, 'TRANSACTIONID');
        $item->setAmazonId('AMAZON-AUTH-2');
        $item->setCaptureNow(true);
        $item->setRequestMode(AmazonReferenceId::REQUEST_MODE_SYNCHRONOUS);
        $expected->addItem($item);

        $authList = $manager->getListOfAuthorizations();

        $this->assertEquals($expected, $authList);
    }

    public function test_getListOfAuthorizationsNoMatches()
    {
        $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);

        $authList = $manager->getListOfAuthorizations();

        $this->assertNull($authList);
    }

    public function test_getListOfCaptures()
    {
        $manager = $this->fixtureGetManagerWithData();

        $expected = new AmazonReferenceIdList('AMAZON-ORDER-REF', IAmazonReferenceId::TYPE_CAPTURE);
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_CAPTURE, 'CAPTURE-1', 80, null);
        $item->setAmazonId('AMAZON-CAPTURE-1');
        $expected->addItem($item);

        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_CAPTURE, 'AUTH-2', 50, 'TRANSACTIONID');
        $item->setAmazonId('AMAZON-CAPTURE-2');
        $expected->addItem($item);

        $authList = $manager->getListOfCaptures();

        $this->assertEquals($expected, $authList);
    }

    public function test_getListOfCapturesNoMatches()
    {
        $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);

        $authList = $manager->getListOfCaptures();

        $this->assertNull($authList);
    }

    private function fixtureGetManagerWithData()
    {
        $source = array();

        $source[] = array(
            'id' => '1',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'AUTH-1',
            'amazon_id' => 'AMAZON-AUTH-1',
            'value' => 100,
            'type' => '1',
            'request_mode' => '1',
            'capture_now' => '0',
            'pkg_shop_payment_transaction_id' => '',
        );

        $source[] = array(
            'id' => '2',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'AUTH-2',
            'amazon_id' => 'AMAZON-AUTH-2',
            'value' => 50,
            'type' => '1',
            'request_mode' => '2',
            'capture_now' => '1',
            'pkg_shop_payment_transaction_id' => 'TRANSACTIONID',
        );

        $source[] = array(
            'id' => '3',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'CAPTURE-1',
            'amazon_id' => 'AMAZON-CAPTURE-1',
            'value' => 80,
            'type' => '2',
            'request_mode' => '',
            'capture_now' => '0',
            'pkg_shop_payment_transaction_id' => '',
        );

        $source[] = array(
            'id' => '4',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'AUTH-2',
            'amazon_id' => 'AMAZON-CAPTURE-2',
            'value' => 50,
            'type' => '2',
            'request_mode' => '',
            'capture_now' => '0',
            'pkg_shop_payment_transaction_id' => 'TRANSACTIONID',
        );
        $source[] = array(
            'id' => '5',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'REFUND-1',
            'amazon_id' => 'AMAZON-REFUND-1',
            'value' => 10,
            'type' => '3',
            'request_mode' => '',
            'capture_now' => '0',
            'pkg_shop_payment_transaction_id' => '',
        );

        $source[] = array(
            'id' => '6',
            'shop_order_id' => 'ORDERID',
            'amazon_order_reference_id' => 'AMAZON-ORDER-REF',
            'local_id' => 'REFOUND-2',
            'amazon_id' => 'AMAZON-REFOUND-2',
            'value' => 20,
            'type' => '3',
            'request_mode' => '',
            'capture_now' => '0',
            'pkg_shop_payment_transaction_id' => '',
        );

        return AmazonReferenceIdManager::createFromRecordList($source);
    }
}
