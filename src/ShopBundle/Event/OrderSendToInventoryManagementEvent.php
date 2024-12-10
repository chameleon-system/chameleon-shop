<?php

namespace ChameleonSystem\ShopBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OrderSendToInventoryManagementEvent extends Event
{
    public function __construct(
        private readonly \TdbShopOrder $shopOrder
    ) {
    }

    public function getShopOrder(): \TdbShopOrder
    {
        return $this->shopOrder;
    }
}
