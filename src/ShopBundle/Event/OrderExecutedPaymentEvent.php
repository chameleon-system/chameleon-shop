<?php

namespace ChameleonSystem\ShopBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class OrderExecutedPaymentEvent extends Event
{
    public function __construct(
        private readonly \TdbShopOrder $shopOrder,
        private readonly \TdbShopPaymentHandler $shopPaymentHandler,
        private readonly string $flashMesssageConsumer
    ) {
    }

    public function getShopOrder(): \TdbShopOrder
    {
        return $this->shopOrder;
    }

    public function getShopPaymentHandler(): \TdbShopPaymentHandler
    {
        return $this->shopPaymentHandler;
    }

    public function getFlashMesssageConsumer(): string
    {
        return $this->flashMesssageConsumer;
    }
}
