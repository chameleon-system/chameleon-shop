<?php

declare(strict_types=1);

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\EcommerceStatsBundle\Library\Interfaces;

use ChameleonSystem\EcommerceStatsBundle\Library\DataModel\ShopOrderItemDataModel;

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
