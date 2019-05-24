<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Controller\ChameleonControllerInterface;
use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\ShopBundle\Interfaces\ShopRouteArticleFactoryInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TPkgShopRouteControllerArticle extends \esono\pkgCmsRouting\AbstractRouteController
{
    /**
     * @var ChameleonControllerInterface
     */
    private $mainController;
    /**
     * @var ShopRouteArticleFactoryInterface
     */
    private $shopRouteArticleFactory;
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param Request $request
     * @param string  $identifier
     * @param string  $pagedef
     *
     * @return Response
     */
    public function shopArticleQuickShop(Request $request, $identifier, $pagedef)
    {
        $cmsident = $identifier;
        $queryParameter = $request->query->all();
        if (0 === count($queryParameter)) {
            $queryParameter = null;
        }

        $aResponse = array(
            'activeShopArticle' => null,
            'activeShopCategory' => null,
            'redirectURL' => null,
            'redirectPermanent' => false,
            'noMatch' => false,
            'pagedef' => $pagedef,
            'queryParameter' => $queryParameter,
        );

        $article = $this->createArticleFromIdentificationToken($cmsident);

        if (null === $article) {
            $aResponse['noMatch'] = true;

            return $this->processArticleResponse($aResponse, null);
        }

        if (false === $article->AllowDetailviewInShop()) {
            $aResponse['noMatch'] = true;

            return $this->processArticleResponse($aResponse, null);
        }

        $variantArticle = TdbShopVariantDisplayHandler::GetArticleMatchingCurrentSelection($article);
        if (null !== $variantArticle) {
            $aResponse['activeShopArticle'] = $variantArticle;
        } else {
            $aResponse['activeShopArticle'] = $article;
        }

        /** @var PortalDomainServiceInterface $portalDomainService */
        $portalDomainService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.portal_domain_service');
        $activePortal = $portalDomainService->getActivePortal();
        $aResponse['pagedef'] = $activePortal->GetSystemPageId('quickshop');

        return $this->processArticleResponse($aResponse, $request, null);
    }

    /**
     * @param string $articleIdentificationToken
     *
     * @return TdbShopArticle|null
     */
    private function createArticleFromIdentificationToken($articleIdentificationToken)
    {
        return $this->shopRouteArticleFactory->createArticleFromIdentificationToken($articleIdentificationToken);
    }

    /**
     * @param Request     $request
     * @param string      $identifier
     * @param string      $pagedef
     * @param string|null $catid
     *
     * @return Response
     */
    public function shopArticle(Request $request, $identifier, $pagedef, $catid = null)
    {
        $queryParameter = $request->query->all();
        if (isset($queryParameter[TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY])) {
            unset($queryParameter[TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY]);
        }
        if (0 === count($queryParameter)) {
            $queryParameter = null;
        }

        $cmsident = $identifier;

        $variantSelection = TdbShopVariantDisplayHandler::GetActiveVariantTypeSelection(true);
        $aKey = array('class' => __CLASS__, 'fnc' => 'shopArticle', 'catid' => $catid, 'cmsident' => $cmsident, 'variantSelection' => $variantSelection);
        $cache = $this->getCache();
        $key = $cache->getKey($aKey);

        $aResponse = $cache->get($key);
        if (null !== $aResponse) {
            $aResponse['queryParameter'] = $queryParameter;
            // check if we need a redirect

            if (!isset($aResponse['activeShopArticle']) || null === $aResponse['activeShopArticle']) {
                return $this->processArticleResponse($aResponse, null);
            }

            $realItemURL = (isset($aResponse['fullURL'])) ? $aResponse['fullURL'] : $request->getPathInfo();
            if ($realItemURL !== $request->getPathInfo()) {
                $aResponse['redirectURL'] = $realItemURL;
                $aResponse['redirectPermanent'] = true;
            }

            return $this->processArticleResponse($aResponse, $request);
        }

        $aResponse = array(
            'activeShopArticle' => null,
            'activeShopCategory' => null,
            'redirectURL' => null,
            'redirectPermanent' => false,
            'fullURL' => null,
            'noMatch' => false,
            'pagedef' => $pagedef,
            'queryParameter' => $queryParameter,
        );

        $article = $this->createArticleFromIdentificationToken($cmsident);
        if (null === $article) {
            $aResponse['noMatch'] = true;

            return $this->processArticleResponse($aResponse, $request, $key);
        }

        $category = $this->getValidCategoryForArticle($catid, $article);

        $canShowCategory = true;

        if (null === $category && null !== $article->GetPrimaryCategory()) {
            $currentArticlePath = $article->getLink(false);
            $requestedPath = $request->getPathInfo();
            if ($requestedPath !== $currentArticlePath) {
                $aResponse['redirectURL'] = $article->getLink(true);
                $aResponse['fullURL'] = $aResponse['redirectURL'];
                $aResponse['redirectPermanent'] = true;
            }

            $canShowCategory = false;
        }

        $canShowDetails = true === $article->AllowDetailviewInShop();

        if (false === $canShowDetails || false === $canShowCategory) {
            if (false === $canShowDetails) {
                $aResponse = $this->prepareArticleResponseWhenDetailViewNotAllowed($aResponse, $article, $catid);
            }

            return $this->processArticleResponse($aResponse, $request, $key);
        }

        $variantArticle = $article;
        if (!is_null($article) && (false !== $variantSelection || false === $article->IsVariant())) {
            $variantArticle = TdbShop::GetActiveItemVariant($article);
        }

        $realItemURL = $this->getArticleFullUrlForRequest($category, $variantArticle);
        $aResponse['fullURL'] = $realItemURL;
        $currentFullURL = $request->getPathInfo();
        if ($realItemURL !== $currentFullURL && $variantArticle->AllowDetailviewInShop()) {
            $aResponse['redirectURL'] = $realItemURL;
            $aResponse['redirectPermanent'] = true;
        }
        $article = $variantArticle;

        $aResponse['activeShopArticle'] = $article;
        if (null !== $category) {
            $aResponse['activeShopCategory'] = $category;
        }

        return $this->processArticleResponse($aResponse, $request, $key);
    }

    /**
     * @param array          $articleResponse
     * @param TdbShopArticle $article
     * @param string         $categoryId
     *
     * @return array
     */
    protected function prepareArticleResponseWhenDetailViewNotAllowed(array $articleResponse, TdbShopArticle $article, $categoryId)
    {
        if ($article->IsVariant()) {
            $oActiveItemParent = $article->GetFieldVariantParent();
            if ($oActiveItemParent && $oActiveItemParent->AllowDetailviewInShop()) {
                $articleResponse['redirectURL'] = $oActiveItemParent->getLink(
                    true,
                    null,
                    array(TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $categoryId)
                );
                $articleResponse['fullURL'] = $articleResponse['redirectURL'];
            }
        }
        $articleResponse['noMatch'] = true;

        return $articleResponse;
    }

    /**
     * @param array        $aResponse
     * @param Request|null $request
     *
     * @return Response
     */
    private function processResponse($aResponse, $request)
    {
        $redirectUrl = (null !== $aResponse['redirectURL']) ? $aResponse['redirectURL'] : null;
        if (null !== $redirectUrl) {
            if (isset($aResponse['queryParameter']) && is_array($aResponse['queryParameter'])) {
                $redirectUrl .= $this->urlUtil->getArrayAsUrl($aResponse['queryParameter'], '?', '&');
            }
            $code = isset($aResponse['redirectPermanent']) ? 301 : 302;

            return new RedirectResponse($redirectUrl, $code);
        }

        if (true === $aResponse['noMatch']) {
            throw new NotFoundHttpException('route matches shop article route pattern, but the article id passed was not found or is disabled.');
        }

        $request->attributes->set('pagedef', $aResponse['pagedef']);
        $request->query->set('pagedef', $aResponse['pagedef']);

        if (null !== $aResponse['activeShopArticle']) {
            $request->attributes->set('activeShopArticle', $aResponse['activeShopArticle']);
        }

        if (null !== $aResponse['activeShopCategory']) {
            $request->attributes->set('activeShopCategory', $aResponse['activeShopCategory']);
        }

        if (null === $this->mainController) {
            throw new BadMethodCallException('No main controller has been set before calling __invoke()');
        }

        return $this->mainController->__invoke();
    }

    /**
     * @param array        $aResponse
     * @param Request|null $request
     * @param string|null  $cacheKey
     *
     * @return Response
     */
    private function processArticleResponse($aResponse, $request, $cacheKey = null)
    {
        $isRedirect = (is_array($aResponse) && isset($aResponse['redirectURL']) && '' != $aResponse['redirectURL']);
        if (null !== $cacheKey && false === $isRedirect) {
            $trigger = array(
                array('table' => 'shop_article', 'id' => null),
                array('table' => 'shop_category', 'id' => null),
            );
            $cacheResponse = $aResponse;
            $cacheResponse['queryParameter'] = null;
            $this->getCache()->set($cacheKey, $cacheResponse, $trigger);
        }

        return $this->processResponse($aResponse, $request);
    }

    /**
     * @param array        $aResponse
     * @param Request|null $request
     * @param string|null  $cacheKey
     *
     * @return Response
     */
    private function processCategoryResponse($aResponse, $request, $cacheKey = null)
    {
        if (null !== $cacheKey) {
            $trigger = array(
                array('table' => 'shop_category', 'id' => null),
            );
            $cacheResponse = $aResponse;
            $cacheResponse['queryParameter'] = null;
            $this->getCache()->set($cacheKey, $cacheResponse, $trigger);
        }

        return $this->processResponse($aResponse, $request);
    }

    /**
     * @param Request $request
     * @param string  $category
     * @param string  $categoryPath
     * @param string  $pagedef
     *
     * @return Response
     */
    public function shopCategory(Request $request, $category, $categoryPath, $pagedef)
    {
        $catPath = rtrim($category, '/');
        if ('.html' === substr($catPath, -5) && strlen($catPath) > 5) {
            $catPath = substr($catPath, 0, -5);
        }

        $oPathCat = TdbShopCategoryList::GetCategoryForCategoryPath(explode('/', $catPath));

        if (null !== $oPathCat && true === $oPathCat->AllowDisplayInShop()) {
            $aKey = array(
                'class' => __CLASS__,
                'method' => 'shopCategory',
                'path' => $category,
            );
            $cache = $this->getCache();
            $key = $cache->getKey($aKey);
            $aResponse = $cache->get($key);
            if (null !== $aResponse) {
                return $this->processCategoryResponse($aResponse, $request);
            }

            $queryParameter = $request->query->all();
            if (0 === count($queryParameter)) {
                $queryParameter = null;
            }

            $aResponse = array(
                'activeShopArticle' => null,
                'activeShopCategory' => null,
                'redirectURL' => null,
                'redirectPermanent' => false,
                'noMatch' => false,
                'pagedef' => $pagedef,
                'queryParameter' => $queryParameter,
            );

            // does the URL match?
            $url = $oPathCat->GetLink(false);

            $catPrefix = $categoryPath;
            $tmp = array();
            if ('' !== $catPrefix) {
                $tmp[] = $catPrefix;
            }
            if ('' !== $category) {
                $tmp[] = $category;
            }
            $fullPath = '/'.implode('/', $tmp);

            if (false === $this->compareRelativeAndPrefixedURL($fullPath, $url)) {
                $aResponse['redirectURL'] = $oPathCat->GetLink(true);
                $aResponse['redirectPermanent'] = true;
                $key = null; // skip caching on redirect, otherwise we could lose URL parameters.
            }

            $aResponse['activeShopCategory'] = $oPathCat;
            if ('' !== $oPathCat->fieldDetailPageCmsTreeId) {
                $targetNode = TdbCmsTree::GetNewInstance($oPathCat->fieldDetailPageCmsTreeId);
                if (false !== $targetNode->sqlData) {
                    $pagedef = $targetNode->GetLinkedPage();
                    if (null !== $pagedef && false !== $pagedef) {
                        $aResponse['pagedef'] = $pagedef;
                    }
                }
            }
        } else {
            $aResponse['noMatch'] = true;
        }

        return $this->processCategoryResponse($aResponse, $request, $key);
    }

    /**
     * @param TdbShopCategory $cat
     * @param TdbShopArticle  $article
     *
     * @return string
     */
    private function getArticleFullUrlForRequest($cat, $article)
    {
        $catParameter = array();
        if (null !== $cat) {
            $catParameter = array(TdbShopArticle::CMS_LINKABLE_OBJECT_PARAM_CATEGORY => $cat->id);
        }

        return $article->getLink(false, null, $catParameter);
    }

    /**
     * @param ShopRouteArticleFactoryInterface $shopRouteArticleFactory
     */
    public function setShopRouteArticleFactory(ShopRouteArticleFactoryInterface $shopRouteArticleFactory)
    {
        $this->shopRouteArticleFactory = $shopRouteArticleFactory;
    }

    /**
     * @param ChameleonControllerInterface $mainController
     */
    public function setMainController(ChameleonControllerInterface $mainController)
    {
        $this->mainController = $mainController;
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterface
     */
    private function getCache()
    {
        if (null === $this->cache) {
            $this->cache = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_cms_cache.cache');
        }

        return $this->cache;
    }

    private function getValidCategoryForArticle(?string $requestedCategoryId, TdbShopArticle $article): ?TdbShopCategory
    {
        if (null === $requestedCategoryId || 0 === $requestedCategoryId) {
            return null;
        }

        $category = TdbShopCategory::GetNewInstance();
        if (false === $category->LoadFromField('cmsident', $requestedCategoryId)) {
            return null;
        }

        if (false === $category->AllowDisplayInShop()) {
            return null;
        }

        if (false === $article->IsInCategory([$category->id])) {
            return null;
        }

        return $category;
    }
}
