<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\Service;

use ChameleonSystem\ShopBundle\ProductInventory\Interfaces\ProductInventoryServiceInterface;

class ProductInventoryServiceCacheProxy implements ProductInventoryServiceInterface
{
    /**
     * @var ProductInventoryServiceInterface
     */
    private $subject;
    private $cache = array();

    public function __construct(ProductInventoryServiceInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableStock($shopArticleId)
    {
        if (isset($this->cache['x'.$shopArticleId])) {
            return $this->cache['x'.$shopArticleId];
        }
        $this->cache['x'.$shopArticleId] = $this->subject->getAvailableStock($shopArticleId);

        return $this->cache['x'.$shopArticleId];
    }

    /**
     * {@inheritdoc}
     */
    public function addStock($shopArticleId, $stock)
    {
        $this->triggerCacheChange($shopArticleId);

        return $this->subject->addStock($shopArticleId, $stock);
    }

    /**
     * {@inheritdoc}
     */
    public function setStock($shopArticleId, $stock)
    {
        $this->triggerCacheChange($shopArticleId);

        return $this->subject->setStock($shopArticleId, $stock);
    }

    /**
     * {@inheritdoc}
     */
    public function updateVariantParentStock($parentArticleId)
    {
        $this->triggerCacheChange($parentArticleId);

        return $this->subject->updateVariantParentStock($parentArticleId);
    }

    /**
     * @param string $shopArticleId
     */
    private function triggerCacheChange($shopArticleId)
    {
        if (isset($this->cache['x'.$shopArticleId])) {
            unset($this->cache['x'.$shopArticleId]);
        }
    }
}
