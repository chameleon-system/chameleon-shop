<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Event\ArticleListFilterExecutedEvent;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\FilterFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\ShopEvents;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResultFactoryCache implements ResultFactoryInterface
{
    /**
     * @var Interfaces\ResultFactoryInterface
     */
    private $resultFactory;
    /**
     * @var \esono\pkgCmsCache\CacheInterface
     */
    private $cache;
    /**
     * @var FilterFactoryInterface
     */
    private $filterFactory;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var DbAdapterInterface
     */
    private $dbAdapter;

    /**
     * @param ResultFactoryInterface   $resultFactory
     * @param CacheInterface           $cache
     * @param FilterFactoryInterface   $filterFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param DbAdapterInterface       $dbAdapter
     */
    public function __construct(
        ResultFactoryInterface $resultFactory,
        CacheInterface $cache,
        FilterFactoryInterface $filterFactory,
        EventDispatcherInterface $eventDispatcher,
        DbAdapterInterface $dbAdapter
    ) {
        $this->resultFactory = $resultFactory;
        $this->cache = $cache;
        $this->filterFactory = $filterFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->dbAdapter = $dbAdapter;
    }

    /**
     * Note: If an invalid page is requested via state, then the first page will be returned instead.
     *
     * @param ConfigurationInterface $moduleConfiguration
     * @param StateInterface         $state
     *
     * @return ResultDataInterface
     */
    public function createResult(ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        if (false === $this->_AllowCache($moduleConfiguration)) {
            return $this->resultFactory->createResult($moduleConfiguration, $state);
        }
        $key = $this->getKey($moduleConfiguration, $state);
        $resultData = $this->cache->get($key);
        if (null !== $resultData) {
            $this->dispatchCachedFilterExecutedEvent($resultData, $moduleConfiguration, $state);

            return $resultData;
        }

        $resultData = $this->resultFactory->createResult($moduleConfiguration, $state);

        $this->cache->set($key, $resultData, $this->getTrigger($moduleConfiguration));

        return $resultData;
    }

    private function getKey(ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        $keyData = array(
            'class' => 'ChameleonSystem\ShopBundle\objects\ArticleList\ResultFactoryCache',
        );
        $filterParameter = $this->_GetCacheParameters($moduleConfiguration, $state);
        $keyData = array_merge_recursive($keyData, $filterParameter);

        return $this->cache->getKey($keyData);
    }

    private function getTrigger(ConfigurationInterface $moduleConfiguration)
    {
        return $this->_GetCacheTableInfos($moduleConfiguration);
    }

    public function _AllowCache(ConfigurationInterface $moduleConfiguration)
    {
        return $this->resultFactory->_AllowCache($moduleConfiguration);
    }

    public function _GetCacheParameters(ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        return $this->resultFactory->_GetCacheParameters($moduleConfiguration, $state);
    }

    public function _GetCacheTableInfos(ConfigurationInterface $moduleConfiguration)
    {
        return $this->resultFactory->_GetCacheTableInfos($moduleConfiguration);
    }

    public function getFilterQuery(ConfigurationInterface $moduleConfiguration)
    {
        return $this->resultFactory->getFilterQuery($moduleConfiguration);
    }

    public function moduleInitHook(ConfigurationInterface $moduleConfiguration)
    {
        return $this->resultFactory->moduleInitHook($moduleConfiguration);
    }

    /**
     * @param ResultDataInterface    $resultData
     * @param ConfigurationInterface $moduleConfiguration
     * @param StateInterface         $state
     */
    private function dispatchCachedFilterExecutedEvent(ResultDataInterface $resultData, ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        $filterConfiguration = $this->dbAdapter->getFilterDefinitionFromId($moduleConfiguration->getDefaultFilterId());
        $event = new ArticleListFilterExecutedEvent(
            $this->filterFactory->createFilter($filterConfiguration),
            $resultData,
            $moduleConfiguration,
            $state,
            true
        );
        $this->eventDispatcher->dispatch($event, ShopEvents::ARTICLE_LIST_FILTER_EXECUTED);
    }
}
