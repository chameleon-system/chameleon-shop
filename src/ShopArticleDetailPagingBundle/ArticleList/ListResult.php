<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\ArticleList;

use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListItemInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListResultInterface;

class ListResult implements ListResultInterface
{
    /**
     * @var string
     */
    private $nextPageUrl = null;
    /**
     * @var string
     */
    private $previousPageUrl = null;
    /**
     * @var array<string, ListItemInterface> (key = id)
     */
    private $itemList = array();

    /**
     * @param array<string, ListItemInterface> $itemList
     *
     * @return void
     */
    public function setItemList(array $itemList)
    {
        $this->itemList = $itemList;
    }

    /**
     * @return array<string, ListItemInterface>
     */
    public function getItemList()
    {
        return $this->itemList;
    }

    /**
     * @param string $nextPageUrl
     *
     * @return void
     */
    public function setNextPageUrl($nextPageUrl)
    {
        $this->nextPageUrl = $nextPageUrl;
    }

    /**
     * @return string
     */
    public function getNextPageUrl()
    {
        return $this->nextPageUrl;
    }

    /**
     * @param string $previousPageUrl
     *
     * @return void
     */
    public function setPreviousPageUrl($previousPageUrl)
    {
        $this->previousPageUrl = $previousPageUrl;
    }

    /**
     * @return string
     */
    public function getPreviousPageUrl()
    {
        return $this->previousPageUrl;
    }
}
