<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *  Module class for RatingServiceWidget.
 *
/**/
class MTRatingServiceWidgetCore extends TUserCustomModelBase
{
    /**
     * @var TdbPkgShopRatingServiceWidgetConfig|null
     */
    protected $oModuleConfig = null;

    public function Execute()
    {
        parent::Execute();
        $this->data['oModuleConfig'] = $this->GetModuleConfig();
        $this->data['oRatingService'] = $this->GetRatingService();

        return $this->data;
    }

    /**
     * Select right RatingService object.
     *
     * @return TdbPkgShopRatingService|null
     */
    protected function GetRatingService()
    {
        $oRatingServiceItem = null;
        if (!empty($this->oModuleConfig->fieldPkgShopRatingServiceId)) {
            $oRatingServiceItem = TdbPkgShopRatingService::GetNewInstance($this->oModuleConfig->fieldPkgShopRatingServiceId);
        }

        return $oRatingServiceItem;
    }

    /**
     * loads config for instance.
     *
     * @return TdbPkgShopRatingServiceWidgetConfig|null
     */
    protected function GetModuleConfig()
    {
        $this->oModuleConfig = TdbPkgShopRatingServiceWidgetConfig::GetNewInstance();
        if (!$this->oModuleConfig->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
            $this->oModuleConfig = null;
        }

        return $this->oModuleConfig;
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        //if (!is_array($aIncludes)) $aIncludes = array();
        //$aIncludes[] = "<link type='text/css' rel='stylesheet' href='/assets/css/PkgShopRatingServiceWidget.css' />";
        //$aIncludes[] = "<script type='text/javascript' src='/assets/js/PkgShopRatingServiceWidget.js'></script>";
        return $aIncludes;
    }

    /**
     * if this function returns true, then the result of the execute function will be cached.
     *
     * @return bool
     */
    public function _AllowCache()
    {
        return false;
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

        $aTrigger[] = array('table' => 'pkg_shop_rating_service_widget_config', 'id' => $this->oModuleConfig->id);

        return $aTrigger;
    }
}
