<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

class DefaultDeliveryDateProvider implements DeliveryDateProviderInterface
{
    public function getDeliveryDate(\TShopOrder $order): ?\DateTimeInterface
    {
        $dateCreated = (string) $order->fieldDatecreated;
        if ('' === $dateCreated) {
            return null;
        }

        try {
            return new \DateTimeImmutable($dateCreated);
        } catch (\Exception) {
            return null;
        }
    }
}
