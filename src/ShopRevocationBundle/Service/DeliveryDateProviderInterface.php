<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

interface DeliveryDateProviderInterface
{
    /**
     * Returning null means that the active provider cannot supply a usable relevance date
     * for this order. In that case the revocation relevance service must treat the order
     * as not relevant.
     */
    public function getDeliveryDate(\TShopOrder $order): ?\DateTimeInterface;
}
