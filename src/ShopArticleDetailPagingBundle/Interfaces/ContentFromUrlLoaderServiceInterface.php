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

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ContentLoadingException;

interface ContentFromUrlLoaderServiceInterface
{
    /**
     * @param string $url
     *
     * @return string
     *
     * @throws ContentLoadingException
     */
    public function load($url);
}
