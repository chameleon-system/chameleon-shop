<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces;

use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageNumberException;

interface ResultInterface
{
    public function count();

    /**
     * @param $pageSize
     *
     * @throws \ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageNumberException
     */
    public function setPageSize($pageSize);

    public function getPageSize();

    /**
     * @param int $page
     *
     * @throws InvalidPageNumberException
     */
    public function setPage($page);

    /**
     * @return int
     */
    public function getPage();

    public function setSort(array $sort);

    /**
     * @return \TdbShopArticle[]
     */
    public function asArray();

    public function getNumberOfPages();

    public function addFilterQueryString($filterQueryString);

    /**
     * limit result to this. pass null to remove limit.
     *
     * @param int $maxAllowedResults
     */
    public function setMaxAllowedResults($maxAllowedResults);
}
