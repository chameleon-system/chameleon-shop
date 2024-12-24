<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopBundle\objects\ArticleList;

use ChameleonSystem\CoreBundle\MapperLoader\MapperLoaderInterface;
use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\ConfigurationInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\DatabaseAccessLayer\Interfaces\DbAdapterInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultDataInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\ResultFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateFactoryInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\Interfaces\StateInterface;
use ChameleonSystem\ShopBundle\objects\ArticleList\State\StateElementPageSize;
use ChameleonSystem\ShopBundle\objects\ArticleList\StateRequestExtractor\Interfaces\StateRequestExtractorCollectionInterface;
use esono\pkgCmsCache\CacheInterface;
use IMapperCacheTriggerRestricted;
use IMapperVisitorRestricted;
use MTPkgViewRendererAbstractModuleMapper;
use MTShopArticleListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use TdbCmsLocals;
use TdbPkgShopCurrency;
use TdbShop;
use TTools;
use ViewRenderer;

class Module extends MTPkgViewRendererAbstractModuleMapper
{
    /**
     * @var StateFactoryInterface
     */
    private $stateFactory;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var State
     */
    private $state;
    /**
     * @var DbAdapterInterface
     */
    private $dbAdapter;
    /**
     * @var ConfigurationInterface
     */
    private $configuration;
    /**
     * @var bool
     */
    private $preventCaching = false;
    /**
     * @var ResultFactoryInterface
     */
    private $resultFactory;
    /**
     * @var StateRequestExtractorCollectionInterface
     */
    private $requestExtractorCollection;
    /**
     * @var ActivePageServiceInterface
     */
    private $activePageService;
    /**
     * @var ViewRenderer
     */
    private $viewRenderer;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * Mapping of viewname => twig template path
     * @var array<string, string>
     */
    private $viewToListViewMapping;
    /**
     * @var StateElementPageSize
     */
    private $stateElementPageSize;
    /**
     * @var array
     */
    private $validPageSizes;
    /**
     * @var UrlUtil
     */
    private $urlUtil;
    /**
     * @var MapperLoaderInterface
     */
    private $mapperLoader;
    /**
     * @var ResultDataInterface
     */
    private $moduleListResult;

    /**
     * @param array<string, string>                    $viewToListViewMapping - Mapping of template name to twig template path
     * @param RequestStack                             $requestStack
     * @param StateFactoryInterface                    $stateFactory
     * @param DbAdapterInterface                       $dbAdapter
     * @param ResultFactoryInterface                   $resultFactory
     * @param StateRequestExtractorCollectionInterface $requestExtractorCollection
     * @param ActivePageServiceInterface               $activePageService
     * @param ViewRenderer                             $viewRenderer
     * @param CacheInterface                           $cache
     * @param StateElementPageSize                     $stateElementPageSize
     * @param array                                    $validPageSizes
     * @param UrlUtil                                  $urlUtil
     * @param MapperLoaderInterface                    $mapperLoader
     */
    public function __construct(
        array $viewToListViewMapping,
        RequestStack $requestStack,
        StateFactoryInterface $stateFactory,
        DbAdapterInterface $dbAdapter,
        ResultFactoryInterface $resultFactory,
        StateRequestExtractorCollectionInterface $requestExtractorCollection,
        ActivePageServiceInterface $activePageService,
        ViewRenderer $viewRenderer,
        CacheInterface $cache,
        StateElementPageSize $stateElementPageSize,
        array $validPageSizes,
        UrlUtil $urlUtil,
        MapperLoaderInterface $mapperLoader
    ) {
        parent::__construct();
        $this->requestStack = $requestStack;
        $this->stateFactory = $stateFactory;
        $this->dbAdapter = $dbAdapter;
        $this->resultFactory = $resultFactory;

        $this->requestExtractorCollection = $requestExtractorCollection;
        $this->activePageService = $activePageService;
        $this->viewRenderer = $viewRenderer;
        $this->cache = $cache;
        $this->viewToListViewMapping = $viewToListViewMapping;
        $this->stateElementPageSize = $stateElementPageSize;
        $this->validPageSizes = $validPageSizes;
        $this->urlUtil = $urlUtil;
        $this->mapperLoader = $mapperLoader;
    }

    /**
     * @return Request
     * @psalm-suppress NullableReturnStatement, InvalidNullableReturnType - We know that a request exists here
     */
    protected function getCurrentRequest()
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @return DbAdapterInterface
     */
    protected function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function Init(): void
    {
        parent::Init();
        $this->loadConfiguration(); // need to load config in init because this may affect what get/post parameter are relevant for the state (and thus for caching).
        $this->initializeListState();
        $this->resultFactory->moduleInitHook($this->configuration);
        $this->initProductListResult();
    }

