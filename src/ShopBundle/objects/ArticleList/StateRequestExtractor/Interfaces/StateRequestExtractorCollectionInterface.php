<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces;

interface StateRequestExtractorCollectionInterface
{
    /**
     * @param array $configuration
     * @param array $requestData
     * @param string $listSpotName
     *
     * @return mixed
     */
    public function extract(array $configuration, array $requestData, $listSpotName);

    /**
     * @param StateRequestExtractorInterface $extractor
     * @return void
     */
    public function registerExtractor(StateRequestExtractorInterface $extractor);
}
