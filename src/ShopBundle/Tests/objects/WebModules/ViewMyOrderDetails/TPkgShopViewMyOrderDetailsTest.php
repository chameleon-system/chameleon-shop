<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class TPkgShopViewMyOrderDetailsTest.
 *
 * @covers \TPkgShopViewMyOrderDetails
 */
class TPkgShopViewMyOrderDetailsTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var IPkgShopViewMyOrderDetailsDbAdapter|ObjectProphecy
     */
    private $mockDbAdapter;
    /**
     * @var IPkgShopViewMyOrderDetailsSessionAdapter|ObjectProphecy
     */
    private $mockSessionAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDbAdapter = $this->prophesize('IPkgShopViewMyOrderDetailsDbAdapter');
        $this->mockSessionAdapter = $this->prophesize('IPkgShopViewMyOrderDetailsSessionAdapter');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->mockDbAdapter = null;
        $this->mockSessionAdapter = null;
    }

    /**
     * @test
     */
    public function itAddsAnOrderToAGuestsOrderList()
    {
        $this->mockSessionAdapter->addOrderId('ORDERID')->shouldBeCalled(null);
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $viewMyOrderDetails->addOrderIdToMyList('ORDERID');
    }

    /**
     * @test
     */
    public function itAddsAnOrderToAUsersOrderList()
    {
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $viewMyOrderDetails->addOrderIdToMyList('ORDERID', 'USERID');
    }

    /**
     * @test
     */
    public function itConfirmsOrderInUsersOrderList()
    {
        $this->mockDbAdapter->hasOrder('USERID', 'ORDERID')->willReturn(true);
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $this->assertTrue($viewMyOrderDetails->orderIdBelongsToUser('ORDERID', 'USERID'));
    }

    /**
     * @test
     */
    public function itConfirmsOrderInGuestsOrderList()
    {
        $this->mockSessionAdapter->hasOrder('ORDERID')->willReturn(true);
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $this->assertTrue($viewMyOrderDetails->orderIdBelongsToUser('ORDERID'));
    }

    /**
     * @test
     */
    public function itDeniesOrderInUsersOrderList()
    {
        $this->mockDbAdapter->hasOrder('USERID', 'ORDERID')->willReturn(false);
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $this->assertFalse($viewMyOrderDetails->orderIdBelongsToUser('ORDERID', 'USERID'));
    }

    /**
     * @test
     */
    public function itDeniesOrderInGuestsOrderList()
    {
        $this->mockSessionAdapter->hasOrder('ORDERID')->willReturn(false);
        $viewMyOrderDetails = new TPkgShopViewMyOrderDetails($this->mockDbAdapter->reveal(), $this->mockSessionAdapter->reveal());

        $this->assertFalse($viewMyOrderDetails->orderIdBelongsToUser('ORDERID'));
    }
}
