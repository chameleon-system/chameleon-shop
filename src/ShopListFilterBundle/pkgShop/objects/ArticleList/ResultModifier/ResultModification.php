<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopListFilterBundle\pkgShop\objects\ArticleList\ResultModifier;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\ResultModifier\Interfaces\ResultModificationInterface;

class ResultModification implements ResultModificationInterface
{
    const CONFIG_CAN_BE_FILTERED = 'can_be_filtered';

    /**
     * @param ResultInterface $result
     * @param array           $configuration
     * @param $filterDepth
     *
     * @return ResultInterface
     */
    public function apply(ResultInterface $result, array $configuration, $filterDepth)
    {
        if (false === $this->listAllowsUseOfPostSearchFilter($configuration, $filterDepth)) {
            return $result;
        }

        $result->addFilterQueryString($this->getFilterQueryString());

        return $result;
    }

    private function listAllowsUseOfPostSearchFilter(array $configuration, $filterDepth)
    {
        if (false === $this->postSearchFilterEnabled($configuration)) {
            return false;
        }

        if (true === $this->isTheResultSetOfAFallbackFilter($filterDepth)) {
            return false;
        }

        return true;
    }

    private function postSearchFilterEnabled($configuration)
    {
        return true === isset($configuration[self::CONFIG_CAN_BE_FILTERED]) && '1' === $configuration[self::CONFIG_CAN_BE_FILTERED];
    }

    private function isTheResultSetOfAFallbackFilter($filterDepth)
    {
        return $filterDepth > 0;
    }

    private function getFilterQueryString()
    {
        return \TdbPkgShopListfilter::GetActiveInstance()->getActiveFilterAsQueryString();
    }

    /**
     * @param ResultInterface $result
     * @param array           $configuration
     * @param StateInterface  $state
     *
     * @return ResultInterface
     */
    public function applyState(ResultInterface $result, array $configuration, StateInterface $state)
    {
        return $result;
    }
}
