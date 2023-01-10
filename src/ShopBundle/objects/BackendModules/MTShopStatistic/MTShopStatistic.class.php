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
 * module is used to display and export sales stats for the shop.
/**/
class MTShopStatistic extends TCMSModelBase
{
    /** @var string */
    protected $sStartDate = null;

    /** @var string */
    protected $sEndDate = null;

    /** @var string */
    protected $sDateGroupType = 'day';

    /** @var bool|'0'|'1' */
    protected $bShowChange = false;

    /** @var string */
    protected $sViewName = 'html.table';

    /** @var string[] */
    protected $portalList = array();

    /** @var string */
    protected $selectedPortalId = '';

    const SEPARATOR = ';';

    public function Init()
    {
        parent::Init();
        $this->sStartDate = $this->GetUserInput('sStartDate', date('1.m.Y'));
        $this->sEndDate = $this->GetUserInput('sEndDate', date('d.m.Y'));
        $this->sDateGroupType = $this->GetUserInput('sDateGroupType', 'day');
        /**
         * @FIXME `bShowChange` is probably meant to be a bool field. However, `GetUserInput` writes strings (probably '1' & '0')
         * @psalm-suppress InvalidPropertyAssignmentValue
         */
        $this->bShowChange = $this->GetUserInput('bShowChange', '0');

        $this->sViewName = $this->GetUserInput('sViewName', 'html.table');
        $this->selectedPortalId = $this->GetUserInput('portalId', '');
        $this->portalList = $this->getPortalList();
    }

