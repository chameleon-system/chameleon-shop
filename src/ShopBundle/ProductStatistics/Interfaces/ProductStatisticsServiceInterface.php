<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\ProductStatistics\Interfaces;

interface ProductStatisticsServiceInterface
{
    public const TYPE_SALES = 1;
    public const TYPE_DETAIL_VIEWS = 2;
    public const TYPE_REVIEW_AVERAGE = 3;
    public const TYPE_REVIEW_COUNT = 4;

    /**
     * @param string $articleId
     *
     * @return ProductStatisticsInterface
     */
    public function getStats($articleId);

    /**
     * @param string $articleId
     * @param int $type
     * @param float $amount
     *
     * @psalm-param self::TYPE_* $type
     *
     * @return void
     */
    public function add($articleId, $type, $amount);

    /**
     * @param string $articleId
     * @param int $type
     * @param float $amount
     *
     * @psalm-param self::TYPE_* $type
     *
     * @return void
     */
    public function set($articleId, $type, $amount);

    /**
     * @param string $parentArticleId
     *
     * @return void
     */
    public function updateAllBasedOnVariants($parentArticleId);
}
