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

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ArticleListException;

interface DetailPagingServiceInterface
{
    /**
     * @param string $currentArticleId
     * @param string $pagerSpotName
     *
     * @return ListItemInterface
     *
     * @throws ArticleListException
     */
    public function getNextItem($currentArticleId, $pagerSpotName);

    /**
     * @param string $currentArticleId
     * @param string $pagerSpotName
     *
     * @return ListItemInterface
     *
     * @throws ArticleListException
     */
    public function getPreviousItem($currentArticleId, $pagerSpotName);

    /**
     * @param string $pagerSpotName
     * @return string|null
     */
    public function getBackToListUrl($pagerSpotName);
}
