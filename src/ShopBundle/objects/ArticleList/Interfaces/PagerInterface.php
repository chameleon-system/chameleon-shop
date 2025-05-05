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
    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl, StateInterface $state);

    /**
     * @param int $pageNumber
     *
     * @return string
     */
    public function getPageLink($pageNumber);

    /**
     * @return string
     */
    public function getFirstPageLink();

    /**
     * @return string
     */
    public function getLastPageLink();
}
