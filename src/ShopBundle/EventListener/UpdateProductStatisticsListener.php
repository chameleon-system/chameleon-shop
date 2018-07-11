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
use TdbShopArticle;

class UpdateProductStatisticsListener
{
    /**
     * @var ProductStatisticsServiceInterface
     */
    private $productStatisticsService;

    /**
     * @param ProductStatisticsServiceInterface $productStatisticsService
     */
    public function __construct(ProductStatisticsServiceInterface $productStatisticsService)
    {
        $this->productStatisticsService = $productStatisticsService;
    }

    /**
     * @param UpdateProductStockEvent $event
     */
    public function onUpdateProductStock(UpdateProductStockEvent $event)
    {
        $productId = $event->getProductId();
        $product = TdbShopArticle::GetNewInstance($productId);
        $isVariant = $product->IsVariant();
        if (!$isVariant && !$product->HasVariants()) {
            return;
        }
        $this->updateProductStatistics($isVariant ? $product->fieldVariantParentId : $product->id);
    }

    /**
     * @param string $parentId
     */
    private function updateProductStatistics($parentId)
    {
        $this->productStatisticsService->updateAllBasedOnVariants($parentId);
    }
}
