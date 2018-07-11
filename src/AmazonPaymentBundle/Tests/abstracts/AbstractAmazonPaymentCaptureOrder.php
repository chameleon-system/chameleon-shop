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
use ChameleonSystem\AmazonPaymentBundle\Exceptions\AmazonAuthorizationDeclinedException;
use ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentHandler;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;

require_once __DIR__.'/AbstractAmazonPayment.php';

abstract class AbstractAmazonPaymentCaptureOrder extends AbstractAmazonPayment
{
    /**
     * @param $includePhysicalProducts
     * @param $includeDownloads
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\TdbShopOrder
     */
    protected function createOrderMock($includePhysicalProducts, $includeDownloads)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\TdbShopOrder $order */
        $order = $this->getMockBuilder('TdbShopOrder')->setMethods(array('SaveFieldsFast', 'GetFieldShopOrderItemList', 'GetPaymentHandler', 'GetFieldPkgShopCurrency'))->getMock();

        $order->expects($this->any())->method('GetFieldPkgShopCurrency')->will($this->returnValue($this->helperCreateCurrency('EURO', 'EUR', '€')));

        /** @var $paymentHandler AmazonPaymentHandler|\PHPUnit_Framework_MockObject_MockObject */
        $paymentHandler = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentHandler')->disableOriginalConstructor()->getMock();
        $paymentHandler->expects($this->any())->method('getAmazonOrderReferenceId')->will($this->returnValue('AMAZON-ORDER-REF-ID'));

        $order->expects($this->any())->method('GetPaymentHandler')->will($this->returnValue($paymentHandler));

        $orderItemList = new \TIterator();

        if ($includeDownloads) {
            $orderItemList->AddItem($this->helperCreateOrderItem(2, 4.5, true));
            $orderItemList->AddItem($this->helperCreateOrderItem(1, 5, true));
            $orderItemList->AddItem($this->helperCreateOrderItem(1, 1.5, true));
        }

        if ($includePhysicalProducts) {
            $orderItemList->AddItem($this->helperCreateOrderItem(1, 3.5, false));
            $orderItemList->AddItem($this->helperCreateOrderItem(3, 51, false));
            $orderItemList->AddItem($this->helperCreateOrderItem(1, 2.5, false));
        }

        $order->LoadFromRow($this->helperCreateOrderData($orderItemList, 0, 0));

        $order->expects($this->any())->method('GetFieldShopOrderItemList')->will($this->returnValue($orderItemList));

