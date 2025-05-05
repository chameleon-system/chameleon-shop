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
     * @param string $listSpotName
     */
    public function extract(array $configuration, array $requestData, $listSpotName);

    /**
     * @return void
     */
    public function registerExtractor(StateRequestExtractorInterface $extractor);
}
