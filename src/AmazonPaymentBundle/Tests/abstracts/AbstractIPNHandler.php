<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\abstracts;

use ChameleonSystem\AmazonPaymentBundle\AmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\IPN\AmazonIPNHandler;
use ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager;

require_once __DIR__.'/AbstractAmazonPayment.php';

abstract class AbstractIPNHandler extends AbstractAmazonPayment
{
    /**
     * @var \TPkgShopPaymentIPNRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ipnRequest = null;

    /**
     * @var AmazonIPNHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ipnHandler = null;

    /**
     * @var AmazonReferenceIdManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $idReferenceManager = null;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_MockObject_MockObject $request */
        $this->ipnRequest = $this->getMockBuilder('\TPkgShopPaymentIPNRequest')->disableOriginalConstructor()->getMock();
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $order->expects($this->any())->method('GetSQLWithTablePrefix')->will($this->returnValue(array('shop_order__ordernumber' => 'SHOP-ORDER-ID')));
        $this->ipnRequest->expects($this->any())->method('getOrder')->will($this->returnValue($order));

        /** @var $IPNHandler AmazonIPNHandler|\PHPUnit_Framework_MockObject_MockObject */
        $this->ipnHandler = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\IPN\AmazonIPNHandler')->setMethods(array('sentOKHeader', 'getMailProfile', 'updateShopOrderBillingAddress', 'getTransactionFromRequest', 'confirmTransaction'))->getMock();
        $this->ipnHandler->expects($this->once())->method('sentOKHeader')->will($this->returnValue(null));

        $this->idReferenceManager = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceIdManager')->setConstructorArgs(array('AMAZON-ORDER-REFERENCE', 'SHOP-ORDER-ID'))->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->ipnRequest = null;
        $this->ipnHandler = null;

        $this->idReferenceManager = null;
    }

    protected function helperAddIPNRequest(\OffAmazonPaymentsNotifications_Notification $notificationObject, AmazonReferenceIdManager $idManager)
    {
        $payload = array(
            'amazonNotificationObject' => $notificationObject,
            'amazonReferenceIdManager' => $idManager,
        );
        $this->ipnRequest->expects($this->any())->method('getRequestPayload')->will($this->returnValue($payload));
    }

    /**
     * @param string      $state
     * @param string|null $reasonCode
     * @param string|null $reasonDescription
     */
    protected function helperAddIPNMailObject($state, $reasonCode = null, $reasonDescription = null)
    {
        $statusData = array('state' => $state);
        if (null !== $reasonCode) {
            $statusData['reasonCode'] = $reasonCode;
        }
        if (null !== $reasonDescription) {
            $statusData['reasonDescription'] = $reasonDescription;
        }

        $mailMock = $this->getMockBuilder('TdbDataMailProfile')->disableOriginalConstructor()->getMock();
        $mailMock->expects($this->once())->method('SendUsingObjectView')->will($this->returnValue(true));
        $mailMock->expects($this->once())->method('AddDataArray')->with($this->equalTo(array('shop_order__ordernumber' => 'SHOP-ORDER-ID')));
        $mailMock->expects($this->once())->method('AddData')->with($this->equalTo('status'), $this->equalTo($statusData));

        $this->ipnHandler->expects($this->once())->method('getMailProfile')->with($this->equalTo(AmazonPayment::MAIL_PROFILE_IPN_ERROR))->will($this->returnValue($mailMock));
    }
}
