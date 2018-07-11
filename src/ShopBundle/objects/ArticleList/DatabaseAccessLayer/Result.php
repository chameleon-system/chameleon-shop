<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer;

use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ResultInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Exceptions\InvalidPageNumberException;

class Result implements ResultInterface
{
    /**
     * @var \TdbShopArticleList
     */
    private $content;
    private $page = 0;
    private $pageSize = -1;
    private $sort;

    public function __construct(\TdbShopArticleList $content)
    {
        $this->content = $content;
    }

    public function count()
    {
        return $this->content->Length();
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = (int) $pageSize;

        $this->transferPagingToContentObject();
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    private function transferPagingToContentObject()
    {
        if ($this->pageSize === -1) {
            $startRecord = 0;
        } else {
            $startRecord = $this->page * $this->pageSize;
        }
        if (false === $this->content->SetPagingInfo($startRecord, $this->pageSize)) {
            $this->page = 0;
            $this->content->SetPagingInfo(0, $this->pageSize);
            throw new InvalidPageNumberException("the page you requested [{$this->page}] is larger than the result set (found ".$this->count(
                )." records, page size set to {$this->pageSize})");
        }
    }

    public function setPage($page)
    {
        $this->page = (int) $page;
        if ($this->pageSize < 1 && 0 !== $this->page) {
            throw new \InvalidArgumentException('trying to move to another page, but page size is set to infinity');
        }
        $this->transferPagingToContentObject();
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    public function setSort(array $sort)
    {
        $this->sort = $sort;
        $this->content->ChangeOrderBy($this->sort);
    }

    /**
     * @return \TdbShopArticle[]
     */
    public function asArray()
    {
        $data = array();
        $this->content->GoToStart();
        while ($item = $this->content->Next()) {
            $data[] = $item;
        }

        return $data;
    }

    public function getNumberOfPages()
    {
        if ($this->pageSize < 1) {
            return 1;
        }

        return ceil($this->count() / $this->pageSize);
    }

    public function addFilterQueryString($filterQueryString)
    {
        if (null !== $filterQueryString && '' !== $filterQueryString) {
            $this->content->AddFilterString($filterQueryString);
        }
    }

    /**
     * limit result to this. pass null to remove limit.
     *
     * @param int $maxAllowedResults
     */
    public function setMaxAllowedResults($maxAllowedResults)
    {
        $this->content->SetActiveListLimit($maxAllowedResults);
    }
}
