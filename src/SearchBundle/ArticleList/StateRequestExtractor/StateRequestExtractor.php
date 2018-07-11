<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\SearchBundle\ArticleList\StateRequestExtractor;

use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorInterface;

class StateRequestExtractor implements StateRequestExtractorInterface
{
    /**
     * @param array $configuration
     * @param array $requestData
     *
     * @return array
     */
    public function extract(array $configuration, array $requestData, $listSpotName)
    {
        $searchQuery = $this->getSearchQuery($requestData);
        $searchFilter = $this->getSearchFilter($requestData);
        if (null === $searchQuery && null === $searchFilter) {
            return array();
        }

        $returnData = array(
            StateInterface::QUERY => array(),
        );
        if (null !== $searchQuery) {
            $returnData[StateInterface::QUERY][\TShopModuleArticlelistFilterSearch::PARAM_QUERY] = $searchQuery;
        }
        if (null !== $searchFilter) {
            $returnData[StateInterface::QUERY][\TShopModuleArticlelistFilterSearch::URL_FILTER] = $searchFilter;
        }

        return $returnData;
    }

    private function getSearchQuery($requestData)
    {
        return (isset($requestData[\TShopModuleArticlelistFilterSearch::PARAM_QUERY])) ? $requestData[\TShopModuleArticlelistFilterSearch::PARAM_QUERY] : null;
    }

    private function getSearchFilter($requestData)
    {
        return (isset($requestData[\TShopModuleArticlelistFilterSearch::URL_FILTER])) ? $requestData[\TShopModuleArticlelistFilterSearch::URL_FILTER] : null;
    }
}
