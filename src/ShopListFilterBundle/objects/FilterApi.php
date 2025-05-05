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
    /**
     * @var DbAdapter
     */
    private $dbAdapter;
    /**
     * @var DbAdapterInterface
     */
    private $listDbAdapter;
    /**
     * @var StateFactoryInterface
     */
    private $stateFactory;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var ResultFactoryInterface
     */
    private $resultFactory;
    /**
     * @var ConfigurationInterface
     */
    private $listModuleConfig;
    /**
     * @var ActivePageServiceInterface
     */
    private $activePageService;
    /**
     * @var string|null
     */
    private $articleListSpotName;
    /**
     * @var StateInterface
     */
    private $articleListState;
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StateRequestExtractorCollectionInterface
     */
    private $stateRequestExtractorCollection;

    /**
     * @param RequestStack                             $requestStack
     * @param DbAdapter                                $dbAdapter
     * @param DbAdapterInterface                       $listDbAdapter
     * @param StateFactoryInterface                    $stateFactory
     * @param ResultFactoryInterface                   $resultFactory
     * @param ActivePageServiceInterface               $activePageService
     * @param CacheInterface                           $cache
     * @param StateRequestExtractorCollectionInterface $stateRequestExtractorCollection
     */
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

        return $state->getStateAsUrlQueryArray($parameterIdentifier, array(StateInterface::PAGE));
    }

    /**
     * @return array
     */
    private function getArticleListStateData()
    {
        $parameterIdentifier = $this->getArticleListSpotName();
        $request = $this->requestStack->getCurrentRequest();
        $data = array_merge_recursive($request->query->all(), $request->request->all());

        $stateData = $this->stateRequestExtractorCollection->extract(
            $this->getListConfiguration()->getAsArray(),
            $data,
            $parameterIdentifier
        );

        return $stateData;
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
        $cacheKey = $this->cache->getKey(array(
            'class' => __CLASS__,
            'method' => 'getListConfiguration',
            'page' => $activePageId,
        ), false);
        $instance = $this->cache->get($cacheKey);
        if (null === $instance) {
            $listInstanceId = $this->dbAdapter->getFilterableListInstanceIdOnPage($activePageId);
            $this->listModuleConfig = $this->listDbAdapter->getConfigurationFromInstanceId($listInstanceId);

            $this->cache->set($cacheKey, $this->listModuleConfig, array(
                    array('table' => 'shop_module_article_list', 'id' => null),
                    array('table' => 'cms_tpl_page_cms_master_pagedef_spot', 'id' => null),
                )
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

    /**
     * @return string|null
     *
     * @FIXME The result of `->getActivePage()` can be null, which means the property fetch on it may be null as well (currently yielding a warning, but may be a fatal error in future PHP Version?)
     */
    private function getActivePageId()
    {
        return $this->activePageService->getActivePage()->id;
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

    /**
     * @return string|null
     */
    private function getArticleListSpotName()
    {
        if (null !== $this->articleListSpotName) {
            return $this->articleListSpotName;
        }
        $this->articleListSpotName = $this->dbAdapter->getFilterableListInstanceSpotOnPage($this->getActivePageId());

        return $this->articleListSpotName;
    }

    /**
     * @return ResultFactoryInterface
     */
    public function getResultFactory()
    {
        return $this->resultFactory;
    }
}
