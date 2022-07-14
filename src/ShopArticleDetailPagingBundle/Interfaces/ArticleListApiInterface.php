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

interface ArticleListApiInterface
{
    /**
     * @param string $listUrl
     * @param string $spot
     *
     * @return ListResultInterface
     *
     * @throws ArticleListException
     */
    public function get($listUrl, $spot);
}
