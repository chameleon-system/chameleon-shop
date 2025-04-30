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

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;

class ResultData implements ResultDataInterface
{
    /**
     * @var \TdbShopArticle[]
     */
    private $items = [];
    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $pageSize = -1;

    /**
     * @var int
     */
    private $totalNumberOfResults = 0;

    /**
     * @var ResultInterface
     */
    private $rawResult;

    public function count()
    {
        return $this->totalNumberOfResults;
    }

    public function setTotalNumberOfResults($totalNumberOfResults)
    {
        $this->totalNumberOfResults = (int) $totalNumberOfResults;
    }

    public function getTotalNumberOfResults()
    {
        return $this->totalNumberOfResults;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    public function setPage($page)
    {
        $this->page = (int) $page;

        return $this;
    }

    /**
     * @return \TdbShopArticle[]
     */
    public function asArray()
    {
        return $this->items;
    }

    /**
     * @param \TdbShopArticle[] $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return int
     *
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function getNumberOfPages()
    {
        if ($this->pageSize < 1) {
            return 1;
        }

        return ceil($this->count() / $this->pageSize);
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;

        return $this;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return void
     */
    public function setRawResult(ResultInterface $rawResult)
    {
        $this->rawResult = $rawResult;
    }

    /**
     * @return ResultInterface
     */
    public function getRawResult()
    {
        return $this->rawResult;
    }
}
