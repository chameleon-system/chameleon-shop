<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class UpdateProductStockEvent extends Event
{
    public function __construct(
        private readonly string $productId,
        private readonly int $newStock,
        private readonly int $oldStock
    ) {
    }

    /**
     * @return string
     */
    public function getProductId(): string
    {
        return $this->productId;
    }

    /**
     * Returns the new total stock, calculated from all stock records across all warehouses.
     *
     * @return int
     */
    public function getNewStock(): int
    {
        return $this->newStock;
    }

    /**
     *  Returns the old total stock, calculated from all stock records across all warehouses.
     *
     * @return int
     */
    public function getOldStock(): int
    {
        return $this->oldStock;
    }
}
