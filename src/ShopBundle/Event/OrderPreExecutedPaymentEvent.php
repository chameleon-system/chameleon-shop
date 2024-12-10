<?php

namespace ChameleonSystem\ShopBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OrderPreExecutedPaymentEvent extends Event
{
    public function __construct(
        private readonly \TdbShopOrder $shopOrder,
        private readonly string $flashMesssageConsumer
    ) {
    }

    public function getShopOrder(): \TdbShopOrder
    {
        return $this->shopOrder;
    }

    public function getFlashMesssageConsumer(): string
    {
        return $this->flashMesssageConsumer;
    }
}
