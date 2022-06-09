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

interface RequestToListUrlConverterInterface
{
    const URL_PARAMETER_SPOT_NAME = '_ref';
    const URL_PARAMETER_LIST_URL = 'url';

    /**
     * @return null|string
     */
    public function getListUrl();

    /**
     * @return null|string
     */
    public function getListSpotName();

    /**
     * @param string $listSpotName
     * @param string $listPageUrl
     *
     * @return array<string, string>
     */
    public function getPagerParameter($listSpotName, $listPageUrl);
}
