<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\ProductInventory\Interfaces;

interface ProductInventoryServiceInterface
{
    /**
     * Loads stock record from shop_article_stock table and returns stock value.
     * If the record is missing the query will fail and the method returns false.
     *
     * @param string $shopArticleId
     *
     * @return int|false may return false if the query does not find a stock record
     */
    public function getAvailableStock($shopArticleId);

    /**
     * @param string $shopArticleId
     * @param int    $stock
     */
    public function addStock($shopArticleId, $stock);

    /**
     * @param string $shopArticleId
     * @param int    $stock
     */
    public function setStock($shopArticleId, $stock);

    /**
     * @param string $parentArticleId
     */
    public function updateVariantParentStock($parentArticleId);
}
