<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopListFilterBundle\pkgShop\objects\ArticleList\StateRequestExtractor;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorInterface;

class StateRequestExtractor implements StateRequestExtractorInterface
{
    public const CONFIG_CAN_BE_FILTERED = 'can_be_filtered';

    /**
     * {@inheritDoc}
     */
    public function extract(array $configuration, array $requestData, $listSpotName)
    {
        if (false === $this->postSearchFilterEnabled($configuration)) {
            return []; // no need to add state data
        }
        $stateData = [];
        if (false === isset($requestData[\TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA])) {
            // we don't need to initialize whole filter
            return [];
        }
        $filterState = \TdbPkgShopListfilter::GetActiveInstance()->GetCurrentFilterAsArray();
        if (count($filterState) > 0) {
            $stateData[StateInterface::QUERY] = $filterState;
        }

        return $stateData;
    }

    /**
     * @param array $configuration
     *
     * @return bool
     */
    private function postSearchFilterEnabled($configuration)
    {
        return true === isset($configuration[self::CONFIG_CAN_BE_FILTERED]) && '1' === $configuration[self::CONFIG_CAN_BE_FILTERED];
    }
}
