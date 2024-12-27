<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\ActivePageServiceInterface;
use ChameleonSystem\pkgshoplistfilter\Interfaces\FilterApiInterface;
use esono\pkgCmsCache\CacheInterface;

if (!defined('PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM')) {
    define('PKG_SHOP_LISTFILTER_ENABLE_COUNT_PER_FILTER_ITEM', true);
}

class TPkgShopListfilter extends TPkgShopListfilterAutoParent
{
    const VIEW_PATH = 'pkgShopListfilter/views/db/TPkgShopListfilter';
    const URL_PARAMETER_IS_NEW_REQUEST = 'bFilterNewRequest';

    /**
     * @var null|array
     */
    private $staticFilter = null;

    /**
     * @var string
     */
    private $articleListQuery;

    /**
     * @var array
     */
    private $listState;

    /**
     * return the active instance based on the active category or shop config
     * Attention: If list filter has configured a static filter the static filter data was added to post data
     * within this function.
     *
     * @return TdbPkgShopListfilter|null
     */
    public static function GetActiveInstance()
    {
        static $oInstance = false;
        if (false === $oInstance) {
            /** @var $filterApi FilterApiInterface */
            $filterApi = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_list_filter.filter_api');
            $baseQuery = $filterApi->getArticleListQuery();

            $oInstance = self::getInstanceForPage(self::getMyActivePageService()->getActivePage(), $baseQuery);
            if (null === $oInstance) {
                return $oInstance;
            }
            $listState = $filterApi->getArticleListFilterRelevantState();
            $oInstance->setListState($listState);

            $oFilterList = $oInstance->GetFieldPkgShopListfilterItemList();
            $oFilterList->GoToStart();
            $aCloneList = array();
            while ($oFilterItem = $oFilterList->Next()) {
                $filterQueryRestriction = $oFilterList->GetQueryRestriction($oFilterItem);
                $sBaseQueryHash = 'h'.md5($filterQueryRestriction);
                if (false === isset($aCloneList[$sBaseQueryHash])) {
                    $articleList = TdbShopArticleList::GetList($baseQuery);
                    if ('' !== $filterQueryRestriction) {
                        $articleList->AddFilterString($filterQueryRestriction);
                    }
                    $aCloneList[$sBaseQueryHash] = $articleList;
                }
                $oFilterItem->SetFilteredItemList($aCloneList[$sBaseQueryHash]);
            }
        }

        return $oInstance;
    }

    /**
     * @param array $listState
     * @return void
     */
    public function setListState(array $listState)
    {
        $this->listState = $listState;
    }

