<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Service;

use ChameleonSystem\CmsStringUtilitiesBundle\Interfaces\UrlUtilityServiceInterface;

class AddParametersToUrlService
{
    /**
     * @var \ChameleonSystem\CmsStringUtilitiesBundle\Interfaces\UrlUtilityServiceInterface
     */
    private $urlUtility;

    public function __construct(UrlUtilityServiceInterface $urlUtility)
    {
        $this->urlUtility = $urlUtility;
    }

    /**
     * @return string
     *
     * @param null|string $url
     */
    public function addParameterToUrl($url, array $parameter)
    {
        return $this->urlUtility->addParameterToUrl($url, $parameter);
    }
}
