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
    /** @var int */
    private $sales = 0;
    /** @var int */
    private $detailViews = 0;
    /** @var int */
    private $reviews = 0;
    /** @var int */
    private $reviewAverage = 0;

    /**
     * @param int $detailViews
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
