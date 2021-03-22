<?php declare(strict_types=1);

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
