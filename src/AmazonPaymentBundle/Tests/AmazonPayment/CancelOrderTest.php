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
use ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

class CancelOrderTest extends AbstractAmazonPayment
{
    const FIXTURE_CANCELLATION_REASON = 'cancellationReason';

    public function test_success()
    {
        $config = $this->getConfig();
        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderReferenceObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderReferenceObject->expects($this->once())->method('cancelOrderReference')
            ->with($this->equalTo(self::FIXTURE_CANCELLATION_REASON));

        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderReferenceObject));
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();

        $amazonPayment = new AmazonPayment($config);
        $amazonPayment->cancelOrder($transactionManager, $order, self::FIXTURE_CANCELLATION_REASON);
    }

    public function test_api_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR, array(
            'responseCode' => '123',
            'errorCode' => 'InternalServerError',
            'errorType' => 'Unknown',
            'message' => 'There was an unknown error in the service',
        ));
        $config = $this->getConfig();
        /** @var $amazonOrderRef IAmazonOrderReferenceObject|\PHPUnit_Framework_MockObject_MockObject */
        $amazonOrderReferenceObject = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\Interfaces\IAmazonOrderReferenceObject')->getMockForAbstractClass();
        $amazonOrderReferenceObject->expects($this->once())->method('cancelOrderReference')
            ->with($this->equalTo(self::FIXTURE_CANCELLATION_REASON))
            ->will($this->throwException($expectedException));

        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->will($this->returnValue($amazonOrderReferenceObject));
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')->disableOriginalConstructor()->getMock();

        $amazonPayment = new AmazonPayment($config);

        $exception = null;
        try {
            $amazonPayment->cancelOrder($transactionManager, $order, self::FIXTURE_CANCELLATION_REASON);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException->getMessageCode(), $exception->getMessageCode());
        $this->assertEquals($expectedException->getAdditionalData(), $exception->getAdditionalData());
    }
}
