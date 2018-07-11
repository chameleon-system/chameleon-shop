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

use Symfony\Component\EventDispatcher\Event;

class UpdateProductStockEvent extends Event
{
    /**
     * @var string
     */
    private $productId;
    /**
     * @var int
     */
    private $newStock;
    /**
     * @var int
     */
    private $oldStock;

    /**
     * @param string $productId
     * @param int    $newStock
     * @param int    $oldStock
     */
    public function __construct($productId, $newStock, $oldStock)
    {
        $this->productId = $productId;
        $this->newStock = $newStock;
        $this->oldStock = $oldStock;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getNewStock()
    {
        return $this->newStock;
    }

    /**
     * @return int
     */
    public function getOldStock()
    {
        return $this->oldStock;
    }
}
