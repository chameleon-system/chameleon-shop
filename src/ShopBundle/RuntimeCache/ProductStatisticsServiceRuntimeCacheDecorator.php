<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\RuntimeCache;

use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsInterface;
use ChameleonSystem\ShopBundle\ProductStatistics\Interfaces\ProductStatisticsServiceInterface;

class ProductStatisticsServiceRuntimeCacheDecorator implements ProductStatisticsServiceInterface
{
    /**
     * @var ProductStatisticsServiceInterface
     */
    private $subject;

    /**
     * @var ProductStatisticsInterface[]
     */
    private $cacheStats = [];

    public function __construct(ProductStatisticsServiceInterface $subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getStats($articleId)
    {
        if (isset($this->cacheStats[$articleId])) {
            return $this->cacheStats[$articleId];
        }
        $this->cacheStats[$articleId] = $this->subject->getStats($articleId);

        return $this->cacheStats[$articleId];
    }

    /**
     * {@inheritdoc}
     */
    public function add($articleId, $type, $amount)
    {
        $this->subject->add($articleId, $type, $amount);
        $this->resetStatsCache($articleId);
    }

    /**
     * {@inheritdoc}
     */
    public function set($articleId, $type, $amount)
    {
        $this->subject->set($articleId, $type, $amount);
        $this->resetStatsCache($articleId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAllBasedOnVariants($parentArticleId)
    {
        $this->subject->updateAllBasedOnVariants($parentArticleId);
        $this->cacheStats = [];
    }

    /**
     * @param string $articleId
     *
     * @return void
     */
    private function resetStatsCache($articleId)
    {
        if (isset($this->cacheStats[$articleId])) {
            unset($this->cacheStats[$articleId]);
        }
    }
}
