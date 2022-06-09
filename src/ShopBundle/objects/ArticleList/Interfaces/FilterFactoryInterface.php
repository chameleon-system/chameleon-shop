<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterDefinitionInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\FilterInterface;

interface FilterFactoryInterface
{
    /**
     * @param FilterDefinitionInterface $filterDefinition
     *
     * @return FilterInterface
     */
    public function createFilter(FilterDefinitionInterface $filterDefinition);

    /**
     * @param FilterInterface $filter
     *
     * @return FilterInterface|null
     */
    public function createFallbackFilter(FilterInterface $filter);
}
