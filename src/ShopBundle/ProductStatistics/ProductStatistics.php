<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\ProductStatistics;

use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsInterface;

class ProductStatistics implements ProductStatisticsInterface
{
    private $sales = 0;
    private $detailViews = 0;
    private $reviews = 0;
    private $reviewAverage = 0;

    /**
     * @param int $detailViews
     */
    public function setDetailViews($detailViews)
    {
        $this->detailViews = $detailViews;
    }

    /**
     * @return int
     */
    public function getDetailViews()
    {
        return (null !== $this->detailViews) ? $this->detailViews : 0;
    }

    /**
     * @param int $reviewAverage
     */
    public function setReviewAverage($reviewAverage)
    {
        $this->reviewAverage = $reviewAverage;
    }

    /**
     * @return int
     */
    public function getReviewAverage()
    {
        return (null !== $this->reviewAverage) ? $this->reviewAverage : 0;
    }

    /**
     * @param int $reviews
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
    }

    /**
     * @return int
     */
    public function getReviews()
    {
        return (null !== $this->reviews) ? $this->reviews : 0;
    }

    /**
     * @param int $sales
     */
    public function setSales($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return int
     */
    public function getSales()
    {
        return (null !== $this->sales) ? $this->sales : 0;
    }
}
