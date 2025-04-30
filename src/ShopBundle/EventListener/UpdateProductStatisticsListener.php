<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\EventListener;

use ChameleonSystem\ShopBundle\Event\UpdateProductStockEvent;
use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface;

class UpdateProductStatisticsListener
{
    /**
     * @var ProductStatisticsServiceInterface
     */
    private $productStatisticsService;

    public function __construct(ProductStatisticsServiceInterface $productStatisticsService)
    {
        $this->productStatisticsService = $productStatisticsService;
    }

    /**
     * @return void
     */
    public function onUpdateProductStock(UpdateProductStockEvent $event)
    {
        $productId = $event->getProductId();
        $product = \TdbShopArticle::GetNewInstance($productId);
        $isVariant = $product->IsVariant();
        if (!$isVariant && !$product->HasVariants()) {
            return;
        }
        $this->updateProductStatistics($isVariant ? $product->fieldVariantParentId : $product->id);
    }

    /**
     * @param string $parentId
     *
     * @return void
     */
    private function updateProductStatistics($parentId)
    {
        $this->productStatisticsService->updateAllBasedOnVariants($parentId);
    }
}
