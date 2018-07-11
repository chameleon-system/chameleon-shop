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
    const TYPE_SALES = 1;
    const TYPE_DETAIL_VIEWS = 2;
    const TYPE_REVIEW_AVERAGE = 3;
    const TYPE_REVIEW_COUNT = 4;

    /**
     * @param $articleId
     *
     * @return ProductStatisticsInterface
     */
    public function getStats($articleId);

    public function add($articleId, $type, $amount);

    public function set($articleId, $type, $amount);

    public function updateAllBasedOnVariants($parentArticleId);
}
