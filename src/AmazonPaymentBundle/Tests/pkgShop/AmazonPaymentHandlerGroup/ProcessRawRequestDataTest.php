<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\pkgShop\AmazonPaymentHandlerGroup;

use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\pkgShop\db\AmazonShopPaymentHandlerGroup;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../abstracts/AbstractAmazonPayment.php';

class ProcessRawRequestDataTest extends AbstractAmazonPayment
{
    /**
     * we expect
     * 1. an OffAmazonPaymentsNotifications_Model_OrderReferenceNotification Object
     * 2. an IPN Manager loaded via the order AmazonOrderReferenceId
     * 3. no localId.
     */
    public function test_order_reference_ipn()
    {
        $request = AmazonPaymentFixturesFactory::getIPNRequest('OrderReferenceNotification.post');
        $expectedIPNObject = AmazonPaymentFixturesFactory::getIPNOrderReferenceNotification('Open.xml');
        $this->helperStubIPNApi($request, $expectedIPNObject);

        $expectedIdManager = new AmazonReferenceIdManager('AMAZON-ORDER-REFERENCE', 'SOME-SHOP-ORDER-ID');

        //amazonOrderReferenceObjectFactory
        $this->getConfig()->expects($this->once())->method('amazonReferenceIdManagerFactory')
            ->with($this->anything(), $this->equalTo(AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_AMAZON_ORDER_REFERENCE_ID), $this->equalTo('AMAZON-ORDER-REFERENCE'))
            ->will($this->returnValue($expectedIdManager));

        /** @var $payment AmazonShopPaymentHandlerGroup|PHPUnit_Framework_MockObject_MockObject */
        $payment = $this->helperGetPaymentGroupHandler($request);

        $rawData = $payment->processRawRequestData(array());

        $this->assertTrue(isset($rawData['amazonNotificationObject']), 'no amazonNotificationObject');
        $this->assertTrue(isset($rawData['amazonReferenceIdManager']), 'no amazonReferenceIdManager');
        $this->assertFalse(isset($rawData['amazonLocalReferenceId']), 'there should not be a amazonLocalReferenceId');

        $this->assertEquals($expectedIPNObject, $rawData['amazonNotificationObject']);
        $this->assertEquals($expectedIdManager, $rawData['amazonReferenceIdManager']);
    }

    /**
     * we expect
     * 1. an OffAmazonPaymentsNotifications_Model_AuthorizationNotification Object
     * 2. an IPN Manager loaded via the order AuthorizationReferenceId
     * note: it does not matter if the request was synchronous or not or if it was in combination with a capture now.
     */
    public function test_authorization_ipn()
    {
        $request = AmazonPaymentFixturesFactory::getIPNRequest('AuthorizationNotification.post');
        $expectedIPNObject = AmazonPaymentFixturesFactory::getIPNAuthorizationNotification('Open.xml');
        $this->helperStubIPNApi($request, $expectedIPNObject);

        $expectedId = new AmazonReferenceId(AmazonReferenceId::TYPE_AUTHORIZE, 'AUTH-REF-ID', 12.23, null);
        /** @var $expectedIdManager AmazonReferenceIdManager|PHPUnit_Framework_MockObject_MockObject */
        $expectedIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->setConstructorArgs(array('AMAZON-ORDER-REFERENCE', 'SOME-SHOP-ORDER-ID'))->getMock();
        $expectedIdManager->expects($this->any())->method('findFromLocalReferenceId')->with($this->equalTo('AUTH-REF-ID'), $this->equalTo(IAmazonReferenceId::TYPE_AUTHORIZE))->will($this->returnValue($expectedId));

        //amazonOrderReferenceObjectFactory
        $this->getConfig()->expects($this->once())->method('amazonReferenceIdManagerFactory')
            ->with($this->anything(), $this->equalTo(AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID), $this->equalTo('AUTH-REF-ID'))
            ->will($this->returnValue($expectedIdManager));

        /** @var $payment AmazonShopPaymentHandlerGroup|PHPUnit_Framework_MockObject_MockObject */
        $payment = $this->helperGetPaymentGroupHandler($request);

        $rawData = $payment->processRawRequestData(array());

        $this->assertTrue(isset($rawData['amazonNotificationObject']), 'no amazonNotificationObject');
        $this->assertTrue(isset($rawData['amazonReferenceIdManager']), 'no amazonReferenceIdManager');
        $this->assertTrue(isset($rawData['amazonLocalReferenceId']), 'no amazonLocalReferenceId');

        $this->assertEquals($expectedIPNObject, $rawData['amazonNotificationObject']);
        $this->assertEquals($expectedIdManager, $rawData['amazonReferenceIdManager']);
        $this->assertEquals($expectedId, $rawData['amazonLocalReferenceId']);
    }