        return $order;
    }

    protected function helperGetOrderTotal(\TdbShopOrder $order, $includePhysicalProducts, $includeDownloads)
    {
        if ($includePhysicalProducts && $includeDownloads) {
            return $order->fieldValueTotal;
        }
        if ($includePhysicalProducts) {
            $total = 0;
            $orderItems = $order->GetFieldShopOrderItemList();
            /** @var \TdbShopOrderItem $orderItem */
            while ($orderItem = $orderItems->Next()) {
                if (false === $orderItem->isDownload()) {
                    $total += $orderItem->fieldOrderPriceTotal;
                }
            }
            $orderItems->GoToStart();

            return $total;
        }
        if ($includeDownloads) {
            $total = 0;
            $orderItems = $order->GetFieldShopOrderItemList();
            /** @var \TdbShopOrderItem $orderItem */
            while ($orderItem = $orderItems->Next()) {
                if (true === $orderItem->isDownload()) {
                    $total += $orderItem->fieldOrderPriceTotal;
                }
            }
            $orderItems->GoToStart();

            return $total;
        }

        return 0;
    }

    protected function helperGetTransactionManagerForOrder(\TdbShopOrder $order, array $methodsToOverwrite = array())
    {
        $methodsToOverwrite[] = 'getBillableProducts';
        $methodsToOverwrite[] = 'getRefundableProducts';

        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->getMockBuilder('TPkgShopPaymentTransactionManager')
            ->setConstructorArgs(array($order))
            ->setMethods($methodsToOverwrite)
            ->getMock();
        //$aProductList[$oOrderItem->id] = $iRemaining;
        $aProductList = array();
        $products = $order->GetFieldShopOrderItemList();
        $products->GoToStart();
        while ($product = $products->Next()) {
            $aProductList[$product->id] = $product->fieldOrderAmount;
        }
        $products->GoToStart();
        $transactionManager->expects($this->any())->method('getRefundableProducts')->will($this->returnValue($aProductList));
        $transactionManager->expects($this->any())->method('getBillableProducts')->will($this->returnValue($aProductList));

        return $transactionManager;
    }

    /**
     * @param \TdbShopOrder $order
     * @param $includePhysicalProducts
     * @param $includeDownloads
     *
     * @return \TPkgShopPaymentTransactionData
     */
    protected function helperGetTransactionDataForOrder(\TdbShopOrder $order, $includePhysicalProducts, $includeDownloads)
    {
        /** @var $transactionManager \TPkgShopPaymentTransactionManager|\PHPUnit_Framework_MockObject_MockObject */
        $transactionManager = $this->helperGetTransactionManagerForOrder($order);
        $transactionData = array();
        if ($includePhysicalProducts && $includeDownloads) {
            return $transactionManager->getTransactionDataFromOrder();
        }

        $restriction = array();
        if ($includePhysicalProducts) {
            $orderItems = $order->GetFieldShopOrderItemList();
            $orderItems->GoToStart();
            /** @var \TdbShopOrderItem $orderItem */
            while ($orderItem = $orderItems->Next()) {
                if (false === $orderItem->isDownload()) {
                    $restriction[$orderItem->id] = $orderItem->fieldOrderAmount;
                }
            }
            $orderItems->GoToStart();
        }
        if ($includeDownloads) {
            $orderItems = $order->GetFieldShopOrderItemList();
            $orderItems->GoToStart();
            /** @var \TdbShopOrderItem $orderItem */
            while ($orderItem = $orderItems->Next()) {
                if (true === $orderItem->isDownload()) {
                    $restriction[$orderItem->id] = $orderItem->fieldOrderAmount;
                }
            }
            $orderItems->GoToStart();
        }

        return $transactionManager->getTransactionDataFromOrder(\TPkgShopPaymentTransactionData::TYPE_PAYMENT, $restriction);
    }

    /**
     * @param $name
     * @param $iso4217
     * @param $symbol
     *
     * @return \TdbPkgShopCurrency
     */
    private function helperCreateCurrency($name, $iso4217, $symbol)
    {
        static $curCmsIdent = 666;
        $data = array(
            'id' => \TTools::GetUUID(),
            'cmsident' => $curCmsIdent++,
            'name' => $name,
            'symbol' => $symbol,
            'factor' => '1',
            'is_base_currency' => '1',
            'iso4217' => $iso4217,
        );

        return \TdbPkgShopCurrency::GetNewInstance($data);
    }

    /**
     * @param $amount
     * @param $price
     * @param $isDownload
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\TdbShopOrderItem
     */
    private function helperCreateOrderItem($amount, $price, $isDownload)
    {
        static $cmsIdent = 1000;

        $data = array(
            'id' => \TTools::GetUUID(),
            'cmsident' => $cmsIdent++,
            'shop_order_id' => 'testorder',
            'name' => 'Test Artikel '.\TTools::GetUUID(),
            'articlenumber' => \TTools::GetUUID(),
            'price' => $price,
            'vat_percent' => 19.00,
            'order_amount' => $amount,
            'order_price_total' => $amount * $price,
            'order_price_after_discounts' => $amount * $price,
            'order_price' => $price,
            'shop_article_id' => 'xxx',
            'download' => '',
            'price_discounted' => $price,
            'exclude_from_discounts' => '0',
        );
        /** @var $orderItem \TdbShopOrderItem|\PHPUnit_Framework_MockObject_MockObject */
        $orderItem = $this->getMockBuilder('TdbShopOrderItem')->setMethods(array('isDownload'))->getMock();
        $orderItem->expects($this->any())->method('isDownload')->will($this->returnValue($isDownload));
        $orderItem->LoadFromRow($data);

        return $orderItem;
    }

    private function helperCreateOrderData(\TIterator $orderItemList, $shipmentCost = 0, $paymentCost = 0)
    {
        static $cmsIdent = 5000;
        static $orderNumber = 10000;
        static $customerNumber = 50000;

        $totals = array(
            'count_unique_articles' => $orderItemList->Length(),
            'count_articles' => 0,
            'value_article' => 0,
        );
        /** @var \TdbShopOrderItem $item */
        while ($item = $orderItemList->Next()) {
            $totals['count_articles'] += $item->fieldOrderAmount;
            $totals['value_article'] += $item->fieldOrderPriceAfterDiscounts;
        }
        $grandTotal = $totals['value_article'] + $shipmentCost + $paymentCost;

        $orderData = array(
            'adr_billing_city' => 'Freiburg',
            'adr_billing_country_id' => '1',
            'adr_billing_postalcode' => '79098',
            'adr_shipping_city' => 'Freiburg',
            'adr_shipping_country_id' => '1',
            'adr_shipping_postalcode' => '79098',
            'adr_shipping_use_billing' => '1',
            'canceled' => '0',
            'cms_portal_id' => '1',
            'cmsident' => $cmsIdent++,
            'count_articles' => $totals['count_articles'],
            'count_unique_articles' => $totals['count_unique_articles'],
            'value_article' => $totals['value_article'],
            'customer_number' => $customerNumber++,
            'data_extranet_user_id' => \TTools::GetUUID(),
            'datecreated' => date('Y-m-d H:i:s'),
            'id' => \TTools::GetUUID(),
            'order_is_paid' => '0',
            'ordernumber' => $orderNumber++,
            'pkg_shop_currency_id' => \TTools::GetUUID(),
            'shop_id' => '1',
            'shop_payment_method_id' => \TTools::GetUUID(),
            'shop_payment_method_name' => 'Amazon Payment',
            'shop_payment_method_price' => $paymentCost,
            'shop_payment_method_vat_percent' => 19.0,
            'shop_shipping_group_id' => \TTools::GetUUID(),
            'shop_shipping_group_name' => 'Amazon Shipping',
            'shop_shipping_group_price' => $shipmentCost,
            'shop_shipping_group_vat_percent' => 19.00,
            'system_order_payment_method_executed' => '',
            'system_order_payment_method_executed_date' => '',
            'user_email' => 'info@esono.de',
            'user_ip' => '23.23.23.23',
            'value_discounts' => 0,
            'value_total' => $grandTotal,
            'value_vat_total' => 0,
            'value_vouchers' => 0,
            'value_vouchers_not_sponsored' => 0,
            'value_wrapping' => 0,
            'value_wrapping_card' => 0,
            'vat_id' => \TTools::GetUUID(),
        );

        return $orderData;
    }

    /**
     * we expect the order to be canceled and otherwise unchanged. There should be no transaction, and neither auth nor auth+capture calls.
     */
    public function test_amazon_api_error_on_set_order_reference_details()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * we expect the order to be canceled but otherwise unchanged. there should be no auth or auth+capture and no transactions.
     */
    public function test_amazon_api_error_on_confirm_order_reference()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * the order reference was confirmed but not used. so we expect
     * - the order ref object to be canceled
     * - the order to be canceled and unchanged
     * - no calls to auth or auth+capture
     * - no transactions.
     */
    public function test_amazon_api_error_on_get_order_reference_details()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_API_ERROR);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * the error should be thrown when trying to setOrderReferenceDetails, so we expect the method to cancel the order.
     */
    public function test_shippingAddressNotSet_constraint_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_NO_SHIPPING_ADDRESS);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * expect the same as test_shippingAddressNotSet_constraint_error.
     */
    public function test_paymentPlanNotSet_constraint_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_NO_PAYMENT_PLAN_SET);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * expect order to be canceled.
     */
    public function test_AmountNotSet_constraint_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_NO_AMOUNT_SET);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * expect order to be canceled.
     */
    public function test_Unknown_constraint_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_UNKNOWN_CONSTRAINT);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->never())->method('getOrderReferenceDetails');
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

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
     * expect order to be canceled.
     */
    public function test_InvalidCountry_error()
    {
        $expectedException = new \TPkgCmsException_LogAndMessage(AmazonPayment::ERROR_CODE_INVALID_ADDRESS);

        $order = $this->createOrderMock(true, true);

        // - the orders shipping address to be updated
        // - the orders buyer data to be updated (email etc. we do not have access to the billing address until after the authorize is confirmed)
        $order->expects($this->never())->method('SaveFieldsFast');

        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')->will($this->throwException($expectedException));
        $amazonOrderRef->expects($this->never())->method('authorize');
        $amazonOrderRef->expects($this->never())->method('authorizeAndCapture');

        $transactionManager = $this->helperGetTransactionManagerForOrder($order, array('addTransaction'));
        $transactionManager->expects($this->never())->method('addTransaction');

        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        /** @var $amazonPayment AmazonPayment|\PHPUnit_Framework_MockObject_MockObject */
        $amazonPayment = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonPayment')->setMethods(array('cancelOrder'))->setConstructorArgs(array($config))->getMock();
        $amazonPayment->expects($this->once())->method('cancelOrder')->with($this->equalTo($transactionManager), $this->equalTo($order));

        $exception = null;
        try {
            $amazonPayment->captureOrder($transactionManager, $order);
        } catch (\TPkgCmsException_LogAndMessage $e) {
            $exception = $e;
        }
        $this->assertNotNull($exception, 'expecting an exception');
        $this->assertEquals($expectedException, $exception);
    }

    protected function helperSynchronousAuthRejected($exceptionCode, $expectedErrorCode)
    {
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
        $transactionManager->expects($this->never())->method('confirmTransaction');

        // throw the exception
        $amazonOrderRef = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject')->setConstructorArgs(array($this->getConfig(), 'AMAZON-ORDER-REF-ID'))->getMock();
        $amazonOrderRef->expects($this->once())->method('setOrderReferenceDetails');
        $amazonOrderRef->expects($this->once())->method('confirmOrderReference');
        $amazonOrderRef->expects($this->once())->method('getOrderReferenceDetails')
            ->will(
                $this->returnValue(AmazonPaymentFixturesFactory::getOrderReferenceDetailsResponse('full.xml')->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()
                )
            );

        $amazonOrderRef->expects($this->once())->method('authorizeAndCapture')->with(
            $this->equalTo($order), // \TdbShopOrder $order
            $this->equalTo('LOCAL-AUTH-ID-WITH-CAPTURE'), // $localAuthorizationReferenceId
            $this->equalTo($order->fieldValueTotal), // $amount
            $this->equalTo(true), // $synchronous
            $this->equalTo(null) // $invoiceNumber
        )->will($this->throwException(new AmazonAuthorizationDeclinedException($exceptionCode)));

        $amazonOrderRef->expects($this->never())->method('authorize');
        $config = $this->getConfig();
        $config->expects($this->any())->method('amazonOrderReferenceObjectFactory')->with($this->equalTo('AMAZON-ORDER-REF-ID'))->will($this->returnValue($amazonOrderRef));

        $mockLocalId = $this->getMockBuilder('\ChameleonSystem\AmazonPaymentBundle\ReferenceIdMapping\AmazonReferenceId')->disableOriginalConstructor()->getMock();
        $mockLocalId->expects($this->any())->method('getLocalId')->will($this->returnValue('LOCAL-AUTH-ID-WITH-CAPTURE'));
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
        $this->assertEquals($expectedErrorCode, $exception->getMessageCode());
    }
}
