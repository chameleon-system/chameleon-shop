<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\EventListener;

use ChameleonSystem\SearchBundle\Interfaces\ShopSearchLoggerInterface;
use ChameleonSystem\SearchBundle\Interfaces\ShopSearchSessionInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Event\ArticleListFilterExecutedEvent;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

class SearchResultLoggerListener
{
    /**
     * @var ShopSearchSessionInterface
     */
    private $session;
    /**
     * @var ShopSearchLoggerInterface
     */
    private $searchLogger;
    /**
     * @var ShopServiceInterface
     */
    private $shopService;

    /**
     * @param ShopSearchSessionInterface $session
     * @param ShopSearchLoggerInterface  $searchLogger
     * @param ShopServiceInterface       $shopService
     */
    public function __construct(ShopSearchSessionInterface $session, ShopSearchLoggerInterface $searchLogger, ShopServiceInterface $shopService)
    {
        $this->session = $session;
        $this->searchLogger = $searchLogger;
        $this->shopService = $shopService;
    }

    public function onArticleListResultGenerated(ArticleListFilterExecutedEvent $event)
    {
        if (false === $this->isSearchEvent($event)) {
            return;
        }

        if (false === $this->isInitialSearch($event)) {
            return;
        }

        $numberOfMatches = $this->getNumberOfResults($event);

        $searchParameter = $this->getSearchParameters($event);

        if ($this->shopService->getActiveShop()->fieldUseShopSearchLog) {
            $this->logSearch(
                $searchParameter[\TShopModuleArticlelistFilterSearch::PARAM_QUERY],
                $searchParameter[\TShopModuleArticlelistFilterSearch::URL_FILTER],
                $numberOfMatches
            );
        }

        $this->addSearch($searchParameter);
    }

    private function isSearchEvent(ArticleListFilterExecutedEvent $event)
    {
        $searchClass = '\TShopModuleArticlelistFilterSearch';

        return $event->getFilter() instanceof $searchClass;
    }

    private function isInitialSearch(ArticleListFilterExecutedEvent $event)
    {
        $searchParameter = $this->getSearchParameters($event);

        return false === $this->session->hasSearchedFor($searchParameter);
    }

    private function getNumberOfResults(ArticleListFilterExecutedEvent $event)
    {
        return $event->getResultData()->getTotalNumberOfResults();
    }

    private function getSearchParameters(ArticleListFilterExecutedEvent $event)
    {
        $stateQuery = $event->getState()->getState(StateInterface::QUERY, array());

        $searchParameter = array(
            \TShopModuleArticlelistFilterSearch::PARAM_QUERY => isset($stateQuery[\TShopModuleArticlelistFilterSearch::PARAM_QUERY]) ? $stateQuery[\TShopModuleArticlelistFilterSearch::PARAM_QUERY] : '',
            \TShopModuleArticlelistFilterSearch::URL_FILTER => isset($stateQuery[\TShopModuleArticlelistFilterSearch::URL_FILTER]) ? $stateQuery[\TShopModuleArticlelistFilterSearch::URL_FILTER] : array(),
        );

        return $searchParameter;
    }

    private function logSearch($searchString, array $searchFilter, $numberOfMatches)
    {
        $this->searchLogger->logSearch($searchString, $searchFilter, $numberOfMatches);
    }

    private function addSearch(array $searchParameter)
    {
        $this->session->addSearch($searchParameter);
    }
}