    /**
     * we expect
     * 1. an OffAmazonPaymentsNotifications_Model_CaptureNotification Object
     * 2. an IPN Manager loaded via the order AuthorizationReferenceId which needs to be loaded via api using AmazonCaptureId
     * note: it does not matter if this was a request with capture now or not, or if it was synchronous.
     */
    public function test_capture_ipn()
    {
        $request = AmazonPaymentFixturesFactory::getIPNRequest('CaptureNotification.post');
        $expectedIPNObject = AmazonPaymentFixturesFactory::getIPNCaptureNotification('Completed.xml');
        $this->helperStubIPNApi($request, $expectedIPNObject);

        $expectedId = new AmazonReferenceId(AmazonReferenceId::TYPE_CAPTURE, 'AMAZON-CAPTURE-REFERENCE-ID', 12.23, '12345');
        /** @var $expectedIdManager AmazonReferenceIdManager|PHPUnit_Framework_MockObject_MockObject */
        $expectedIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->setConstructorArgs(array('AMAZON-ORDER-REFERENCE', 'SOME-SHOP-ORDER-ID'))->getMock();
        $expectedIdManager->expects($this->any())->method('findFromLocalReferenceId')->with($this->equalTo('AMAZON-CAPTURE-REFERENCE-ID'), $this->equalTo(IAmazonReferenceId::TYPE_CAPTURE))->will($this->returnValue($expectedId));

        //amazonOrderReferenceObjectFactory
        $this->getConfig()->expects($this->once())->method('amazonReferenceIdManagerFactory')
            ->with($this->anything(), $this->equalTo(AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID), $this->equalTo('AMAZON-CAPTURE-REFERENCE-ID'))
            ->will($this->returnValue($expectedIdManager));

        /** @var $payment AmazonShopPaymentHandlerGroup|PHPUnit_Framework_MockObject_MockObject */
        $payment = $this->helperGetPaymentGroupHandler($request);

        $rawData = $payment->processRawRequestData(array());

        $this->assertTrue(isset($rawData['amazonNotificationObject']), 'no amazonNotificationObject');
        $this->assertTrue(isset($rawData['amazonReferenceIdManager']), 'no amazonReferenceIdManager');
        $this->assertTrue(isset($rawData['amazonLocalReferenceId']), 'no amazonLocalReferenceId');

        $this->assertEquals($expectedIPNObject, $rawData['amazonNotificationObject']);
        $this->assertEquals($expectedIdManager, $rawData['amazonReferenceIdManager']);
        $this->assertEquals($expectedId, $rawData['amazonLocalReferenceId']);
    }

    /**
     * we expect
     * 1. an OffAmazonPaymentsNotifications_Model_RefundNotification Object
     * 2. an IPN Manager loaded via the order RefundReferenceId which needs to be loaded via api using AmazonCaptureId.
     */
    public function test_refund_ipn()
    {
        $request = AmazonPaymentFixturesFactory::getIPNRequest('RefundNotification.post');
        $expectedIPNObject = AmazonPaymentFixturesFactory::getIPNRefundNotification('Completed.xml');
        $this->helperStubIPNApi($request, $expectedIPNObject);

        // the local id may be known - or not
        $expectedId = new AmazonReferenceId(AmazonReferenceId::TYPE_REFUND, 'REFUND-REFERENCE-ID', 12.23, null);
        /** @var $expectedIdManager AmazonReferenceIdManager|PHPUnit_Framework_MockObject_MockObject */
        $expectedIdManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->setConstructorArgs(array('AMAZON-ORDER-REFERENCE', 'SOME-SHOP-ORDER-ID'))->getMock();
        $expectedIdManager->expects($this->any())->method('findFromLocalReferenceId')->with($this->equalTo('REFUND-REFERENCE-ID'), $this->equalTo(IAmazonReferenceId::TYPE_REFUND))->will($this->returnValue($expectedId));

        //amazonOrderReferenceObjectFactory
        $this->getConfig()->expects($this->once())->method('amazonReferenceIdManagerFactory')
            ->with($this->anything(), $this->equalTo(AmazonPaymentGroupConfig::AMAZON_REFERENCE_ID_MANAGER_FACTORY_TYPE_LOCAL_ID), $this->equalTo('REFUND-REFERENCE-ID'))
            ->will($this->returnValue($expectedIdManager));

        /** @var $payment AmazonShopPaymentHandlerGroup|PHPUnit_Framework_MockObject_MockObject */
        $payment = $this->helperGetPaymentGroupHandler($request);

        $rawData = $payment->processRawRequestData(array());

        $this->assertTrue(isset($rawData['amazonNotificationObject']), 'no amazonNotificationObject');
        $this->assertTrue(isset($rawData['amazonReferenceIdManager']), 'no amazonReferenceIdManager');
        $this->assertTrue(isset($rawData['amazonLocalReferenceId']), 'no amazonLocalReferenceId');

        $this->assertEquals($expectedIPNObject, $rawData['amazonNotificationObject']);
        $this->assertEquals($expectedIdManager, $rawData['amazonReferenceIdManager']);
        $this->assertEquals($expectedId, $rawData['amazonLocalReferenceId']);
    }

    /**
     * @param Request $request
     *
     * @return AmazonShopPaymentHandlerGroup|PHPUnit_Framework_MockObject_MockObject
     */
    protected function helperGetPaymentGroupHandler(Request $request)
    {
        $payment = $this->getMockBuilder('ChameleonSystem\AmazonPaymentBundle\pkgShop\db\AmazonShopPaymentHandlerGroup')->setMethods(
            array('getAmazonConfig', 'getRequest', 'getRequestHeader')
        )->getMock();
        $payment->expects($this->once())->method('getAmazonConfig')->will($this->returnValue($this->getConfig()));
        $payment->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $payment->expects($this->any())->method('getRequestHeader')->will($this->returnValue($request->server->all()));

        return $payment;
    }

    protected function helperStubIPNApi(Request $request, $expectedIPNObject)
    {
        // we can"t parse the json, since that would require interaction with amazon to verify the signature. so we stub the AmazonIPNAPI object
        /** @var $amazonIPNApiStub \OffAmazonPaymentsNotifications_Client|PHPUnit_Framework_MockObject_MockObject */
        $amazonIPNApiStub = $this->getMockBuilder('OffAmazonPaymentsNotifications_Client')->disableOriginalConstructor()->getMock();

        $amazonIPNApiStub->expects($this->any())->method('parseRawMessage')
            ->with($this->equalTo($request->server->all()), $this->equalTo($request->getContent()))
            ->will($this->returnValue($expectedIPNObject));
        $this->getConfig()->expects($this->any())->method('getAmazonIPNAPI')->will($this->returnValue($amazonIPNApiStub));
    }
}
