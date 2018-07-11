<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\AmazonPaymentBundle\tests\AmazonPaymentGroupConfig;

require_once __DIR__.'/../abstracts/AbstractAmazonPayment.php';

use ChameleonSystem\AmazonPaymentBundle\AmazonOrderReferenceObject;
use ChameleonSystem\AmazonPaymentBundle\AmazonPaymentGroupConfig;
use ChameleonSystem\AmazonPaymentBundle\tests\abstracts\AbstractAmazonPayment;
use ChameleonSystem\AmazonPaymentBundle\tests\fixtures\AmazonPaymentFixturesFactory;
use Symfony\Component\Config\FileLocator;

class AmazonPaymentGroupConfigTest extends AbstractAmazonPayment
{
    /**
     * @var \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order = null;

    protected function setUp()
    {
        parent::setUp();
        /** @var $mockOrder \TdbShopOrder|\PHPUnit_Framework_MockObject_MockObject */
        $this->order = $this->getMockBuilder('TdbShopOrder')->disableOriginalConstructor()->getMock();

        /** @var $shop \TdbShop|\PHPUnit_Framework_MockObject_MockObject */
        $shop = $this->getMockBuilder('TdbShop')->disableOriginalConstructor()->getMock();
        $shop->expects($this->any())->method('GetSQLWithTablePrefix')->will($this->returnValue(array(
                    'shop__name' => 'MEIN-SHOP',
                )
            )
        );
        $this->order->expects($this->any())->method('GetFieldShop')->will($this->returnValue($shop));
        $this->order->sqlData = array('ordernumber' => 12345);
        $this->order->expects($this->any())->method('GetSQLWithTablePrefix')->will($this->returnValue(array(
                    'shop_order__ordernumber' => '12345',
                )
            )
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->order = null;
    }

    /**
     * @test
     */
    public function test_the_seller_authorization_note_without_capture()
    {
        $expected = 'Vielen Dank für Ihre Bestellung 12345 bei MEIN-SHOP';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('sellerAuthorizationNote.xml');

        $this->assertEquals($expected, $config->getSellerAuthorizationNote($this->order, 1.25, false));
    }

    /**
     * @test
     */
    public function test_the_seller_authorization_note_with_capture()
    {
        $expected = 'Vielen Dank für Ihre Bestellung 12345 bei MEIN-SHOP (mit capture now)';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('sellerAuthorizationNote.xml');

        $this->assertEquals($expected, $config->getSellerAuthorizationNote($this->order, 1, true));
    }

    public function test_get_seller_order_note()
    {
        $expected = 'Vielen Dank für Ihre Bestellung 12345 bei MEIN-SHOP';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('sellerNote.xml');

        $this->assertEquals($expected, $config->getSellerOrderNote($this->order));
    }

    public function test_get_soft_descriptor_with_no_invoice_number()
    {
        $expected = 'BNR 12345';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('softDescriptor.xml');
        $this->assertEquals($expected, $config->getSoftDescriptor($this->order));
    }

    public function test_get_soft_descriptor_with_invoice_number()
    {
        $expected = 'RNR 123456789';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('softDescriptor.xml');

        $this->assertEquals($expected, $config->getSoftDescriptor($this->order, '123456789'));
    }

    public function test_get_descriptor_with_long_invoice_number()
    {
        $expected = 'RNR 123456789012';

        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $loader = new \TPkgShopPaymentGroupConfigXMLLoader($config, new FileLocator(AmazonPaymentFixturesFactory::getFixturePath('AmazonPaymentGroupConfig')));
        $loader->load('softDescriptor.xml');

        $this->assertEquals($expected, $config->getSoftDescriptor($this->order, '123456789012456546456'));
    }

    public function test_create_amazon_order_reference_object()
    {
        $config = new AmazonPaymentGroupConfig(\IPkgShopOrderPaymentConfig::ENVIRONMENT_PRODUCTION);
        $expected = new AmazonOrderReferenceObject($config, 'AMAZON-ORDER-REFERENCE-ID');
        $amazonOrderReferenceObject = $config->amazonOrderReferenceObjectFactory('AMAZON-ORDER-REFERENCE-ID');

        $this->assertEquals($expected, $amazonOrderReferenceObject);
    }
}
