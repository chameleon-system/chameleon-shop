<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\pkgshoplistfilter\Interfaces\FilterApiInterface;

class MTPkgShopListfilterCore extends TUserCustomModelBase
{
    /**
     * @var FilterApiInterface
     */
    private $filterApi;

    protected $bAllowHTMLDivWrapping = true;

    public function __construct()
    {
        parent::__construct();
        $this->filterApi = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_shop_list_filter.filter_api');
    }

    public function Init()
    {
        parent::Init();
        //we need to call this here because function could be load static filter (configured in module config) data to post data
        TdbPkgShopListfilter::GetActiveInstance();
    }

    public function &Execute()
    {
        parent::Execute();
        $this->data['oFilter'] = TdbPkgShopListfilter::GetActiveInstance();

        return $this->data;
    }

    /**
     * if this function returns true, then the result of the execute function will be cached.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return $this->filterApi->allowCache();
    }

    /**
     * return an assoc array of parameters that describe the state of the module.
     *
     * @return array
     */
    public function _GetCacheParameters()
    {
        $parameters = parent::_GetCacheParameters();
        $listState = $this->filterApi->getCacheParameter();
        $listState['is_filter_module'] = true;

        return array_merge_recursive($parameters, $listState);
    }

    /**
     * if the content that is to be cached comes from the database (as ist most often the case)
     * then this function should return an array of assoc arrays that point to the
     * tables and records that are associated with the content. one table entry has
     * two fields:
     *   - table - the name of the table
     *   - id    - the record in question. if this is empty, then any record change in that
     *             table will result in a cache clear.
     *
     * @return array
     */
    public function _GetCacheTableInfos()
    {
        $aTrigger = parent::_GetCacheTableInfos();

        if (!is_array($aTrigger)) {
            $aTrigger = array();
        }
        $aTrigger[] = array('table' => 'pkg_shop_listfilter', 'id' => null);
        $listTrigger = $this->filterApi->getCacheTrigger();

        return array_merge($aTrigger, $listTrigger);
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aAdditionalIncludes = $this->getResourcesForSnippetPackage('pkgShopListFilter');
        $aIncludes = array_merge($aIncludes, $aAdditionalIncludes);

        return $aIncludes;
    }
}
