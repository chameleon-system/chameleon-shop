<?php

namespace ChameleonSystem\ShopRevocationBundle\Service;

use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;

class DefaultRevocationAdditionalDaysProvider implements RevocationAdditionalDaysProviderInterface
{
    private const DEFAULT_ADDITIONAL_DAYS = 3;

    public function __construct(
        private readonly ShopServiceInterface $shopService
    ) {
    }

    public function getAdditionalDays(\TShopOrder $order): int
    {
        $activeShop = $this->shopService->getActiveShop();
        $additionalDays = (int) ($activeShop->fieldRevocationAdditionalDays ?? self::DEFAULT_ADDITIONAL_DAYS);

        if ($additionalDays < 0) {
            return self::DEFAULT_ADDITIONAL_DAYS;
        }

        return $additionalDays;
    }
}
