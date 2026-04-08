<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

class ShopRevocationOrderRelevanceService
{
    private const BASE_REVOCATION_DAYS = 14;

    public function __construct(
        private readonly DeliveryDateProviderInterface $deliveryDateProvider,
        private readonly RevocationAdditionalDaysProviderInterface $additionalDaysProvider
    ) {
    }

    public function isOrderRelevantForRevocation(\TShopOrder $order): bool
    {
        if (true === (bool) $order->fieldCanceled) {
            return false;
        }

        $deliveryDate = $this->deliveryDateProvider->getDeliveryDate($order);
        if (null === $deliveryDate) {
            return false;
        }

        return $deliveryDate >= $this->getCutoffDate($order);
    }

    private function getCutoffDate(\TShopOrder $order): \DateTimeImmutable
    {
        return new \DateTimeImmutable(sprintf('-%d days', self::BASE_REVOCATION_DAYS + $this->additionalDaysProvider->getAdditionalDays($order)));
    }
}
