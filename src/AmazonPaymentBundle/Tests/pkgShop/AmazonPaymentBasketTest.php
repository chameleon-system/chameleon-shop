<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\pkgShop;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;

class AmazonPaymentBasketTest extends AbstractAmazonPayment
{
    /**
     * @test
     */
    public function it_set_the_order_reference_id()
    {
        // we expect the basket to have an order reference id, and we expect an instance of the extranet user passed to be activated as an amazon user
        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->once())->method('setAmazonPaymentEnabled')->with($this->equalTo(true));

        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails'))->getMock();
        $basket->expects($this->once())->method('updateAmazonOrderReferenceDetails');
        $basket->setAmazonOrderReferenceId('35985468546954', $user);

        $this->assertEquals('35985468546954', $basket->getAmazonOrderReferenceId());
    }

    /**
     * @test
     */
    public function it_resets_the_order_reference_id()
    {
        // we expect the basket to have an order reference id, and we expect an instance of the extranet user passed to be activated as an amazon user
        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->once())->method('setAmazonPaymentEnabled')->with($this->equalTo(false));

        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails'))->getMock();
        $basket->expects($this->never())->method('updateAmazonOrderReferenceDetails');

        $basket->setAmazonOrderReferenceId(null, $user);

        $this->assertEquals(null, $basket->getAmazonOrderReferenceId());
    }

    /**
     * @test
     */
    public function it_is_serialized_and_unserialzed()
    {
        // we expect the basket to have an order reference id, and we expect an instance of the extranet user passed to be activated as an amazon user
        $user = $this->getMockBuilder('TdbDataExtranetUser')->disableOriginalConstructor()->getMock();
        $user->expects($this->once())->method('setAmazonPaymentEnabled')->with($this->equalTo(true));
        $prop = new \ReflectionProperty('\ChameleonSystem\AmazonPaymentBundle\pkgShop\AmazonPaymentBasket', 'amazonOrderReferenceValue');
        $prop->setAccessible(true);

        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails'))->getMock();
        $basket->expects($this->once())->method('updateAmazonOrderReferenceDetails');

        $basket->setAmazonOrderReferenceId('35985468546954', $user);
        $prop->setValue($basket, '12345');

        $seralize = serialize($basket);
        $unseralizedBasket = unserialize($seralize);

        $this->assertEquals('35985468546954', $unseralizedBasket->getAmazonOrderReferenceId());
        $this->assertEquals('12345', $prop->getValue($unseralizedBasket));

        // check that the orderRefValue sent to amazon was also recovered
    }

    public function test_basket_value_changed_on_basket_without_amazon_order_reference_id()
    {
        /** @var $newItem \TShopBasketArticle|\PHPUnit_Framework_MockObject_MockObject */
        $newItem = $this->getMockBuilder('\TShopBasketArticle')->disableOriginalConstructor()->getMock();
        $newItem->dProductPrice = 10;
        $newItem->dAmount = 1;
        $newItem->expects($this->any())->method('IsBuyable')->will($this->returnValue(true));

        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->disableOriginalConstructor()->setMethods(array('updateAmazonOrderReferenceDetails', 'SetBasketRecalculationFlag', 'ResetAllShippingMarkers', 'RecalculateDiscounts', 'RecalculateNoneSponsoredVouchers', 'RecalculateShipping', 'CalculatePaymentMethodCosts', 'RecalculateVAT', 'RecalculateVouchers'))->getMock();
        $basket->expects($this->never())->method('updateAmazonOrderReferenceDetails');
        $basket->AddItem($newItem);
        $basket->RecalculateBasket();
    }

    public function test_basket_value_changed_first_time_on_basket_with_amazon_order_reference_id()
    {
        /** @var $newItem \TShopBasketArticle|\PHPUnit_Framework_MockObject_MockObject */
        $newItem = $this->getMockBuilder('\TShopBasketArticle')->disableOriginalConstructor()->getMock();
        $newItem->dProductPrice = 10;
        $newItem->dAmount = 1;
        $newItem->expects($this->any())->method('IsBuyable')->will($this->returnValue(true));

        $userMock = $this->getMockBuilder('TdbDataExtranetUser')->getMock();
        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails', 'SetBasketRecalculationFlag', 'ResetAllShippingMarkers', 'RecalculateDiscounts', 'RecalculateNoneSponsoredVouchers', 'RecalculateShipping', 'CalculatePaymentMethodCosts', 'RecalculateVAT', 'RecalculateVouchers'))->getMock();
        $basket->setAmazonOrderReferenceId('ORDER-REF-ID', $userMock);

        $basket->expects($this->once())->method('updateAmazonOrderReferenceDetails');
        $basket->AddItem($newItem);
        $basket->RecalculateBasket();
    }

    public function test_basket_value_changed_second_time_on_basket_with_amazon_order_reference_id()
    {
        /** @var $newItem \TShopBasketArticle|\PHPUnit_Framework_MockObject_MockObject */
        $newItem = $this->getMockBuilder('\TShopBasketArticle')->disableOriginalConstructor()->getMock();
        $newItem->sBasketItemKey = '1';
        $newItem->dProductPrice = 10;
        $newItem->dPriceTotal = 10;
        $newItem->dAmount = 1;
        $newItem->expects($this->any())->method('IsBuyable')->will($this->returnValue(true));
        /** @var $newItem2 \TShopBasketArticle|\PHPUnit_Framework_MockObject_MockObject */
        $newItem2 = $this->getMockBuilder('\TShopBasketArticle')->disableOriginalConstructor()->getMock();
        $newItem2->sBasketItemKey = '2';
        $newItem2->dProductPrice = 15;
        $newItem2->dPriceTotal = 15;
        $newItem2->dAmount = 1;
        $newItem2->expects($this->any())->method('IsBuyable')->will($this->returnValue(true));

        $userMock = $this->getMockBuilder('TdbDataExtranetUser')->getMock();
        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails', 'SetBasketRecalculationFlag', 'ResetAllShippingMarkers', 'RecalculateDiscounts', 'RecalculateNoneSponsoredVouchers', 'RecalculateShipping', 'CalculatePaymentMethodCosts', 'RecalculateVAT', 'RecalculateVouchers'))->getMock();
        $basket->setAmazonOrderReferenceId('ORDER-REF-ID', $userMock);

        $basket->expects($this->exactly(2))->method('updateAmazonOrderReferenceDetails');
        $basket->AddItem($newItem);
        $basket->RecalculateBasket();
        $basket->dCostTotal = $basket->dCostTotal + 10;
        $basket->AddItem($newItem2);
        $basket->RecalculateBasket();
    }

    public function test_recalculate_without_value_change_on_basket_with_amazon_order_reference_id()
    {
        /** @var $newItem \TShopBasketArticle|\PHPUnit_Framework_MockObject_MockObject */
        $newItem = $this->getMockBuilder('\TShopBasketArticle')->disableOriginalConstructor()->getMock();
        $newItem->sBasketItemKey = '1';
        $newItem->dProductPrice = 10;
        $newItem->dAmount = 1;
        $newItem->expects($this->any())->method('IsBuyable')->will($this->returnValue(true));

        $userMock = $this->getMockBuilder('TdbDataExtranetUser')->getMock();
        /** @var $basket \TShopBasket|\PHPUnit_Framework_MockObject_MockObject */
        $basket = $this->getMockBuilder('\TShopBasket')->setMethods(array('updateAmazonOrderReferenceDetails', 'SetBasketRecalculationFlag', 'ResetAllShippingMarkers', 'RecalculateDiscounts', 'RecalculateNoneSponsoredVouchers', 'RecalculateShipping', 'CalculatePaymentMethodCosts', 'RecalculateVAT', 'RecalculateVouchers'))->getMock();
        $basket->setAmazonOrderReferenceId('ORDER-REF-ID', $userMock);

        $basket->expects($this->once())->method('updateAmazonOrderReferenceDetails');
        $basket->AddItem($newItem);
        $basket->RecalculateBasket();
        $basket->RecalculateBasket();
    }
}
