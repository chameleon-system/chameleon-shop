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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterDefinitionInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\FilterFactoryInterface;

class FilterFactory implements FilterFactoryInterface
{
    /**
     * This works because `ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Filter` extends
     * `ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\FilterDefinition`.
     *
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     *
     * @return FilterInterface
     */
    public function createFilter(FilterDefinitionInterface $filterDefinition)
    {
        return $filterDefinition; // currently the filter is an extension of the filter definition - that is nonsense but can not be changed without breaking oodles of old code.
    }

    /**
     * @return FilterInterface|null
     */
    public function createFallbackFilter(FilterInterface $filter)
    {
        if (true === $filter->PreventUseOfParentObjectWhenNoRecordsAreFound()) {
            return null;
        }

        return $filter->getFallbackListFilter();
    }
}
