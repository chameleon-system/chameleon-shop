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
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Event\ArticleListFilterExecutedEvent;
use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageNumberException;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\FilterFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces\ResultModifierInterface;
use ChameleonSystem\ShopBundle\ShopEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ResultFactory implements ResultFactoryInterface
{
    /**
     * @var DatabaseAccessLayer\Interfaces\DbAdapterInterface
     */
    private $dbAdapter;
    /**
     * @var Interfaces\FilterFactoryInterface
     */
    private $filterFactory;
    /**
     * @var ResultModifier\Interfaces\ResultModifierInterface
     */
    private $resultModifier;

    /**
     * @var DatabaseAccessLayer\Interfaces\FilterInterface[]
     */
    private $filterCache = array();
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(DbAdapterInterface $dbAdapter, FilterFactoryInterface $filterFactory, ResultModifierInterface $resultModifier, EventDispatcherInterface $eventDispatcher)
    {
        $this->dbAdapter = $dbAdapter;
        $this->filterFactory = $filterFactory;
        $this->resultModifier = $resultModifier;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createResult(ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        $results = $this->createUnfilteredResults($moduleConfiguration);

        $results = $this->applyMaxAllowedResults($results, $moduleConfiguration);

        $results = $this->applyStateToResults($results, $moduleConfiguration->getAsArray(), $state);

        $resultData = $this->createResultDataFromResults($results);

        $this->triggerFilterExecutedEvent($resultData, $moduleConfiguration, $state);

        return $resultData;
    }

    /**
     * @param ConfigurationInterface $moduleConfiguration
     *
     * @return ResultInterface
     */
    private function createUnfilteredResults(ConfigurationInterface $moduleConfiguration)
    {
        $filter = $this->getFilter($moduleConfiguration);
        $filterDepth = 0;
        do {
            $result = $this->dbAdapter->getListResults($moduleConfiguration, $filter);
            $result = $this->resultModifier->apply($result, $moduleConfiguration->getAsArray(), $filterDepth);
            if (0 === $result->count()) {
                $filter = $this->filterFactory->createFallbackFilter($filter);
                ++$filterDepth;
            }
        } while (0 === $result->count() && null !== $filter);

        return $result;
    }

    /**
     * @param ResultInterface $results
     * @param array $moduleConfig
     * @param StateInterface $state
     * @return ResultInterface
     */
    private function applyStateToResults(ResultInterface $results, array $moduleConfig, StateInterface $state)
    {
        try {
            $result = $this->resultModifier->applyState($results, $moduleConfig, $state);

            return $result;
        } catch (InvalidPageNumberException $e) {
            $state->setState(StateInterface::PAGE, 0);

            return $this->applyStateToResults($results, $moduleConfig, $state);
        }
    }

    /**
     * @return ResultInterface
     */
    private function applyMaxAllowedResults(ResultInterface $results, ConfigurationInterface $moduleConfiguration)
    {
        if (null === $moduleConfiguration->getMaxResultLimitation()) {
            return $results;
        }

        $results->setMaxAllowedResults($moduleConfiguration->getMaxResultLimitation());

        return $results;
    }

    /**
     * @param ResultInterface $results
     *
     * @return ResultDataInterface
     */
    private function createResultDataFromResults(ResultInterface $results)
    {
        $resultData = new ResultData();
        $resultData
            ->setPageSize($results->getPageSize())
            ->setPage($results->getPage())
            ->setItems($results->asArray())
            ->setTotalNumberOfResults($results->count());
        $resultData->setRawResult($results);

        return $resultData;
    }

    /**
     * @param ConfigurationInterface $moduleConfiguration
     *
     * @return DatabaseAccessLayer\Interfaces\FilterInterface
     */
    private function getFilter(ConfigurationInterface $moduleConfiguration)
    {
        $filterId = $moduleConfiguration->getDefaultFilterId();
        if (isset($this->filterCache[$filterId])) {
            return $this->filterCache[$filterId];
        }
        $filterConfiguration = $this->dbAdapter->getFilterDefinitionFromId($moduleConfiguration->getDefaultFilterId());
        $this->filterCache[$filterId] = $this->filterFactory->createFilter($filterConfiguration);

        return $this->filterCache[$filterId];
    }

    public function _AllowCache(ConfigurationInterface $moduleConfiguration)
    {
        return $this->getFilter($moduleConfiguration)->_AllowCache();
    }

    public function _GetCacheParameters(ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        $cacheParameter = $this->getFilter($moduleConfiguration)->_GetCacheParameters();
        $cacheParameter['state'] = $state->getStateArray();
        $cacheParameter['configuration_id'] = $moduleConfiguration->getId();

        return $cacheParameter;
    }

    public function _GetCacheTableInfos(ConfigurationInterface $moduleConfiguration)
    {
        $filter = $this->getFilter($moduleConfiguration)->_GetCacheTableInfos();
        $result = array(
            array('table' => 'shop_module_article_list', 'id' => $moduleConfiguration->getId()),
            array('table' => 'shop_article', 'id' => null),
            array('table' => 'shop', 'id' => null),
            array('table' => 'shop_category', 'id' => null),
            array('table' => 'shop_manufacturer', 'id' => null),
        );

        return array_merge($filter, $result);
    }

    public function getFilterQuery(ConfigurationInterface $moduleConfiguration)
    {
        return $this->getFilter($moduleConfiguration)->getFilterQuery($moduleConfiguration);
    }

    /**
     * @param array $state
     *
     * @return string
     */
    private function getHashFromArray($state)
    {
        ksort($state);

        foreach (array_keys($state) as $key) {
            if (is_array($state[$key])) {
                $state[$key] = $this->getHashFromArray($state[$key]);
            }
        }

        return md5(json_encode($state));
    }

    public function moduleInitHook(ConfigurationInterface $moduleConfiguration)
    {
        $this->getFilter($moduleConfiguration)->ModuleInitHook();
    }

    /**
     * @return void
     */
    private function triggerFilterExecutedEvent(ResultDataInterface $resultData, ConfigurationInterface $moduleConfiguration, StateInterface $state)
    {
        $event = new ArticleListFilterExecutedEvent(
            $this->getFilter($moduleConfiguration),
            $resultData,
            $moduleConfiguration,
            $state
        );
        $this->eventDispatcher->dispatch(ShopEvents::ARTICLE_LIST_FILTER_EXECUTED, $event);
    }
}
