<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\Bridge\Service;

use ChameleonSystem\CoreBundle\Util\InputFilterUtilInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface;

class RequestToListUrlConverter implements RequestToListUrlConverterInterface
{
    /**
     * @var InputFilterUtilInterface
     */
    private $inputFilterUtil;

    public function __construct(InputFilterUtilInterface $inputFilterUtil)
    {
        $this->inputFilterUtil = $inputFilterUtil;
    }

    /**
     * @return string|null
     */
    public function getListUrl()
    {
        return $this->getListUrlFromParameters();
    }

    /**
     * @return string|null
     */
    public function getListSpotName()
    {
        return $this->inputFilterUtil->getFilteredInput(self::URL_PARAMETER_SPOT_NAME, null);
    }

    /**
     * @return string|null
     */
    private function getListUrlFromParameters()
    {
        return $this->inputFilterUtil->getFilteredInput(self::URL_PARAMETER_LIST_URL, null);
    }

    /**
     * @param string $listSpotName
     * @param string $listPageUrl
     *
     * @return array<string, string>
     */
    public function getPagerParameter($listSpotName, $listPageUrl)
    {
        return [
            RequestToListUrlConverterInterface::URL_PARAMETER_SPOT_NAME => $listSpotName,
            RequestToListUrlConverterInterface::URL_PARAMETER_LIST_URL => $listPageUrl,
        ];
    }
}
