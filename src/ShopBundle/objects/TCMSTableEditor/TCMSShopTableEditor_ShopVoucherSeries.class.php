<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TCMSShopTableEditor_ShopVoucherSeries extends TCMSTableEditor
{
    /**
     * set public methods here that may be called from outside.
     */
    public function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'CreateVoucherCodes';
        $this->methodCallAllowed[] = 'ExportVoucherCodes';
    }

    /**
     * add table-specific buttons to the editor (add them directly to $this->oMenuItems).
     */
    protected function GetCustomMenuItems()
    {
        parent::GetCustomMenuItems();
        if ($this->AllowCreatingVoucherCodes()) {
            $oMenuItem = new TCMSTableEditorMenuItem();
            $oMenuItem->sItemKey = 'createvoucher';
            $oMenuItem->sDisplayName = TGlobal::Translate('chameleon_system_shop.voucher.action_create');
            $oMenuItem->sIcon = 'fas fa-file-invoice-dollar';

            $oGlobal = TGlobal::instance();
            $oExecutingModulePointer = &$oGlobal->GetExecutingModulePointer();

            $aURLData = array('module_fnc' => array($oExecutingModulePointer->sModuleSpotName => 'ExecuteAjaxCall'), '_fnc' => 'CreateVoucherCodes', '_noModuleFunction' => 'true', 'pagedef' => $oGlobal->GetUserData('pagedef'), 'id' => $this->sId, 'tableid' => $this->oTableConf->id);
            $sURL = PATH_CMS_CONTROLLER.'?'.TTools::GetArrayAsURLForJavascript($aURLData);
            $sNumberOfVouchersToCreatePromptText = TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.prompt_number_of_vouchers_to_create'));
            $sVoucherCodeToCreatePromptText = TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.prompt_voucher_code_to_create'));
            $sJS = "TCMSShopTableEditor_ShopVoucherSeries_CreateVouchers('{$sURL}','".$sNumberOfVouchersToCreatePromptText."','".$sVoucherCodeToCreatePromptText."');";
            $oMenuItem->sOnClick = $sJS;
            $this->oMenuItems->AddItem($oMenuItem);
        }
        if ($this->AllowExportingVoucherCodes()) {
            $oMenuItem = new TCMSTableEditorMenuItem();
            $oMenuItem->sItemKey = 'exportvoucher';
            $oMenuItem->sDisplayName = TGlobal::Translate('chameleon_system_shop.voucher.action_export');
            $oMenuItem->sIcon = 'fas fa-file-download';

            $oGlobal = TGlobal::instance();
            $oExecutingModulePointer = &$oGlobal->GetExecutingModulePointer();

            $aURLData = array('module_fnc' => array($oExecutingModulePointer->sModuleSpotName => 'ExportVoucherCodes'), '_noModuleFunction' => 'true', 'pagedef' => $oGlobal->GetUserData('pagedef'), 'id' => $this->sId, 'tableid' => $this->oTableConf->id);
            $sURL = PATH_CMS_CONTROLLER.'?'.TTools::GetArrayAsURLForJavascript($aURLData);
            $sJS = "void(window.open('".$sURL."'))";
            $oMenuItem->sOnClick = $sJS;
            $this->oMenuItems->AddItem($oMenuItem);
        }
    }

    /**
     * create a number of vouchers in the shop_voucher table.
     *
     * @param string $sCode             - the code to use. if empty, a random unique code will be generated
     * @param int    $iNumberOfVouchers - number of vouchers to create (will fetch this from user input if null given)
     */
    public function CreateVoucherCodes($sCode = null, $iNumberOfVouchers = null)
    {
        $oReturn = new stdClass();
        $oReturn->bError = false;
        $oReturn->sMessage = 'Gutscheine erstellt';

        if ($this->AllowCreatingVoucherCodes()) {
            $oGlobal = TGlobal::instance();
            if (is_null($iNumberOfVouchers)) {
                $iNumberOfVouchers = intval($oGlobal->GetUserData('iNumberOfVouchers'));
            }
            if (is_null($sCode)) {
                $sCode = $oGlobal->GetUserData('sCode');
            }
            $bUseRandomCode = (empty($sCode));

            if (!$bUseRandomCode) {
                // make sure that the code does not exist in another series
                $query = "SELECT * FROM `shop_voucher`
                     WHERE `shop_voucher_series_id` <> '".MySqlLegacySupport::getInstance()->real_escape_string($this->sId)."'
                       AND `code` = '".MySqlLegacySupport::getInstance()->real_escape_string($sCode)."'";
                $tRes = MySqlLegacySupport::getInstance()->query($query);
                if (MySqlLegacySupport::getInstance()->num_rows($tRes) > 0) {
                    // invalid code
                    $oReturn->bError = true;
                    $oReturn->sMessage = 'Code wurde bereits in mindestens einer anderen Gutscheinserie verwendet!';
                }
            }
            if (!$oReturn->bError) {
                $oVoucher = TdbShopVoucher::GetNewInstance();
                /** @var $oVoucher TdbShopVoucher */
                $oVoucherCodeTableConf = &$oVoucher->GetTableConf();
                $oVoucherCodeEditor = new TCMSTableEditorManager();
                /** @var $oEditor TCMSTableEditorManager */
                $oVoucherCodeEditor->Init($oVoucherCodeTableConf->id, null);

                $oVoucher->AllowEditByAll(true);
                TCacheManager::SetDisableCaching(true);
                for ($i = 0; $i < $iNumberOfVouchers; ++$i) {
                    if ($bUseRandomCode) {
                        do {
                            $sCode = strtolower(TTools::GenerateVoucherCode(10));
                            if ($oVoucher->LoadFromFields(array('code' => $sCode))) {
                                $sCode = '';
                            }
                        } while (empty($sCode));
                    }
                    $aData = array('shop_voucher_series_id' => $this->sId, 'code' => $sCode, 'datecreated' => date('Y-m-d H:i:s'));
                    // save entrie...
                    $oVoucherCodeEditor->Save($aData);
                }
                TCacheManager::SetDisableCaching(false);
                TCacheManager::PerformeTableChange('shop_voucher', null); // flush all cache entries related to the voucher table
            }
        }

        return $oReturn;
    }

    /**
     * return true if the current user has the right to create codes in the shop_voucher table.
     *
     * @return bool
     */
    protected function AllowCreatingVoucherCodes()
    {
        $bAllowCreatingCodes = false;

        $oTargetTableConf = TdbCmsTblConf::GetNewInstance();
        /** @var $oTargetTableConf TdbCmsTblConf */
        if ($oTargetTableConf->Loadfromfield('name', 'shop_voucher')) {
            $oGlobal = TGlobal::instance();
            $bUserIsInCodeTableGroup = $oGlobal->oUser->oAccessManager->user->IsInGroups($oTargetTableConf->fieldCmsUsergroupId);
            $bHasNewPermissionOnTargetTable = ($oGlobal->oUser->oAccessManager->HasNewPermission('shop_voucher'));
            $bAllowCreatingCodes = ($bUserIsInCodeTableGroup && $bHasNewPermissionOnTargetTable);
        }

        return $bAllowCreatingCodes;
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes[] = '<script src="'.URL_USER_CMS_PUBLIC.'/blackbox/pkgShop/TCMSShopTableEditor_ShopVoucherSeries.js" type="text/javascript"></script>';

        return $aIncludes;
    }

    /**
     * return true if the current user has the right to export codes from the shop_voucher table.
     *
     * @return bool
     */
    protected function AllowExportingVoucherCodes()
    {
        $bAllowCreatingCodes = $this->AllowCreatingVoucherCodes();

        return $bAllowCreatingCodes;
    }

    /**
     * export all vouchers from a voucher series as csv-file with Code, Datecreated and UsedUp-Information.
     */
    public function ExportVoucherCodes()
    {
        $sReturn = '';

        if ($this->AllowExportingVoucherCodes()) {
            $oVouchers = TdbShopVoucherList::GetList("SELECT * FROM `shop_voucher` WHERE `shop_voucher_series_id`='".MySqlLegacySupport::getInstance()->real_escape_string($this->sId)."'");
            $oVouchers->GoToStart();
            $count = 0;
            while ($oVoucher = $oVouchers->Next()) {
                $aCsvVouchers[$count][0] = str_replace('"', '""', $oVoucher->fieldCode);
                if ('0000-00-00 00:00:00' != $oVoucher->fieldDatecreated) {
                    $aCsvVouchers[$count][1] = date('d.m.Y H:i', strtotime($oVoucher->fieldDatecreated));
                } else {
                    $aCsvVouchers[$count][3] = '';
                }
                $aCsvVouchers[$count][2] = $oVoucher->fieldIsUsedUp;
                if ('0000-00-00 00:00:00' != $oVoucher->fieldDateUsedUp) {
                    $aCsvVouchers[$count][3] = date('d.m.Y H:i', strtotime($oVoucher->fieldDateUsedUp));
                } else {
                    $aCsvVouchers[$count][3] = '';
                }
                ++$count;
            }

            $sCsv = '';
            for ($i = 0; $i < count($aCsvVouchers); ++$i) {
                $sCsv .= '"'.implode('";"', $aCsvVouchers[$i])."\"\n";
            }
            $sCsv = '"'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.export_column_code')).'";"'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.export_column_created')).'";"'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.export_column_spent')).'";"'.TGlobal::OutHTML(TGlobal::Translate('chameleon_system_shop.voucher.export_column_spent_date'))."\"\n".$sCsv;

            while (@ob_end_clean()) {
            }
            header('Cache-Control: must-revalidate');
            header('Pragma: must-revalidate');
            header('Content-type: application/csv');
            header('Content-disposition: attachment; filename=ShopVouchers.csv');

            echo $sCsv;

            exit(0);
        } else {
            echo 'Keine Berechtigung';
        }
    }
}