    public function Execute()
    {
        parent::Execute();

        $this->data['oStats'] = $this->GetStats();
        //print_r($this->data['aStats']);
        $this->data['sStartDate'] = $this->sStartDate;
        $this->data['sEndDate'] = $this->sEndDate;
        $this->data['sDateGroupType'] = $this->sDateGroupType;
        $this->data['bShowChange'] = $this->bShowChange;
        $this->data['sViewName'] = $this->sViewName;
        $this->data['portalList'] = $this->portalList;
        $this->data['selectedPortalId'] = $this->selectedPortalId;

        return $this->data;
    }

    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'GetAsCSV';
        $this->methodCallAllowed[] = 'DownloadTopsellers';
    }

    /**
     * @return array<string, string>
     */
    protected function getPortalList()
    {
        $portalIdList = array();
        $portalList = TdbCmsPortalList::GetList();
        while ($portal = $portalList->Next()) {
            $portalIdList[$portal->id] = $portal->GetName();
        }

        return $portalIdList;
    }

    /**
     * @return void
     */
    protected function GetProductStats()
    {
        $oLocal = TCMSLocal::GetActive();
        $query = 'SELECT SUM(`shop_order_item`.`order_amount`) AS totalordered,
                       SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                       shop_payment_method_name,
                       `shop_category`.`url_path` AS categorypath, `shop_order_item`.*
                  FROM `shop_order_item`
             LEFT JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
             LEFT JOIN `shop_article_shop_category_mlt` ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
             LEFT JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`';
        $aBaseCondition = array();
        if (!is_null($this->sStartDate)) {
            $aBaseCondition[] = "`shop_order`.`datecreated` >= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sStartDate))."'";
        }
        if (!is_null($this->sEndDate)) {
            $aBaseCondition[] = "`shop_order`.`datecreated` <= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sEndDate))." 23:59:59'";
        }
        if ('' != $this->selectedPortalId) {
            $aBaseCondition[] = "`shop_order`.`cms_portal_id` = '".\MySqlLegacySupport::getInstance()->real_escape_string($this->selectedPortalId)."'";
        }

        $query .= ' WHERE ('.implode(') AND (', $aBaseCondition).')';
        $query .= ' ORDER BY `shop_category`.`url_path` ';
        $oStats = new TCMSGroupedStatistics();
        if ($this->bShowChange) {
            $oStats->bShowDiffColumn = true;
        }
        $oStats = new TCMSGroupedStatistics();
        if ($this->bShowChange) {
            $oStats->bShowDiffColumn = true;
        }
    }

    /**
     * @return void
     */
    protected function DownloadTopsellers()
    {
        $oLocal = TCMSLocal::GetActive();
        $oOrderItems = $this->GetTopsellers();
        $oOrderItems->GoToStart();
        $aFields = array('articlenumber' => TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.field_article_number'), 'name' => TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.field_article_name'), 'totalordered' => TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.field_order_count'), 'totalorderedvalue' => TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.field_value'), 'categorypath' => TGlobal::Translate('chameleon_system_shop.cms_module_shop_statistic.field_category'));
        $aNumbers = array('totalordered' => 0, 'totalorderedvalue' => 2);
        $sData = '"'.implode('"'.self::SEPARATOR.'"', array_values($aFields)).'"'."\n";
        while ($oItem = $oOrderItems->Next()) {
            $aRow = array();
            foreach ($aFields as $sFieldName => $sFieldTitle) {
                if (array_key_exists($sFieldName, $aNumbers)) {
                    $oItem->sqlData[$sFieldName] = $oLocal->FormatNumber($oItem->sqlData[$sFieldName], $aNumbers[$sFieldName]);
                }
                $aRow[] = str_replace('"', "'", $oItem->sqlData[$sFieldName]);
            }
            $sData .= '"'.implode('"'.self::SEPARATOR.'"', array_values($aRow)).'"'."\n";
        }
        $sName = 'topseller-'.$this->sStartDate.'-'.$this->sEndDate.'.csv';
        $this->OutputAsDownload($sData, $sName);
    }

    /*
    * return the top seller
    * @return TdbShopOrderItemList
    */
    /**
     * @return TdbShopOrderItemList
     */
    protected function GetTopsellers()
    {
        $oLocal = TCMSLocal::GetActive();
        $query = 'SELECT SUM(`shop_order_item`.`order_amount`) AS totalordered,
                       SUM(`shop_order_item`.`order_price_after_discounts`) AS totalorderedvalue,
                       `shop_category`.`url_path` AS categorypath, `shop_order_item`.*
                  FROM `shop_order_item`
             LEFT JOIN `shop_order` ON `shop_order_item`.`shop_order_id` = `shop_order`.`id`
             LEFT JOIN `shop_article_shop_category_mlt` ON `shop_order_item`.`shop_article_id` = `shop_article_shop_category_mlt`.`source_id`
             LEFT JOIN `shop_category` ON `shop_article_shop_category_mlt`.`target_id` = `shop_category`.`id`

               ';
        $aBaseCondition = array();
        if (!is_null($this->sStartDate)) {
            $aBaseCondition[] = "`shop_order`.`datecreated` >= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sStartDate))."'";
        }
        if (!is_null($this->sEndDate)) {
            $aBaseCondition[] = "`shop_order`.`datecreated` <= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sEndDate))." 23:59:59'";
        }
        if ('' != $this->selectedPortalId) {
            $aBaseCondition[] = "`shop_order`.`cms_portal_id` = '".\MySqlLegacySupport::getInstance()->real_escape_string($this->selectedPortalId)."'";
        }

        $query .= ' WHERE ('.implode(') AND (', $aBaseCondition).')';

        $query .= ' GROUP BY `shop_category`.`id`, `shop_order_item`.`shop_article_id`';
        $query .= ' ORDER BY totalordered DESC ';
        $query .= ' LIMIT 0,50';

        $oOrderItems = TdbShopOrderItemList::GetList($query);

        return $oOrderItems;
    }

    /**
     * @return void
     */
    protected function GetAsCSV()
    {
        $oStats = $this->GetStats();
        $sName = 'stats-'.$this->sStartDate.'-'.$this->sEndDate.'.csv';
        $this->OutputAsDownload($oStats->Render('csv.table'), $sName);
    }

    /**
     * @param string $sContent
     * @param string $sTargetFileName
     *
     * @return never
     */
    protected function OutputAsDownload($sContent, $sTargetFileName)
    {
        $sContent = utf8_decode($sContent);
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename="'.$sTargetFileName.'"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.strlen($sContent)); // provide file size
        echo $sContent;
        exit(0);
    }

    /**
     * @return TCMSGroupedStatistics
     */
    protected function GetStats()
    {
        $oStats = new TCMSGroupedStatistics();
        if ($this->bShowChange) {
            $oStats->bShowDiffColumn = true;
        }
        $sDateBlock = 'DATE(datecreated)';
        switch ($this->sDateGroupType) {
            case 'year':
                $sDateBlock = 'YEAR(datecreated)';
                break;

            case 'month':
                $sDateBlock = "DATE_FORMAT(datecreated,'%Y-%m')";
                break;

            case 'week':
                $sDateBlock = "CONCAT(YEAR(datecreated), '-KW', WEEK(datecreated, 7))";
                break;

            case 'day':
            default:
                $sDateBlock = 'DATE(datecreated)';
                break;
        }
        $oLocal = TCMSLocal::GetActive();

        $oGroups = TdbPkgShopStatisticGroupList::GetList();
        while ($oGroup = $oGroups->Next()) {
            $aBaseCondition = array();
            if (!is_null($this->sStartDate)) {
                $aBaseCondition[] = \MySqlLegacySupport::getInstance()->real_escape_string($oGroup->fieldDateRestrictionField)." >= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sStartDate))."'";
            }
            if (!is_null($this->sEndDate)) {
                $aBaseCondition[] = \MySqlLegacySupport::getInstance()->real_escape_string($oGroup->fieldDateRestrictionField)." <= '".\MySqlLegacySupport::getInstance()->real_escape_string($oLocal->StringToDate($this->sEndDate))." 23:59:59'";
            }
            if ('' != $oGroup->fieldPortalRestrictionField && '' != $this->selectedPortalId) {
                $aBaseCondition[] = \MySqlLegacySupport::getInstance()->real_escape_string($oGroup->fieldPortalRestrictionField)." = '".\MySqlLegacySupport::getInstance()->real_escape_string($this->selectedPortalId)."'";
            }
            $aCondition = $aBaseCondition;
            $sBaseQuery = $oGroup->fieldQuery;
            $sCondition = '';
            if (count($aCondition)) {
                $sCondition = 'WHERE ('.implode(') AND (', $aCondition).')';
            }
            $sBlockQuery = str_replace(array('[{sColumnName}]', '[{sCondition}]'), array($sDateBlock, $sCondition), $sBaseQuery);
            $aGroupFields = explode(',', $oGroup->fieldGroups);
            $aRealGroupFields = array();
            if (count($aGroupFields) > 0) {
                foreach ($aGroupFields as $groupId => $sGroupField) {
                    $sGroupField = trim($sGroupField);
                    if (!empty($sGroupField)) {
                        $aRealGroupFields[$groupId] = $sGroupField;
                    }
                }
                reset($aGroupFields);
            }
            $oStats->AddBlock($oGroup->fieldName, $sBlockQuery, $aRealGroupFields);
        }

        return $oStats;
    }

    /**
     * returns an array holding the required style, js, and other info for the
     * module that needs to be loaded in the document head. each include should
     * be one element of the array, and should be formated exactly as it would
     * by another module that requires the same data (so it is not loaded twice).
     * the function will be called for every module on the page AUTOMATICALLY by
     * the controller (the controller will replace the tag "<!--#CMSHEADERCODE#-->" with
     * the results).
     *
     * @return string[]
     */
    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes[] = '<link href="'.TGlobal::GetStaticURL('/chameleon/blackbox/pkgShop/MTShopStatistics.css').'" rel="stylesheet" type="text/css" /> ';
        $aIncludes[] = '<link href="'.TGlobal::GetStaticURL('/chameleon/blackbox/pkgShop/MTShopStatisticsPrint.css').'" rel="stylesheet" type="text/css" media="print" /> ';

        return $aIncludes;
    }
}

class MTShopStatistic_Statblock
{
    /** @var string */
    public $sGroupName = '';

    /** @var array<string, mixed> */
    public $aColumns = array();

    /** @var self[] */
    protected $aSubGroups = array();

    /** @var bool */
    public $bShowGrandTotal = true;

    /**
     * Returns
     * - (mixed) The value of the column if it exists
     * - (int) A sum of the values of subgroups, if it does not but subgroups exist and `bShowGrandTotal=true` is set
     * - (string) An empty string, if the column does not exist but subgroups exist and `bShowGrandTotal=false` is set
     * - (int) 0 If the column does not exist and has no subGroups - irrespective of the value of `bShowGrandTotal`
     *
     * @param string $sColName
     * @return int|mixed|''
     */
    public function GetColumn($sColName)
    {
        // the column is the sume of the sub columns - if there are any
        $dValue = 0;
        if (!array_key_exists($sColName, $this->aColumns) && count($this->aSubGroups) > 0) {
            if ($this->bShowGrandTotal) {
                reset($this->aSubGroups);
                foreach ($this->aSubGroups as $iSubGroup => $oGroupData) {
                    $dValue = $dValue + $oGroupData->GetColumn($sColName);
                }
                reset($this->aSubGroups);
                $this->aColumns[$sColName] = $dValue;
            } else {
                $dValue = '';
            }
        } elseif (array_key_exists($sColName, $this->aColumns)) {
            $dValue = $this->aColumns[$sColName];
        }

        return $dValue;
    }

    /**
     * @param string $sFieldName
     * @return void
     */
    public function AddSubgroupingForField($sFieldName)
    {
        // split the result by field name
    }

    /**
     * @param string $sName
     * @param string $sQuery
     * @param array $aSubGrupping
     * @return void
     */
    public function AddGroupValue($sName, $sQuery, $aSubGrupping = array())
    {
        // subdivide by $aSubGrupping -> nested grupping..
        if (count($aSubGrupping) > 0) {
            foreach ($aSubGrupping as $sField) {
            }
        } else {
            $oGroup = new self();
            $oGroup->sGroupName = $sName;
            $tRes = \MySqlLegacySupport::getInstance()->query($sQuery);
            //echo $sQuery;exit(0);
            while ($aRow = \MySqlLegacySupport::getInstance()->fetch_assoc($tRes)) {
                $oGroup->aColumns[$aRow['sColumnName']] = $aRow['dColumnValue'];
            }
            $this->aSubGroups[] = $oGroup;
        }
    }

    /**
     * @param string $sViewName
     * @param array $aOtherParamter
     * @return string
     */
    public function Render($sViewName, $aOtherParamter = array())
    {
        $oView = new TViewParser();
        /** @var $oView TViewParser */
        $oView->AddVar('oGroup', $this);
        $oView->AddVarArray($aOtherParamter);
        $oView->AddVar('aSubGroups', $this->aSubGroups);

        return $oView->RenderBackendModuleView($sViewName, 'MTShopStatistic', 'Customer');
    }

    /**
     * @return string[]
     */
    public function GetColumnNames()
    {
        $aColumnNames = array_keys($this->aColumns);
        if (count($this->aSubGroups)) {
            reset($this->aSubGroups);
            foreach ($this->aSubGroups as $oSubGroup) {
                $aSubGroupColumns = $oSubGroup->GetColumnNames();
                foreach ($aSubGroupColumns as $sCol) {
                    if (!in_array($sCol, $aColumnNames)) {
                        $aColumnNames[] = $sCol;
                    }
                }
            }
            reset($this->aSubGroups);
        }

        return $aColumnNames;
    }

    /**
     * @return int
     * @psalm-return positive-int
     */
    public function GetColumnGroupDepth()
    {
        $iDepth = 1;
        $iMaxSubDepth = 0;
        reset($this->aSubGroups);
        foreach ($this->aSubGroups as $oSubGroup) {
            $iMaxDepth = $oSubGroup->GetColumnGroupDepth();
            $iMaxSubDepth = max($iMaxSubDepth, $iMaxDepth);
        }
        $iDepth += $iMaxSubDepth;
        reset($this->aSubGroups);

        return $iDepth;
    }
}
