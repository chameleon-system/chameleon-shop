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

interface ProductStatisticsInterface
{
    /**
     * @return int
     */
    public function getSales();

    /**
     * @return int
     */
    public function getDetailViews();

    /**
     * @return int
     */
    public function getReviews();

    /**
     * @return int
     */
    public function getReviewAverage();
}