    /**
     * @return void
     */
    private function initProductListResult()
    {
        if (null === $this->moduleListResult) {
            $enrichedState = $this->enrichStateWithDefaultsFromConfiguration();
            $this->moduleListResult = $this->getResults($enrichedState);
        }
    }

    /**
     * @return void
     */
    private function initializeListState()
    {
        $stateData = $this->getStateDataFromRequest($this->getCurrentRequest());
        $stateData = $this->makePageSizeValid($stateData);
        $this->state = $this->stateFactory->createState($stateData);
    }

    /**
     * @param array $stateData
     *
     * @return array
     */
    private function makePageSizeValid(array $stateData): array
    {
        $pageSizeKey = $this->stateElementPageSize->getKey();
        if (!isset($stateData[$pageSizeKey])) {
            return $stateData;
        }
        if (!$this->pageSizeIsValid($stateData[$pageSizeKey])) {
            $stateData[$pageSizeKey] = $this->configuration->getDefaultPageSize();
        }

        return $stateData;
    }

    /**
     * @param string $requestedPageSize
     *
     * @return bool
     */
    private function pageSizeIsValid($requestedPageSize)
    {
        return $requestedPageSize === $this->configuration->getDefaultPageSize()
            || in_array($requestedPageSize, $this->validPageSizes);
    }

    /**
     * {@inheritdoc}
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ): void {
        $enrichedState = $this->enrichStateWithDefaultsFromConfiguration();
        $results = $this->moduleListResult;
        if (null === $results) {
            $results = $this->getResults($enrichedState);
        }
        $template = $this->getListTemplateFromConfigName($this->aModuleConfig['view']);

        $oVisitor->SetMappedValue('articleList', $this->renderProducts($template, $results, $enrichedState));
        $oVisitor->SetMappedValue('aArticleList', $results->asArray());
        $oVisitor->SetMappedValue('activePageNumber', $results->getPage());
        $oVisitor->SetMappedValue('listPagerUrl', $this->getListPageUrl());
        $listPageUrl = $this->getListPageUrl();
        $listPageUrlParams = array(
            'module_fnc' => array(
                $this->sModuleSpotName => 'ExecuteAjaxCall',
            ),
            '_fnc' => 'getRenderedList',
        );
        $oVisitor->SetMappedValue('listPagerUrlAjax', $this->urlUtil->getArrayAsUrl($listPageUrlParams, $listPageUrl.'&', '&'));
        $oVisitor->SetMappedValue('numberOfPages', $results->getNumberOfPages());
        $oVisitor->SetMappedValue('state', $enrichedState->getStateArrayWithoutQueryParameter());
        $oVisitor->SetMappedValue('stateObject', $enrichedState);
        $oVisitor->SetMappedValue('listConfiguration', $this->getConfiguration());
        $oVisitor->SetMappedValue('sModuleSpotName', $this->sModuleSpotName);
        $oVisitor->SetMappedValue('local', $this->getActiveLocal());
        $oVisitor->SetMappedValue('currency', $this->getActiveCurrency());
        $oVisitor->SetMappedValue('shop', $this->getActiveShop());
        $oVisitor->SetMappedValueFromArray($this->getListInformationFromConfiguration());

        if ($bCachingEnabled) {
            $this->configureCacheTrigger($oCacheTriggerManager);
        }
    }

    /**
     * @return ConfigurationInterface
     */
    protected function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return void
     */
    private function loadConfiguration()
    {
        $this->configuration = $this->dbAdapter->getConfigurationFromInstanceId($this->instanceID);
    }

    /**
     * @return StateInterface
     */
    protected function enrichStateWithDefaultsFromConfiguration()
    {
        return $this->stateFactory->createStateEnrichedWithDefaults($this->state, $this->configuration);
    }

    /**
     * @return string
     */
    protected function getListPageUrl()
    {
        $stateData = $this->state->getStateAsUrlQueryArray($this->sModuleSpotName, array(StateInterface::PAGE));
        $stateData[$this->sModuleSpotName][StateInterface::PAGE] = '_pageNumber_';

        return $this->getActivePageUrl($stateData);
    }

    /**
     * @return string
     */
    protected function getListPageSizeUrl()
    {
        $stateData = $this->state->getStateAsUrlQueryArray($this->sModuleSpotName, array(StateInterface::PAGE_SIZE, StateInterface::PAGE));
        $stateData[$this->sModuleSpotName][StateInterface::PAGE_SIZE] = '_pageSize_';
        $stateData[$this->sModuleSpotName][StateInterface::PAGE] = 0; //we want to go to first page when page size is changed

        return $this->getActivePageUrl($stateData);
    }

    /**
     * @return string
     */
    protected function getListStateUrl()
    {
        $stateData = $this->state->getStateAsUrlQueryArray($this->sModuleSpotName);

        return $this->getActivePageUrl($stateData);
    }

