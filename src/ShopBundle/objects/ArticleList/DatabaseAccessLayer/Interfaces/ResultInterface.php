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
    /**
     * @return int
     */
    public function count();

    /**
     * @param int $pageSize
     *
     * @return void
     *
     * @throws InvalidPageNumberException
     */
    public function setPageSize($pageSize);

    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @param int $page
     *
     * @return void
     *
     * @throws InvalidPageNumberException
     */
    public function setPage($page);

    /**
     * @return int
     */
    public function getPage();

    /**
     * @psalm-param array<string, 'ASC'|'DESC'> $sort
     *
     * @return void
     */
    public function setSort(array $sort);

    /**
     * @return \TdbShopArticle[]
     */
    public function asArray();

    /**
     * @return int
     */
    public function getNumberOfPages();

    /**
     * @param string|null $filterQueryString
     *
     * @return void
     */
    public function addFilterQueryString($filterQueryString);

    /**
     * limit result to this. pass null to remove limit.
     *
     * @param int $maxAllowedResults
     *
     * @return void
     */
    public function setMaxAllowedResults($maxAllowedResults);
}
