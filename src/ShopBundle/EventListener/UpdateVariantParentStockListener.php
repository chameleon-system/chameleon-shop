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
use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;
use TdbShopArticle;

class UpdateVariantParentStockListener
{
    /**
     * @var ProductInventoryServiceInterface
     */
    private $productInventoryService;

    /**
     * @param ProductInventoryServiceInterface $productInventoryService
     */
    public function __construct(ProductInventoryServiceInterface $productInventoryService)
    {
        $this->productInventoryService = $productInventoryService;
    }

    /**
     * @param UpdateProductStockEvent $event
     *
     * @return void
     */
    public function onUpdateProductStock(UpdateProductStockEvent $event)
    {
        $productId = $event->getProductId();
        $product = TdbShopArticle::GetNewInstance($productId);
        $isVariant = $product->IsVariant();
        if (!$isVariant && !$product->HasVariants()) {
            return;
        }
        $this->updateVariantParentStock($isVariant ? $product->fieldVariantParentId : $product->id);
        if ($isVariant) {
            $this->setVariantParentActive($product);
        }
    }

    /**
     * @param string $parentId
     *
     * @return void
     */
    private function updateVariantParentStock($parentId)
    {
        $this->productInventoryService->updateVariantParentStock($parentId);
    }

    /**
     * @param TdbShopArticle $product
     *
     * @return void
     */
    private function setVariantParentActive(TdbShopArticle $product)
    {
        $parentProduct = $product->GetFieldVariantParent();
        $parentStock = $parentProduct->getAvailableStock();
        $stockMessage = $parentProduct->GetFieldShopStockMessage();
        if (null === $stockMessage) {
            return;
        }
        if ($parentStock > 0 && $stockMessage->fieldAutoActivateOnStock) {
            $activeValue = true;
        } elseif ($parentStock < 1 && $stockMessage->fieldAutoDeactivateOnZeroStock) {
            $activeValue = false;
        } else {
            $activeValue = $parentProduct->fieldActive;
        }

        if ($parentProduct->fieldActive !== $activeValue || $parentProduct->fieldVariantParentIsActive !== $activeValue) {
            $product->setVariantParentActive($parentProduct->id, $activeValue);
        }
    }
}
