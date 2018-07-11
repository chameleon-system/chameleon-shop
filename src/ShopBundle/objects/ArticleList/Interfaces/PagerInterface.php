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

interface PagerInterface
{
    public function __construct($baseUrl, StateInterface $state);

    public function getPageLink($pageNumber);

    public function getFirstPageLink();

    public function getLastPageLink();
}
