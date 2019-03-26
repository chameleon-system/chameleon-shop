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
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\CoreBundle\ServiceLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class AmazonReferenceIdManagerPersistenceTest extends \PHPUnit_Extensions_Database_TestCase
{
    private $amazonOrderReferenceId = 'S02-1974231-0772375';
    private $orderId = '12345';
    const AMAZON_ID_MANAGER_TABLE = 'amazon_payment_id_mapping';

    // only instantiate pdo once for test clean-up/fixture load
    /**
     * @var \PDO
     */
    private static $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    public function test_persist()
    {
        $manager = new AmazonReferenceIdManager($this->amazonOrderReferenceId, $this->orderId);

        $auth1 = $manager->createLocalAuthorizationReferenceIdWithCaptureNow(IAmazonReferenceId::REQUEST_MODE_SYNCHRONOUS, 7.00, null);
        $auth1->setAmazonId('MY-AMAZON-ID');
        $auth2 = $manager->createLocalAuthorizationReferenceId(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS, 8.00, 'transactionId');
        $capture1 = $manager->createLocalCaptureReferenceId(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS, $auth1->getLocalId(), 9.00, 'transactionId2');
        $refund = $manager->createLocalRefundReferenceId(10.00, 'transactionId3');

        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        $manager->persist($dbal);

        // then load and check if equal

        $new = AmazonReferenceIdManager::createFromShopOrderId($dbal, $this->orderId);

        $this->assertEquals($manager, $new);

        $new->delete($dbal);

        // should only contain one record
        $remaining = $dbal->fetchAll('SELECT id, local_id, shop_order_id,amazon_order_reference_id, amazon_id, value, type, capture_now, request_mode, pkg_shop_payment_transaction_id  FROM '.self::AMAZON_ID_MANAGER_TABLE);
        $expected = array(
            array(
                'id' => '1234567890',
                'local_id' => 'NOT-RELATED',
                'shop_order_id' => 'NOT-RELATED-ORDER',
                'amazon_order_reference_id' => 'SOME-ORDER-REF-NOT-RELATED',
                'amazon_id' => 'SOME-UNRELATED-AUTH-ID',
                'value' => 200.00,
                'type' => '1',
                'capture_now' => '0',
                'request_mode' => '1',
                'pkg_shop_payment_transaction_id' => 'NOT-RELATED-TRANSACTION-ID',
            ),
        );
        $this->assertEquals($expected, $remaining);
    }

    public function test_load_from_row_and_find_from_local_id()
    {
        $expected = array();
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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-1', 100, null);
        $item->setAmazonId('AMAZON-AUTH-1');
        $item->setCaptureNow(false);
        $item->setRequestMode(AmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected[] = $item;

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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-2', 50, 'TRANSACTIONID');
        $item->setAmazonId('AMAZON-AUTH-2');
        $item->setCaptureNow(true);
        $item->setRequestMode(AmazonReferenceId::REQUEST_MODE_SYNCHRONOUS);
        $expected[] = $item;

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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_CAPTURE, 'CAPTURE-1', 80, null);
        $item->setAmazonId('AMAZON-CAPTURE-1');
        $item->setCaptureNow(false);
        $item->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected[] = $item;

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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_CAPTURE, 'AUTH-2', 50, 'TRANSACTIONID');
        $item->setAmazonId('AMAZON-CAPTURE-2');
        $item->setCaptureNow(false);
        $item->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected[] = $item;

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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_REFUND, 'REFUND-1', 10, null);
        $item->setAmazonId('AMAZON-REFUND-1');
        $item->setCaptureNow(false);
        $item->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected[] = $item;

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
        $item = new AmazonReferenceId(AmazonReferenceId::TYPE_REFUND, 'REFOUND-2', 20, null);
        $item->setAmazonId('AMAZON-REFOUND-2');
        $item->setCaptureNow(false);
        $item->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);
        $expected[] = $item;

        $manager = AmazonReferenceIdManager::createFromRecordList($source);

        /** @var $item AmazonReferenceId */
        foreach ($expected as $item) {
            $find = $manager->findFromLocalReferenceId($item->getLocalId(), $item->getType());
            $this->assertEquals($item, $find, 'expected '.print_r($item, true)."\nbut got\n".print_r($find, true));
        }
    }

    public function test_load_from_shop_order_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromShopOrderId($dbal, $this->orderId);

        // expecting 2 entries

        $expectedA = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, '1234', 400, 'fdgksfkhjsfhjfgh');
        $expectedA->setAmazonId('57675465465');
        $expectedA->setCaptureNow(false);
        $expectedA->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);

        $expectedB = new AmazonReferenceId(IAmazonReferenceId::TYPE_CAPTURE, '2234', 300, 'fdgksfkhjsxxx');
        $expectedB->setAmazonId('545665465465');
        $expectedB->setCaptureNow(false);
        $expectedB->setRequestMode(IAmazonReferenceId::REQUEST_MODE_ASYNCHRONOUS);

        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $foundA = $manager->findFromLocalReferenceId('1234', IAmazonReferenceId::TYPE_AUTHORIZE);
        $this->assertEquals($expectedA, $foundA);

        $foundB = $manager->findFromLocalReferenceId('2234', IAmazonReferenceId::TYPE_CAPTURE);
        $this->assertEquals($expectedB, $foundB);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_load_from_shop_order_id_not_found()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromShopOrderId($dbal, 'NOTFOUND');
    }

    public function test_load_from_order_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromOrderReferenceId($dbal, 'S02-1974231-0772375');

        // expecting 2 entries

        $expectedA = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, '1234', 400, 'fdgksfkhjsfhjfgh');
        $expectedA->setAmazonId('57675465465');

        $expectedB = new AmazonReferenceId(IAmazonReferenceId::TYPE_CAPTURE, '2234', 300, 'fdgksfkhjsxxx');
        $expectedB->setAmazonId('545665465465');

        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $foundA = $manager->findFromLocalReferenceId('1234', IAmazonReferenceId::TYPE_AUTHORIZE);
        $this->assertEquals($expectedA, $foundA);

        $foundB = $manager->findFromLocalReferenceId('2234', IAmazonReferenceId::TYPE_CAPTURE);
        $this->assertEquals($expectedB, $foundB);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_load_from_order_reference_id_not_found()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromOrderReferenceId($dbal, 'NOTFOUND');
    }

    /**
     * returns items in before.yml that match our sample order.
     *
     * @return array
     */
    private function helperGetExpectedItemList()
    {
        $expected = array();
        $expectedObject = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, '1234', 400, 'fdgksfkhjsfhjfgh');
        $expectedObject->setAmazonId('57675465465');
        $expected[] = array('type' => IAmazonReferenceId::TYPE_AUTHORIZE, 'localid' => '1234', 'object' => $expectedObject);

        $expectedObject = new AmazonReferenceId(IAmazonReferenceId::TYPE_CAPTURE, '2234', 300, 'fdgksfkhjsxxx');
        $expectedObject->setAmazonId('545665465465');
        $expected[] = array('type' => IAmazonReferenceId::TYPE_CAPTURE, 'localid' => '2234', 'object' => $expectedObject);

        $expectedObject = new AmazonReferenceId(IAmazonReferenceId::TYPE_REFUND, 'REFUND-REF-ID', 50, 'REFUND-TRANSACTION-ID');
        $expectedObject->setAmazonId('REFUND-AMAZON-ID');
        $expected[] = array('type' => IAmazonReferenceId::TYPE_REFUND, 'localid' => 'REFUND-REF-ID', 'object' => $expectedObject);

        $expectedObject = new AmazonReferenceId(IAmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-WITH-CAPTURE-NOW', 200, 'AUTH-WITH-CAPTURE-TRANSACTION-ID');
        $expectedObject->setAmazonId('AMAZON-AUTH-WITH-CAPTURE-ID');
        $expectedObject->setCaptureNow(true);
        $expected[] = array('type' => IAmazonReferenceId::TYPE_AUTHORIZE, 'localid' => 'AUTH-WITH-CAPTURE-NOW', 'object' => $expectedObject);

        $expectedObject = new AmazonReferenceId(IAmazonReferenceId::TYPE_CAPTURE, 'AUTH-WITH-CAPTURE-NOW', 200, 'AUTH-WITH-CAPTURE-TRANSACTION-ID');
        $expectedObject->setAmazonId('AMAZON-CAPTURE-ID');
        $expectedObject->setCaptureNow(true);
        $expected[] = array('type' => IAmazonReferenceId::TYPE_CAPTURE, 'localid' => 'AUTH-WITH-CAPTURE-NOW', 'object' => $expectedObject);

        return $expected;
    }

    public function test_load_from_authorization_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, '2234');
        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $expected = $this->helperGetExpectedItemList();
        foreach ($expected as $expectedData) {
            $found = $manager->findFromLocalReferenceId($expectedData['localid'], $expectedData['type']);
            $this->assertEquals($expectedData['object'], $found);
        }
    }

    public function test_load_from_capture_now_authorization_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, 'AUTH-WITH-CAPTURE-NOW');
        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $expected = $this->helperGetExpectedItemList();
        foreach ($expected as $expectedData) {
            $found = $manager->findFromLocalReferenceId($expectedData['localid'], $expectedData['type']);
            $this->assertEquals($expectedData['object'], $found);
        }
    }

    public function test_load_from_capture_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, '2234');
        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $expected = $this->helperGetExpectedItemList();
        foreach ($expected as $expectedData) {
            $found = $manager->findFromLocalReferenceId($expectedData['localid'], $expectedData['type']);
            $this->assertEquals($expectedData['object'], $found);
        }
    }

    public function test_load_from_capture_now_capture_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, 'AUTH-WITH-CAPTURE-NOW');
        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $expected = $this->helperGetExpectedItemList();
        foreach ($expected as $expectedData) {
            $found = $manager->findFromLocalReferenceId($expectedData['localid'], $expectedData['type']);
            $this->assertEquals($expectedData['object'], $found);
        }
    }

    public function test_load_from_refund_reference_id()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, 'REFUND-REF-ID');
        $this->assertEquals($this->amazonOrderReferenceId, $manager->getAmazonOrderReferenceId());

        $expected = $this->helperGetExpectedItemList();
        foreach ($expected as $expectedData) {
            $found = $manager->findFromLocalReferenceId($expectedData['localid'], $expectedData['type']);
            $this->assertEquals($expectedData['object'], $found);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_load_from_local_not_found()
    {
        $dbal = \Doctrine\DBAL\DriverManager::getConnection(array('driver' => 'pdo_mysql', 'pdo' => self::$pdo, 'charset' => 'UTF8'));

        /** @var $manager AmazonReferenceIdManager */
        $manager = AmazonReferenceIdManager::createFromLocalId($dbal, '223454545');
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $containerBuilder = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
        try {
            $loader->load(__DIR__.'/../config.yml');
        } catch (\InvalidArgumentException $e) {
            // services yml not found
        }
        $containerBuilder->compile();
        ServiceLocator::setContainer($containerBuilder);

//        $chameleon = new \chameleon();
//        $chameleon->setRequestType($chameleon::REQUEST_TYPE_FRONTEND);
//        $chameleon->run();

        if (null == self::$pdo) {
            self::$pdo = ServiceLocator::get('testpdo');
            $stm = self::$pdo->prepare(file_get_contents(__DIR__.'/../fixtures/ReferenceIdMapping/ddl.sql'));
            $stm->execute();
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$pdo->query('drop table '.self::AMAZON_ID_MANAGER_TABLE);
    }

    final public function getConnection()
    {
        if (null === $this->conn) {
            $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            dirname(__FILE__).'/../fixtures/ReferenceIdMapping/before.yml'
        );
    }
}