    /**
     * @return TdbPkgShopListfilter|null
     */
    private static function getInstanceForCategory(TdbShopCategory $activeCategory, TdbShop $oShop)
    {
        $sPkgShopListfilterId = $activeCategory->GetFieldPkgShopListfilterIdRecursive();
        if (empty($sPkgShopListfilterId)) {
            if (!empty($oShop->fieldPkgShopListfilterCategoryFilterId)) {
                $sPkgShopListfilterId = $oShop->fieldPkgShopListfilterCategoryFilterId;
            }
        }

        if (!empty($sPkgShopListfilterId)) {
            return TdbPkgShopListfilter::GetNewInstance($sPkgShopListfilterId);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getListSate()
    {
        return $this->listState;
    }

    /**
     * @return string
     */
    public function getActiveFilterAsQueryString()
    {
        $oFilterList = $this->GetFieldPkgShopListfilterItemList();

        return $oFilterList->GetQueryRestriction();
    }

    /**
     * @param string $staticFilter
     *
     * @return void
     */
    private function setStaticFilter($staticFilter)
    {
        parse_str(urldecode($staticFilter), $this->staticFilter);
        if (is_array($this->staticFilter) && isset($this->staticFilter[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA])) {
            $this->staticFilter = $this->staticFilter[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA];
        } else {
            $this->staticFilter = null;
        }
    }

    /**
     * @return bool
     */
    public function isStaticFilter(TdbPkgShopListfilterItem $filter)
    {
        if (!is_array($this->staticFilter)) {
            return false;
        }
        if (isset($this->staticFilter[$filter->id])) {
            return true;
        }

        return false;
    }

    /**
     * @return array|null
     */
    protected function getStaticFilter()
    {
        return $this->staticFilter;
    }

    /**
     * @param TdbCmsTplPage $page
     * @param string        $baseQuery
     *
     * @return TdbPkgShopListfilter|null
     */
    private static function getInstanceForPage(TdbCmsTplPage $page, $baseQuery)
    {
        /** @var CacheInterface $cache */
        $cache = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.cache');

        $cacheKey = $cache->getKey(array(
            'class' => __CLASS__,
            'method' => 'getInstanceForPage',
            'pageId' => $page->id,
        ));
        $instance = $cache->get($cacheKey);
        if (null === $instance) {
            $instance = false;

            $query = "SELECT pkg_shop_listfilter_module_config.*
                    FROM cms_tpl_module_instance
              INNER JOIN cms_tpl_module ON cms_tpl_module_instance.cms_tpl_module_id = cms_tpl_module.id
              INNER JOIN cms_tpl_page_cms_master_pagedef_spot ON cms_tpl_module_instance.id = cms_tpl_page_cms_master_pagedef_spot.cms_tpl_module_instance_id
              INNER JOIN pkg_shop_listfilter_module_config ON cms_tpl_module_instance.id = pkg_shop_listfilter_module_config.cms_tpl_module_instance_id
                   WHERE cms_tpl_page_cms_master_pagedef_spot.cms_tpl_page_id = '{$page->id}'
                     AND pkg_shop_listfilter_module_config.pkg_shop_listfilter_id != ''

        ";

            // we are looking for a module that has a listfilter set. in the future we might change this to a more robust way of identifying the right module. (eg. via generic meta data)
            $filterConfig = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query));
            if ($filterConfig) {
                /** @var self $instance */
                $instance = TdbPkgShopListfilter::GetNewInstance($filterConfig['pkg_shop_listfilter_id']);
                if ('' !== $filterConfig['filter_parameter']) {
                    $instance->setStaticFilter($filterConfig['filter_parameter']);
                }
            }

            $cache->set($cacheKey, $instance, array(array('table' => 'shop', 'id' => null), array('table' => 'cms_tpl_page', 'id' => $page->id), array('table' => 'pkg_shop_listfilter', 'id' => null)));
        }

        if (false === $instance) { // check if the category sets a filter
            $instance = null;
            /** @var \ChameleonSystem\ShopBundle\Interfaces\ShopServiceInterface $shopService */
            $shopService = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service');
            $activeCategory = $shopService->getActiveCategory();
            if (null !== $activeCategory) {
                $instance = self::getInstanceForCategory($activeCategory, $shopService->getActiveShop());
            }
        }

        if (null === $instance) {
            $instance = TdbPkgShopListfilter::GetNewInstance(\ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop.shop_service')->getActiveShop()->fieldPkgShopListfilterPostsearchId);
        }

        $instance->pushStaticFilterToRequest();

        $instance->setArticleListQuery($baseQuery);

        return $instance;
    }

    /**
     * @param string $articleListQuery
     * @return void
     */
    public function setArticleListQuery($articleListQuery)
    {
        $this->articleListQuery = $articleListQuery;
    }

    /**
     * render the filter.
     *
     * @param string $sViewName     - name of the view
     * @param string $sViewType     - where to look for the view
     * @param array  $aCallTimeVars - optional parameters to pass to render method
     *
     * @return string
     */
    public function Render($sViewName = 'standard', $sViewType = 'Customer', $aCallTimeVars = array())
    {
        $oView = new TViewParser();
        /** @var $oView TViewParser */
        $oView->AddVar('oListfilter', $this);
        $oView->AddVar('aCallTimeVars', $aCallTimeVars);
        $aOtherParameters = $this->GetAdditionalViewVariables($sViewName, $sViewType);
        $oView->AddVarArray($aOtherParameters);

        return $oView->RenderObjectPackageView($sViewName, TdbPkgShopListfilter::VIEW_PATH, $sViewType);
    }

    /**
     * use this method to add any variables to the render method that you may
     * require for some view.
     *
     * @param string $sViewName - the view being requested
     * @param string $sViewType - the location of the view (Core, Custom-Core, Customer)
     *
     * @return array
     */
    protected function GetAdditionalViewVariables($sViewName, $sViewType)
    {
        $aViewVariables = array();

        return $aViewVariables;
    }

    /**
     * Listenfilter Einträge.
     *
     * @return TdbPkgShopListfilterItemList
     */
    public function GetFieldPkgShopListfilterItemList()
    {
        $oList = $this->GetFromInternalCache('FieldPkgShopListfilterItemList');
        if (is_null($oList)) {
            $aTrigger = array('class' => __CLASS__, 'method' => 'GetFieldPkgShopListfilterItemList', 'id' => $this->id);
            $aTrigger['activeFilter'] = TGlobal::instance()->GetUserData(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA);
            $bCaching = false;
            if (!is_array($aTrigger['activeFilter']) || 0 == count($aTrigger['activeFilter'])) {
                $bCaching = true;
            }
            $oList = null;
            $sKey = null;
            if ($bCaching) {
                /** @var CacheInterface $cache */
                $cache = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.cache');
                $sKey = $cache->GetKey($aTrigger);
                $oList = $cache->get($sKey);
            }
            if (null === $oList) {
                $oList = TdbPkgShopListfilterItemList::GetListForPkgShopListfilterId($this->id, $this->iLanguageId);
                $oList->bAllowItemCache = true;
                $i = 0;
                while ($oItem = $oList->Next()) {
                    ++$i;
                }
                $oList->GoToStart();
                if ($bCaching) {
                    $aCacheTrigger = array(array('table' => 'pkg_shop_listfilter_item', 'id' => null), array('table' => $this->table, 'id' => $this->id));
                    $cache->set($sKey, $oList, $aCacheTrigger);
                }
            }
            $this->SetInternalCache('FieldPkgShopListfilterItemList', $oList);
        }

        return $oList;
    }

    /**
     * return the current filter as a series of hidden input fields.
     *
     * @return string
     */
    public function GetCurrentFilterAsHiddenInputFields()
    {
        $oList = $this->GetFieldPkgShopListfilterItemList();

        return $oList->GetListSettingAsInputFields();
    }

    /**
     * return the current active filter as an array.
     *
     * @return array
     */
    public function GetCurrentFilterAsArray()
    {
        $aFilterAsArray = $this->GetFromInternalCache('aFilterAsArray');
        if (is_null($aFilterAsArray)) {
            $oList = $this->GetFieldPkgShopListfilterItemList();
            $aFilterAsArray = $oList->GetListSettingAsArray();
            $this->SetInternalCache('aFilterAsArray', $aFilterAsArray);
        }

        return $aFilterAsArray;
    }

    /**
     * return the filter values set for the filter with the given system name. return false if none are set.
     *
     * @param array $sFilterSystemName
     *
     * @return array|false
     */
    public function GetFilterValuesForFilterType($sFilterSystemName)
    {
        /** @var array|null $aActiveFilter */
        $aActiveFilter = $this->GetFromInternalCache('aFilterDataAsArrayFor'.$sFilterSystemName);

        if (is_null($aActiveFilter)) {
            $aActiveFilter = false;

            $aFilterData = $this->GetCurrentFilterAsArray();
            if (is_array($aFilterData) && array_key_exists(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA, $aFilterData) && is_array($aFilterData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA]) && count($aFilterData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA]) > 0) {
                $aFilterData = $aFilterData[TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA];
                $oFilterId = TdbPkgShopListfilterItem::GetNewInstance();
                if ($oFilterId->LoadFromFields(array('pkg_shop_listfilter_id' => $this->id, 'systemname' => $sFilterSystemName))) {
                    if (is_array($aFilterData) && array_key_exists($oFilterId->id, $aFilterData)) {
                        if (is_array($aFilterData[$oFilterId->id]) && count($aFilterData[$oFilterId->id]) > 0) {
                            $aActiveFilter = $aFilterData[$oFilterId->id];
                        }
                    }
                }
            }
            $this->SetInternalCache('aFilterDataAsArrayFor'.$sFilterSystemName, $aActiveFilter);
        }

        return $aActiveFilter;
    }

    /**
     * @return void
     */
    private function pushStaticFilterToRequest()
    {
        if (null === $this->staticFilter) {
            return;
        }

        $filterData = TGlobal::instance()->GetUserData(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA);
        if (!is_array($filterData)) {
            $filterData = array();
        }

        foreach ($this->staticFilter as $param => $val) {
            $filterData[$param] = $val;
        }
        TGlobal::instance()->SetUserData(TdbPkgShopListfilterItem::URL_PARAMETER_FILTER_DATA, $filterData);
    }

    /**
     * @return ActivePageServiceInterface
     */
    private static function getMyActivePageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.active_page_service');
    }
}
