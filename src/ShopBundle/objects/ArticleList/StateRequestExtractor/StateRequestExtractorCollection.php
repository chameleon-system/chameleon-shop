<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor;

use
    ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorCollectionInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorInterface;

class StateRequestExtractorCollection implements StateRequestExtractorCollectionInterface
{
    /**
     * @var StateRequestExtractorInterface[]
     */
    private $extractorList = array();

    /**
     * @param array $configuration
     * @param array $requestData
     * @param string $listSpotName
     *
     * @return array
     */
    public function extract(array $configuration, array $requestData, $listSpotName)
    {
        $data = isset($requestData[$listSpotName]) ? $requestData[$listSpotName] : array();
        foreach ($this->extractorList as $extractor) {
            $data = array_merge_recursive($data, $extractor->extract($configuration, $requestData, $listSpotName));
        }

        reset($this->extractorList);

        return $data;
    }

    public function registerExtractor(StateRequestExtractorInterface $extractor)
    {
        $this->extractorList[] = $extractor;
    }
}
