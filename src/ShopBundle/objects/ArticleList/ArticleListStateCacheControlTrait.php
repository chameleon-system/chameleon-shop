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

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;

trait ArticleListStateCacheControlTrait
{
    private function articleListStateAllowsCache(StateInterface $state): bool
    {
        if ((int) $state->getState(StateInterface::PAGE, 0) > $this->getMaxCacheableArticleListPage()) {
            return false;
        }

        if ([] !== $state->getState(StateInterface::QUERY, [])) {
            return false;
        }

        return true;
    }

    private function getMaxCacheableArticleListPage(): int
    {
        return 10;
    }
}
