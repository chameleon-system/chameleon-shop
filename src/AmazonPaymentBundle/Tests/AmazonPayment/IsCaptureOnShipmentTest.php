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

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

use ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentHandler;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

class IsCaptureOnShipmentTest extends AbstractAmazonPayment
{
    public function test_no_value_set_with_config_is_capture_on_shipment_true()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(true));

        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->setAmazonPaymentGroupConfig($config);

        $this->assertTrue($paymentHandler->isCaptureOnShipment());
    }

    public function test_no_value_set_with_config_is_capture_on_shipment_false()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(false));

        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->setAmazonPaymentGroupConfig($config);

        $this->assertFalse($paymentHandler->isCaptureOnShipment());
    }

    public function test_stored_as_1()
    {
        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->SetPaymentUserData(array(AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT => '1'));

        $this->assertTrue($paymentHandler->isCaptureOnShipment());
    }

    public function test_stored_as_0()
    {
        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->SetPaymentUserData(array(AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT => '0'));

        $this->assertFalse($paymentHandler->isCaptureOnShipment());
    }
}
