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

use ChameleonSystem\ShopBundle\objects\ArticleList\Event\ArticleListFilterExecutedEvent;

class SearchResultTriggerCmsObserverListener
{
    public function onArticleListResultGenerated(ArticleListFilterExecutedEvent $event)
    {
        if (false === $this->isSearchEvent($event)) {
            return;
        }

        $oSearchObserver = &\TPkgSearchObserver::GetInstance();
        $oSearchObserver->AddSearch('pkgShop', $this->getNumberOfResults($event));
    }

    private function isSearchEvent(ArticleListFilterExecutedEvent $event)
    {
        $searchClass = '\TShopModuleArticlelistFilterSearch';

        return $event->getFilter() instanceof $searchClass;
    }

    private function getNumberOfResults(ArticleListFilterExecutedEvent $event)
    {
        return $event->getResultData()->getTotalNumberOfResults();
    }
}
