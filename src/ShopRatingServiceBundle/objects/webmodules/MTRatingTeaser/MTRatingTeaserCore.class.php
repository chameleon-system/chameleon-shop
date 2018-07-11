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
 *  Module class for RatingTeaser-Module.
 *
/**/
class MTRatingTeaserCore extends TUserCustomModelBase
{
    /**
     * @var null|TdbPkgShopRatingServiceTeaserCnf
     */
    protected $oModuleConfig = null;

    public function &Execute()
    {
        parent::Execute();
        $this->data['oModuleConfig'] = $this->GetModuleConfig();
        $oItem = $this->GetRatingItem();
        if ($oItem) {
            $this->data['sRatingItemContent'] = $oItem->Render();
        } else {
            $this->data['sRatingItemContent'] = '';
        }

        return $this->data;
    }

    /**
     * @param int $iCountMax
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function SetNumberOfHintsToCache($iCountMax)
    {
    }

    /**
     * return number of records from which to choose the random teaser from cache
     * returns false if the data is not in cache.
     *
     * @return int|bool
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetNumberOfHitsFromCache()
    {
        return false;
    }

    /**
     * @param string $sCachedItem
     * @param int    $iItemPosition
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function SetCachedItemForPosition($sCachedItem, $iItemPosition)
    {
    }

    /**
     * Get trigger to reset cache.
     *
     * @return array
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetMainTrigger()
    {
        return array(
            array(
                'table' => 'pkg_shop_rating_sevice_rating',
                'id' => '',
            ),
            array(
                'table' => 'pkg_shop_rating_service_teaser_cnf',
                'id' => $this->oModuleConfig->id,
            ),
        );
    }

    /**
     * @param int $iItemPosition
     *
     * @return bool
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetCachedItemForPosition($iItemPosition)
    {
        return false;
    }

    /**
     * Get cache-key for max hint value.
     *
     * @return string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetCacheKeyForMaxHints()
    {
        $aKey = array('class' => __CLASS__, 'valueName' => 'NumberOfHitsFromCache');

        return TCacheManager::GetKey($aKey);
    }

    /**
     * Get cache-key for item position.
     *
     * @param $iItemPosition
     *
     * @return string
     *
     * @deprecated since 6.2.0 - no longer used.
     */
    protected function GetCacheKeyForPosition($iItemPosition)
    {
        $aKey = array('class' => __CLASS__, 'valueName' => 'Item', 'position' => $iItemPosition);

        return TCacheManager::GetKey($aKey);
    }

    /**
     * Select one (random) rating item.
     *
     * @return null|TdbPkgShopRatingServiceRating
     */
    protected function GetRatingItem()
    {
        $iMaxItems = null;
        $iSelectLimit = 0;
        $aItemCache = array();
        $oRatingItem = null;
        if (is_numeric($this->oModuleConfig->fieldNumberOfRatingsToSelectFrom)) {
            $iSelectLimit = $this->oModuleConfig->fieldNumberOfRatingsToSelectFrom;
        }
        $sQueryRatings = '';
        $oRatingServiceList = $this->oModuleConfig->GetMLT('pkg_shop_rating_service_mlt'); //TdbPkgShopRatingServiceList::GetList();

        $oRatingServiceList->AddFilterString("`active` = '1'");
        while ($oServiceItem = $oRatingServiceList->Next()) {
            if (!empty($sQueryRatings)) {
                $sQueryRatings .= "\nUNION\n";
            }
            $sQueryRatings .= "(SELECT * FROM pkg_shop_rating_service_rating WHERE pkg_shop_rating_service_id = '".MySqlLegacySupport::getInstance()->real_escape_string($oServiceItem->id)."'";
            if ($iSelectLimit > 0) {
                $sQueryRatings .= ' LIMIT '.MySqlLegacySupport::getInstance()->real_escape_string($iSelectLimit).')';
            }
        }

        $sQueryRatings = "SELECT T.* FROM ({$sQueryRatings}) AS T";
        // $sQueryRatings should look like:
        // (SELECT * FROM pkg_shop_rating_service_rating WHERE pkg_shop_rating_service_id = '45577194-590f-022b-d7bc-0e4560f44ade' LIMIT 4)
        // UNION
        // (select * from pkg_shop_rating_service_rating where pkg_shop_rating_service_id = '976d4d26-a62f-884c-96a2-f2ab584e6c9a' LIMIT 4)

        $oRatingsList = TdbPkgShopRatingServiceRatingList::GetList($sQueryRatings);
        while ($oRating = $oRatingsList->Next()) {
            $aItemCache[] = $oRating;
        }

        $iMaxItems = count($aItemCache);
        if ($iMaxItems > 0) {
            $iSelectedItem = rand(0, $iMaxItems - 1);
            $oRatingItem = $aItemCache[$iSelectedItem];
        } else {
            $oRatingItem = null;
        }

        return $oRatingItem;
    }

    /**
     * loads config for instance.
     *
     * @return null|TdbPkgShopRatingServiceTeaserCnf
     */
    protected function GetModuleConfig()
    {
        $this->oModuleConfig = TdbPkgShopRatingServiceTeaserCnf::GetNewInstance();
        if (!$this->oModuleConfig->LoadFromField('cms_tpl_module_instance_id', $this->instanceID)) {
            $this->oModuleConfig = null;
        }

        return $this->oModuleConfig;
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

        $aTrigger[] = array('table' => 'pkg_shop_rating_service_teaser_config', 'id' => $this->oModuleConfig->id);

        return $aTrigger;
    }
}
