<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\pkgshoplistfilter\Interfaces;

interface FilterInterface
{
    /**
     * @psalm-suppress UndefinedDocblockClass
     *
     * @FIXME FilterFacetInterface does not exist - is this interface still in use and if yes: What should it return?
     *
     * @return FilterFacetInterface[]
     */
    public function getFacets();
}
