<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\pkgshoplistfilter\objects;

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\pkgshoplistfilter\DatabaseAccessLayer\DbAdapter;
use ChameleonSystem\pkgshoplistfilter\Interfaces\FilterApiInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorCollectionInterface;
use esono\pkgCmsCache\CacheInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FilterApi implements FilterApiInterface
{
    private DbAdapter $dbAdapter;
    private DbAdapterInterface $listDbAdapter;
    private StateFactoryInterface $stateFactory;
    private RequestStack $requestStack;
    private ResultFactoryInterface $resultFactory;
    private ?ConfigurationInterface $listModuleConfig = null;
    private ActivePageServiceInterface $activePageService;
    private ?string $articleListSpotName = null;
    private ?StateInterface $articleListState = null;
    private CacheInterface $cache;
    private StateRequestExtractorCollectionInterface $stateRequestExtractorCollection;

    public function __construct(
        RequestStack $requestStack,
        DbAdapter $dbAdapter,
        DbAdapterInterface $listDbAdapter,
        StateFactoryInterface $stateFactory,
        ResultFactoryInterface $resultFactory,
        ActivePageServiceInterface $activePageService,
        CacheInterface $cache,
        StateRequestExtractorCollectionInterface $stateRequestExtractorCollection
    ) {
        $this->dbAdapter = $dbAdapter;
        $this->listDbAdapter = $listDbAdapter;
        $this->stateFactory = $stateFactory;
        $this->requestStack = $requestStack;
        $this->resultFactory = $resultFactory;
        $this->activePageService = $activePageService;
        $this->cache = $cache;
        $this->stateRequestExtractorCollection = $stateRequestExtractorCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleListQuery()
    {
        $config = $this->getListConfiguration();

        return $this->resultFactory->getFilterQuery($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getArticleListFilterRelevantState()
    {
        $state = $this->getArticleListState();

        $parameterIdentifier = $this->getArticleListSpotName();

        return $state->getStateAsUrlQueryArray($parameterIdentifier, [StateInterface::PAGE]);
    }

    private function getArticleListStateData(): array
    {
        $parameterIdentifier = $this->getArticleListSpotName();
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return [];
        }

        $data = array_merge_recursive($request->query->all(), $request->request->all());

        $listConfiguration = $this->getListConfiguration()->getAsArray();

        if (false === $listConfiguration) {
            return [];
        }

        return $this->stateRequestExtractorCollection->extract(
            $this->getListConfiguration()->getAsArray(),
            $data,
            $parameterIdentifier
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getListConfiguration()
    {
        if (null !== $this->listModuleConfig) {
            return $this->listModuleConfig;
        }

        $activePageId = $this->getActivePageId();
        $cacheKey = $this->cache->getKey([
            'class' => __CLASS__,
            'method' => 'getListConfiguration',
            'page' => $activePageId,
        ], false);
        $instance = $this->cache->get($cacheKey);
        if (null === $instance) {
            $listInstanceId = $this->dbAdapter->getFilterableListInstanceIdOnPage($activePageId);
            $this->listModuleConfig = $this->listDbAdapter->getConfigurationFromInstanceId($listInstanceId);

            $this->cache->set($cacheKey, $this->listModuleConfig, [
                    ['table' => 'shop_module_article_list', 'id' => null],
                    ['table' => 'cms_tpl_page_cms_master_pagedef_spot', 'id' => null],
                ]
            );
        } else {
            $this->listModuleConfig = $instance;
        }

        return $this->listModuleConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function allowCache()
    {
        return $this->resultFactory->_AllowCache($this->getListConfiguration());
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheParameter()
    {
        return $this->resultFactory->_GetCacheParameters($this->getListConfiguration(), $this->getArticleListState());
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheTrigger()
    {
        return $this->resultFactory->_GetCacheTableInfos($this->getListConfiguration());
    }

    private function getActivePageId(): ?string
    {
        return $this->activePageService->getActivePage()?->id;
    }

    /**
     * @return StateInterface
     */
    public function getArticleListState()
    {
        if (null !== $this->articleListState) {
            return $this->articleListState;
        }
        $userData = $this->getArticleListStateData();

        $this->articleListState = $this->stateFactory->createState($userData);

        return $this->articleListState;
    }

    private function getArticleListSpotName(): ?string
    {
        if (null !== $this->articleListSpotName) {
            return $this->articleListSpotName;
        }
        $this->articleListSpotName = $this->dbAdapter->getFilterableListInstanceSpotOnPage($this->getActivePageId());

        return $this->articleListSpotName;
    }

    public function getResultFactory(): ResultFactoryInterface
    {
        return $this->resultFactory;
    }
}