    /**
     * @param array $additionalData
     *
     * @return string
     */
    private function getActivePageUrl($additionalData)
    {
        return str_replace('&amp;', '&', $this->activePageService->getActivePage()->GetRealURLPlain($additionalData));
    }

    /**
     * @return array
     */
    private function getSortList()
    {
        return $this->dbAdapter->getSortListForConfiguration($this->configuration->getId());
    }

    /**
     * @return string
     */
    private function getActiveSortId()
    {
        $activeSortId = $this->state->getState(StateInterface::SORT);
        if (null === $activeSortId) {
            $activeSortId = $this->configuration->getDefaultSortId();
        }

        return $activeSortId;
    }

    /**
     * @return string
     */
    private function getSortFieldName()
    {
        return "{$this->sModuleSpotName}[".StateInterface::SORT.']';
    }

    /**
     * @return string
     */
    private function getSortFormAction()
    {
        return '?';
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getStateDataFromRequest(Request $request)
    {
        $requestData = array_merge_recursive($request->query->all(), $request->request->all());

        $requestData = $this->requestExtractorCollection->extract($this->configuration->getAsArray(), $requestData, $this->sModuleSpotName);

        return $requestData;
    }

    /**
     * @return string
     */
    private function getSortFormStateInputFields()
    {
        $stateData = $this->state->getStateAsUrlQueryArray(
            $this->sModuleSpotName,
            array(StateInterface::SORT, StateInterface::PAGE)
        );
        if (0 === count($stateData)) {
            return '';
        }

        return TTools::GetArrayAsFormInput($stateData);
    }

    /**
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     *
     * @return void
     */
    protected function configureCacheTrigger(IMapperCacheTriggerRestricted $oCacheTriggerManager)
    {
        $filterTrigger = $this->resultFactory->_GetCacheTableInfos($this->configuration);

        foreach ($filterTrigger as $trigger) {
            $id = (isset($trigger['id']) && null !== isset($trigger['id']) && '' !== isset($trigger['id'])) ? $trigger['id'] : null;
            $oCacheTriggerManager->addTrigger($trigger['table'], $id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function _GetCacheParameters(): array
    {
        $cacheParameter = parent::_GetCacheParameters();
        $cacheParameter['list_class'] = 'ChameleonSystem\ShopBundle\objects\ArticleList\Module';

        $resultParameters = $this->resultFactory->_GetCacheParameters($this->configuration, $this->state);
        $cacheParameter = array_merge_recursive($cacheParameter, $resultParameters);

        return $cacheParameter;
    }

    /**
     * @return TdbShop
     */
    private function getActiveShop()
    {
        return TdbShop::GetInstance();
    }

    /**
     * @return TdbCmsLocals
     * @psalm-suppress FalsableReturnStatement - `GetActive` only returns `false` during the bootstrapping phase, which is not the case here
     */
    private function getActiveLocal()
    {
        return TdbCmsLocals::GetActive();
    }

    /**
     * @return TdbPkgShopCurrency|false
     */
    private function getActiveCurrency()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_currency.shop_currency')->getObject();
    }

    /**
     * call this if you want to prevent the result from being written to cache.
     *
     * @return void
     */
    private function preventCachingOfResult()
    {
        $this->preventCaching = true;
    }

    /**
     * {@inheritdoc}
     */
    public function _AllowCache(): bool
    {
        if (true === $this->preventCaching) {
            return false;
        }

        if (false === $this->resultFactory->_AllowCache($this->configuration)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getListInformationFromConfiguration()
    {
        $configDbObject = $this->configuration->getDatabaseObject();
        $icon = $configDbObject->GetImage(0, 'icon');
        $data = array(
            'listTitle' => $configDbObject->fieldName,
            'description_start' => $configDbObject->GetTextField('description_start'),
            'description_end' => $configDbObject->GetTextField('description_end'),
        );
        if ($icon) {
            $data['listIcon'] = array(
                'id' => $icon->id,
                'description' => $icon->aData['description'],
            );
        }

        return $data;
    }

    /**
     * @return ResultDataInterface
     */
    protected function getResults(StateInterface $enrichedState)
    {
        $expectedPage = (int) $enrichedState->getState(StateInterface::PAGE);
        $results = $this->resultFactory->createResult($this->configuration, $enrichedState);
        if ($results->getPage() !== $expectedPage) {
            $this->state->setState(StateInterface::PAGE, 0);
            $this->preventCachingOfResult();

            return $results; // to prevent cache flooding attack we need to prevent this result set from making it into cache
        }

        return $results;
    }

    /**
     * @return array
     */
    protected function getItemMapperBaseData()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'getRenderedList';
    }

    /**
     * returns a fully rendered list - used to page via ajax.
     *
     * @return MTShopArticleListResponse
     */
    protected function getRenderedList()
    {
        $enrichedState = $this->enrichStateWithDefaultsFromConfiguration();
        $results = $this->getResults($enrichedState);

        $oResponse = new MTShopArticleListResponse();
        $oResponse->bHasNextPage = ($results->getNumberOfPages() > $results->getPage() + 1);
        $oResponse->bHasPreviousPage = ($results->getPage() > 0);
        $oResponse->iListKey = $this->sModuleSpotName;
        $oResponse->iNumberOfResults = $results->getTotalNumberOfResults();

        $template = $this->getListTemplateFromConfigName($this->aModuleConfig['view']);
        $oResponse->sItemPage = $this->renderProducts($template);

        return $oResponse;
    }

    /**
     * renders product list using the view provided. this should really be in another controller,
     * unfortunately Chameleon currently does not support calling other controllers the way symfony does.
     *
     * @param string              $viewName
     * @param ResultDataInterface $results
     * @param StateInterface      $enrichedState
     *
     * @return string
     */
    protected function renderProducts($viewName, ResultDataInterface $results = null, StateInterface $enrichedState = null)
    {
        // need to cache this
        $cacheKey = null;
        if ($this->_AllowCache()) {
            $cacheKeyData = $this->_GetCacheParameters();
            $cacheKeyData['is_article_list_content'] = true;
            $cacheKey = $this->cache->getKey($cacheKeyData);
            $resultHTML = $this->cache->get($cacheKey);
            if (null !== $resultHTML) {
                return $resultHTML;
            }
        }

        if (null === $enrichedState) {
            $enrichedState = $this->enrichStateWithDefaultsFromConfiguration();
        }

        if (null === $results) {
            $results = $this->getResults($enrichedState);
        }

        $items = $results->asArray();

        $inputData = array(
            'activePageNumber' => $results->getPage(),
            'itemMapperBaseData' => $this->getItemMapperBaseData(),
            'items' => $items,
            'results' => $results,
            'listPagerUrl' => $this->getListPageUrl(),
            'listPageSizeChangeUrl' => $this->getListPageSizeUrl(),
            'numberOfPages' => $results->getNumberOfPages(),
            'state' => $enrichedState->getStateArrayWithoutQueryParameter(),
            'stateObject' => $enrichedState,
            'sortList' => $this->getSortList(),
            'sortFieldName' => $this->getSortFieldName(),
            'sortFormAction' => $this->getSortFormAction(),
            'sortFormStateInputFields' => $this->getSortFormStateInputFields(),
            'activeSortId' => $this->getActiveSortId(),
            'listConfiguration' => $this->getConfiguration(),
            'sModuleSpotName' => $this->sModuleSpotName,
            'local' => $this->getActiveLocal(),
            'currency' => $this->getActiveCurrency(),
            'shop' => $this->getActiveShop(),
        );

        $inputData = array_merge($inputData, $this->getListInformationFromConfiguration());

        $resultHTML = $this->render($viewName, $inputData);
        if ($this->_AllowCache()) {
            $cacheTrigger = new \MapperCacheTrigger();
            $cacheTriggerRestricted = new \MapperCacheTriggerRestrictedProxy($cacheTrigger);
            $this->configureCacheTrigger($cacheTriggerRestricted);
            $trigger = $cacheTrigger->getTrigger();
            $this->cache->set($cacheKey, $resultHTML, $trigger);
        }

        return $resultHTML;
    }

    /**
     * @param string $viewName
     * @param array  $inputData
     *
     * @return string
     */
    private function render($viewName, $inputData)
    {
        $module = $this->aModuleConfig['model'];
        $moduleConfig = $this->getModuleObject($module, 'classname');

        $chainConfig = $moduleConfig->getMapperChains();
        $mappers = array();
        if (isset($chainConfig[$this->aModuleConfig['view']])) {
            foreach ($chainConfig[$this->aModuleConfig['view']] as $mapper) {
                $mappers[] = $this->mapperLoader->getMapper($mapper);
            }
        }

        $mapperChains = $moduleConfig->getMapperChains();

        $this->viewRenderer->AddSourceObjectsFromArray($inputData);
        foreach ($mapperChains as $name => $chainMappers) {
            $this->viewRenderer->addMapperChain($name, $chainMappers);
        }

        $this->viewRenderer->AddMappers($mappers);

        return $this->viewRenderer->Render($viewName);
    }

    /**
     * @param string $viewName
     *
     * @return string
     */
    private function getListTemplateFromConfigName($viewName)
    {
        if (isset($this->viewToListViewMapping[$viewName])) {
            return $this->viewToListViewMapping[$viewName];
        }

        return current($this->viewToListViewMapping);
    }
}
