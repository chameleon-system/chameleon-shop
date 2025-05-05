<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Event;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ArticleListFilterExecutedEvent extends Event
{
    /**
     * @var ResultDataInterface
     */
    private $resultData;
    /**
     * @var ConfigurationInterface
     */
    private $moduleConfiguration;
    /**
     * @var StateInterface
     */
    private $state;

    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var bool
     */
    private $resultFromCache;

    /**
     * ArticleListFilterExecutedEvent constructor.
     *
     * @param bool $resultFromCache
     */
    public function __construct(
        FilterInterface $filter,
        ResultDataInterface $resultData,
        ConfigurationInterface $moduleConfiguration,
        StateInterface $state,
        $resultFromCache = false
    ) {
        $this->resultData = $resultData;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->state = $state;
        $this->filter = $filter;
        $this->resultFromCache = $resultFromCache;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getModuleConfiguration()
    {
        return $this->moduleConfiguration;
    }

    /**
     * @return ResultDataInterface
     */
    public function getResultData()
    {
        return $this->resultData;
    }

    /**
     * @return StateInterface
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return bool
     */
    public function isResultFromCache()
    {
        return $this->resultFromCache;
    }
}
