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

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ArticleListException;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ArticleListApiInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\DetailPagingServiceInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ListItemInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\RequestToListUrlConverterInterface;

class DetailPagingService implements DetailPagingServiceInterface
{
    /**
     * @var RequestToListUrlConverterInterface
     */
    private $requestToListUrlConverter;
    /**
     * @var ArticleListApiInterface
     */
    private $listApi;
    /**
     * @var AddParametersToUrlService
     */
    private $addParametersToUrlService;

    public function __construct(
        ArticleListApiInterface $listApi,
        RequestToListUrlConverterInterface $requestToListUrlConverter,
        AddParametersToUrlService $addParametersToUrlService
    ) {
        $this->requestToListUrlConverter = $requestToListUrlConverter;
        $this->listApi = $listApi;
        $this->addParametersToUrlService = $addParametersToUrlService;
    }

    /**
     * @param $currentArticleId
     *
     * @return ListItemInterface
     *
     * @throws ArticleListException
     */
    public function getNextItem($currentArticleId, $pagerSpotName)
    {
        $result = $this->searchNext($currentArticleId, $this->requestToListUrlConverter->getListUrl());

        if (null === $result) {
            return null;
        }
        $item = $result['item'];
        $pageUrl = $result['pageUrl'];

        return $this->addPagerInfoToItem($item, $pageUrl);
    }

    /**
     * @param $currentArticleId
     *
     * @return ListItemInterface
     */
    public function getPreviousItem($currentArticleId, $pagerSpotName)
    {
        $result = $this->searchPrevious($currentArticleId, $this->requestToListUrlConverter->getListUrl());
        if (null === $result) {
            return null;
        }

        $item = $result['item'];
        $pageUrl = $result['pageUrl'];

        return $this->addPagerInfoToItem($item, $pageUrl);
    }

    /**
     * @return string
     */
    public function getBackToListUrl($pagerSpotName)
    {
        return $this->requestToListUrlConverter->getListUrl();
    }

    /**
     * @param string $currentArticleId
     * @param string $listUrl
     *
     * @return array|null
     *
     * @throws ArticleListException
     */
    private function searchNext($currentArticleId, $listUrl)
    {
        if (null === $listUrl) {
            return null;
        }

        $page = $this->listApi->get($listUrl, $this->requestToListUrlConverter->getListSpotName());
        $items = $page->getItemList();
        if (0 === count($items)) {
            return null;
        }

        $keys = array_keys($items);
        $positionCurrentElement = array_search($currentArticleId, $keys);
        if (false === $positionCurrentElement) {
            return null;
        }

        $positionNextElement = $positionCurrentElement + 1;
        // if we are the last element, we need to get the first of the next page
        if ($positionNextElement === count($keys)) {
            $nextPageUrl = $page->getNextPageUrl();
            if (null === $nextPageUrl) {
                return null;
            }
            $nextPage = $this->listApi->get($nextPageUrl, $this->requestToListUrlConverter->getListSpotName());
            $nextItems = $nextPage->getItemList();
            if (0 === count($nextItems)) {
                return null;
            }
            reset($nextItems);

            return array('item' => current($nextItems), 'pageUrl' => $nextPageUrl);
        }

        return array('item' => $items[$keys[$positionNextElement]], 'pageUrl' => $listUrl);
    }

    /**
     * @param string $currentArticleId
     * @param string $listUrl
     *
     * @return array|null
     *
     * @throws ArticleListException
     */
    private function searchPrevious($currentArticleId, $listUrl)
    {
        if (null === $listUrl) {
            return null;
        }

        $page = $this->listApi->get($listUrl, $this->requestToListUrlConverter->getListSpotName());
        $items = $page->getItemList();
        if (0 === count($items)) {
            return null;
        }

        $keys = array_keys($items);
        $positionCurrentElement = array_search($currentArticleId, $keys);
        if (false === $positionCurrentElement) {
            return null;
        }

        $positionPreviousElement = $positionCurrentElement - 1;
        // if we are the last element, we need to get the first of the next page
        if (-1 === $positionPreviousElement) {
            $previousPageUrl = $page->getPreviousPageUrl();
            if (null === $previousPageUrl) {
                return null;
            }
            $previousPage = $this->listApi->get($previousPageUrl, $this->requestToListUrlConverter->getListSpotName());
            $nextItems = $previousPage->getItemList();
            if (0 === count($nextItems)) {
                return null;
            }

            return array('item' => end($nextItems), 'pageUrl' => $previousPageUrl);
        }

        return array('item' => $items[$keys[$positionPreviousElement]], 'pageUrl' => $listUrl);
    }

    /**
     * @param ListItemInterface $item
     * @param $listPageUrl
     *
     * @return ListItemInterface
     */
    private function addPagerInfoToItem(ListItemInterface $item, $listPageUrl)
    {
        $pagerParameter = $this->requestToListUrlConverter->getPagerParameter($this->requestToListUrlConverter->getListSpotName(), $listPageUrl);
        $modifiedUrl = $this->addParametersToUrlService->addParameterToUrl($item->getUrl(), $pagerParameter);
        $item->setUrl($modifiedUrl);

        return $item;
    }
}
