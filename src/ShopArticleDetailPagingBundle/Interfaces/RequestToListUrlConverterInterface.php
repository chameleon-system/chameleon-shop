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

    public function getListUrl();

    public function getListSpotName();

    /**
     * @param $listSpotName
     * @param $listPageUrl
     *
     * @return array
     */
    public function getPagerParameter($listSpotName, $listPageUrl);
}
