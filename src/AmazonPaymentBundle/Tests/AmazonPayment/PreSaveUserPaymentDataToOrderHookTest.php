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

class PreSaveUserPaymentDataToOrderHookTest extends AbstractAmazonPayment
{
    public function test_set_to_pay_on_shipment()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(true));

        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->setAmazonPaymentGroupConfig($config);

        $reflection = new \ReflectionClass(get_class($paymentHandler));
        $method = $reflection->getMethod('PreSaveUserPaymentDataToOrderHook');
        $method->setAccessible(true);

        $input = array('foo' => 'bar');
        $parameters = array($input);
        $result = $method->invokeArgs($paymentHandler, $parameters);

        $expected = $input;
        $expected[AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT] = '1';

        $this->assertEquals($expected, $result);
    }

    public function test_set_to_pay_on_order()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(false));

        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->setAmazonPaymentGroupConfig($config);

        $reflection = new \ReflectionClass(get_class($paymentHandler));
        $method = $reflection->getMethod('PreSaveUserPaymentDataToOrderHook');
        $method->setAccessible(true);

        $input = array('foo' => 'bar');
        $parameters = array($input);
        $result = $method->invokeArgs($paymentHandler, $parameters);

        $expected = $input;
        $expected[AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT] = '0';

        $this->assertEquals($expected, $result);
    }

    public function test_overwrite_existing_value()
    {
        $config = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig')->disableOriginalConstructor()->getMock();
        $config->expects($this->any())->method('isCaptureOnShipment')->will($this->returnValue(true));

        $paymentHandler = new AmazonPaymentHandler();
        $paymentHandler->setAmazonPaymentGroupConfig($config);

        $reflection = new \ReflectionClass(get_class($paymentHandler));
        $method = $reflection->getMethod('PreSaveUserPaymentDataToOrderHook');
        $method->setAccessible(true);

        $input = array('foo' => 'bar', AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT => '0');
        $parameters = array($input);
        $result = $method->invokeArgs($paymentHandler, $parameters);

        $expected = $input;
        $expected[AmazonPaymentHandler::PARAMETER_IS_PAYMENT_ON_SHIPMENT] = '1';

        $this->assertEquals($expected, $result);
    }
}
