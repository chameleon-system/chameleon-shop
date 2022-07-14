<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopArticleDetailPagingBundle\ArticleList;

use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ArticleListException;
use ChameleonSystem\ShopArticleDetailPagingBundle\Exception\ContentLoadingException;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ArticleListApiInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface;
use ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService;

class ArticleListApi implements ArticleListApiInterface
{
    /**
     * @var \ChameleonSystem\ShopArticleDetailPagingBundle\Service\AddParametersToUrlService
     */
    private $addParametersToUrlService;
    /**
     * @var \ChameleonSystem\ShopArticleDetailPagingBundle\Interfaces\ContentFromUrlLoaderServiceInterface
     */
    private $contentLoader;

    /**
     * @var ListResult[]
     */
    private $cachedResults = array();

    public function __construct(ContentFromUrlLoaderServiceInterface $contentLoader, AddParametersToUrlService $addParametersToUrlService)
    {
        $this->contentLoader = $contentLoader;
        $this->addParametersToUrlService = $addParametersToUrlService;
    }

    /**
     * {@inheritdoc}
     */
    public function get($listUrl, $spot)
    {
        $listRequestURL = $this->getFullURL($listUrl, $spot);

        return $this->getListResponse($listRequestURL);
    }

    /**
     * @param string $listUrl
     * @param string $spot
     * @return string
     */
    private function getFullURL($listUrl, $spot)
    {
        $methodAccessParameter = array(
            'module_fnc' => array($spot => 'ExecuteAjaxCall'),
            '_fnc' => 'getAsJson',
        );

        return $this->addParametersToUrlService->addParameterToUrl($listUrl, $methodAccessParameter);
    }

    /**
     * @param string $listRequestURL
     *
     * @return ListResult
     *
     * @throws ArticleListException
     */
    private function getListResponse($listRequestURL)
    {
        $key = md5($listRequestURL);
        if (isset($this->cachedResults[$key])) {
            return $this->cachedResults[$key];
        }

        try {
            $responseString = $this->contentLoader->load($listRequestURL);
        } catch (ContentLoadingException $e) {
            throw new ArticleListException('Error while loading article list.', 0, $e);
        }

        $responseData = json_decode($responseString, true);
        if (null === $responseData) {
            throw new ArticleListException('Error while decoding article list response.');
        }
        $this->cachedResults[$key] = $this->createResponseObject($responseData);

        return $this->cachedResults[$key];
    }

    /**
     * @return ListResult
     */
    private function createResponseObject(array $responseData)
    {
        $responseObject = new ListResult();
        if (!empty($responseData['previousPage'])) {
            $responseObject->setPreviousPageUrl($responseData['previousPage']);
        }
        if (!empty($responseData['nextPage'])) {
            $responseObject->setNextPageUrl($responseData['nextPage']);
        }

        $itemObjs = array();
        foreach ($responseData['items'] as $item) {
            $itemObj = new ListItem();
            $itemObj->setId($item['id']);
            $itemObj->setName($item['name']);
            $itemObj->setUrl($item['url']);
            $itemObjs[$itemObj->getId()] = $itemObj;
        }
        $responseObject->setItemList($itemObjs);

        return $responseObject;
    }
}
