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
     * @var TdbPkgShopRatingServiceTeaserCnf|null
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
     * Select one (random) rating item.
     *
     * @return TdbPkgShopRatingServiceRating|null
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
     * @return TdbPkgShopRatingServiceTeaserCnf|null
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
