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

require_once __DIR__.'/AbstractAmazonPayment.php';

class AbstractAmazonOrderReference extends AbstractAmazonPayment
{
    /**
     * @var \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order = null;

    protected function setUp()
    {
        parent::setUp();

        /** @var $shop \PHPUnit_Framework_MockObject_MockObject|\TdbShop */
        $shop = $this->getMockBuilder('TdbShop')->disableOriginalConstructor()->getMock();
        $shop->fieldName = 'test store';

        /** @var $currency \PHPUnit_Framework_MockObject_MockObject|\TdbPkgShopCurrency */
        $currency = $this->getMockBuilder('TdbPkgShopCurrency')->disableOriginalConstructor()->getMock();
        $currency->fieldIso4217 = 'EUR';

        /** @var $order \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();
        $order->expects($this->any())->method('getAmazonOrderReferenceId')->will($this->returnValue('ORDER-REFERENCE-ID'));
        $order->expects($this->any())->method('GetFieldShop')->will($this->returnValue($shop));
        $order->expects($this->any())->method('GetFieldPkgShopCurrency')->will($this->returnValue($currency));
        $order->fieldValueTotal = 1234.56;
        $order->fieldOrdernumber = 'TEST-ORDERNUMBER';
        $order->id = 'TEST-ORDER-ID';

        $this->order = $order;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->order = null;
    }
}
