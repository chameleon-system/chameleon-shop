<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces;

interface ListResultInterface
{
    /**
     * @return string
     */
    public function getNextPageUrl();

    /**
     * @return string
     */
    public function getPreviousPageUrl();

    /**
     * @return array<string, ListItemInterface>
     */
    public function getItemList();

    /**
     * @param string $url
     * @return void
     */
    public function setNextPageUrl($url);

    /**
     * @param string $url
     * @return void
     */
    public function setPreviousPageUrl($url);

    /**
     * @param array<string, ListItemInterface> $items (key = id)
     * @return void
     */
    public function setItemList(array $items);
}
