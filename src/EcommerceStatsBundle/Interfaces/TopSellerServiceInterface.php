<?php

namespace ChameleonSystem\EcommerceStatsBundle\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\DataModel\ShopOrderItemDataModel;

interface TopSellerServiceInterface
{
    /**
     * @return ShopOrderItemDataModel[]
     */
    public function getTopsellers(
        ?\DateTime $startDate,
        ?\DateTime $endDate,
        string $portalId,
        int $limit = 50
    ): array;
}
