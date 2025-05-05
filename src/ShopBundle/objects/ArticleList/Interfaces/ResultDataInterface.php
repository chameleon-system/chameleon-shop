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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;

interface ResultDataInterface
{
    /**
     * @param int $totalNumberOfResults
     *
     * @return void
     */
    public function setTotalNumberOfResults($totalNumberOfResults);

    /**
     * @return int
     */
    public function getTotalNumberOfResults();

    /**
     * @return int
     */
    public function count();

    /**
     * @return int
     */
    public function getPage();

    /**
     * @param int $page
     *
     * @return $this
     */
    public function setPage($page);

    /**
     * @return \TdbShopArticle[]
     */
    public function asArray();

    /**
     * @param \TdbShopArticle[] $items
     *
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @return int
     */
    public function getNumberOfPages();

    /**
     * @param int $pageSize
     *
     * @return $this
     */
    public function setPageSize($pageSize);

    /**
     * @return int
     */
    public function getPageSize();

    /**
     * @return ResultInterface
     */
    public function getRawResult();

    /**
     * @return void
     */
    public function setRawResult(ResultInterface $rawResult);
}
