<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\SecurityBundle\Service\SecurityHelperAccess;
use ChameleonSystem\SecurityBundle\Voter\CmsPermissionAttributeConstants;
use esono\pkgCmsCache\CacheInterface;

class TCMSShopTableEditor_ShopVoucherSeries extends TCMSTableEditor
{
    /**
     * set public methods here that may be called from outside.
     *
     * @return void
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
            $oMenuItem->sDisplayName = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.voucher.action_create');
            $oMenuItem->sIcon = 'fas fa-file-invoice-dollar';

            $oGlobal = TGlobal::instance();
            $oExecutingModulePointer = $oGlobal->GetExecutingModulePointer();

            $aURLData = array('module_fnc' => array($oExecutingModulePointer->sModuleSpotName => 'ExecuteAjaxCall'), '_fnc' => 'CreateVoucherCodes', '_noModuleFunction' => 'true', 'pagedef' => $oGlobal->GetUserData('pagedef'), 'id' => $this->sId, 'tableid' => $this->oTableConf->id);
            $sURL = PATH_CMS_CONTROLLER.'?'.TTools::GetArrayAsURLForJavascript($aURLData);
            $sNumberOfVouchersToCreatePromptText = TGlobal::OutJS(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.voucher.prompt_number_of_vouchers_to_create'));
            $sVoucherCodeToCreatePromptText = TGlobal::OutJS(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.voucher.prompt_voucher_code_to_create'));
            $sJS = "TCMSShopTableEditor_ShopVoucherSeries_CreateVouchers('{$sURL}','".$sNumberOfVouchersToCreatePromptText."','".$sVoucherCodeToCreatePromptText."');";
            $oMenuItem->sOnClick = $sJS;
            $this->oMenuItems->AddItem($oMenuItem);
        }
        if ($this->AllowExportingVoucherCodes()) {
            $oMenuItem = new TCMSTableEditorMenuItem();
            $oMenuItem->sItemKey = 'exportvoucher';
            $oMenuItem->sDisplayName = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop.voucher.action_export');
            $oMenuItem->sIcon = 'fas fa-file-download';

            $oGlobal = TGlobal::instance();
            $oExecutingModulePointer = $oGlobal->GetExecutingModulePointer();

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
     *
     * @return stdClass
     */
    public function CreateVoucherCodes($sCode = null, $iNumberOfVouchers = null)
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');

        $oReturn = new stdClass();
        $oReturn->bError = false;
        $oReturn->sMessage = 'Gutscheine erstellt';

        if ($this->AllowCreatingVoucherCodes()) {
            $oGlobal = TGlobal::instance();
            if (is_null($iNumberOfVouchers)) {
                $iNumberOfVouchers = (int) $oGlobal->GetUserData('iNumberOfVouchers');
            }
            if (is_null($sCode)) {
                $sCode = $oGlobal->GetUserData('sCode');
            }
            $bUseRandomCode = (empty($sCode));

            if (!$bUseRandomCode) {
                // make sure that the code does not exist in another series
                $quotedSeriesId = $connection->quote($this->sId);
                $quotedCode = $connection->quote($sCode);
                $query = "SELECT * FROM `shop_voucher`
                      WHERE `shop_voucher_series_id` <> {$quotedSeriesId}
                        AND `code` = {$quotedCode}";
                $result = $connection->fetchAssociative($query);

                if ($result) {
                    $oReturn->bError = true;
                    $oReturn->sMessage = 'Code wurde bereits in mindestens einer anderen Gutscheinserie verwendet!';
                }
            }

            if (!$oReturn->bError) {
                $oVoucher = TdbShopVoucher::GetNewInstance();
                /** @var $oVoucher TdbShopVoucher */
                $oVoucherCodeTableConf = $oVoucher->GetTableConf();
                $oVoucherCodeEditor = new TCMSTableEditorManager();
                /** @var $oVoucherCodeEditor TCMSTableEditorManager */
                $oVoucherCodeEditor->Init($oVoucherCodeTableConf->id, null);

                $oVoucher->AllowEditByAll(true);
                $this->getCache()->disable();
                for ($i = 0; $i < $iNumberOfVouchers; ++$i) {
                    if ($bUseRandomCode) {
                        do {
                            $sCode = strtolower(TTools::GenerateVoucherCode(10));
                            if ($oVoucher->LoadFromFields(['code' => $sCode])) {
                                $sCode = '';
                            }
                        } while (empty($sCode));
                    }
                    $aData = [
                        'shop_voucher_series_id' => $this->sId,
                        'code' => $sCode,
                        'datecreated' => date('Y-m-d H:i:s'),
                    ];
                    $oVoucherCodeEditor->Save($aData);
                }
                $this->getCache()->enable();
                $this->getCache()->callTrigger('shop_voucher', null); // flush all cache entries related to the voucher table
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
            /** @var SecurityHelperAccess $securityHelper */
            $securityHelper = ServiceLocator::get(SecurityHelperAccess::class);

            $bUserIsInCodeTableGroup = $securityHelper->isGranted(CmsPermissionAttributeConstants::TABLE_EDITOR_ACCESS, $oTargetTableConf->fieldName);
            $bHasNewPermissionOnTargetTable = ($securityHelper->isGranted(CmsPermissionAttributeConstants::TABLE_EDITOR_NEW, 'shop_voucher'));
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
     *
     * @return void
     */
    public function ExportVoucherCodes()
    {
        $sReturn = '';

        if ($this->AllowExportingVoucherCodes()) {
            /** @var \Doctrine\DBAL\Connection $connection */
            $connection = \ChameleonSystem\CoreBundle\ServiceLocator::get('database_connection');
            $quotedSeriesId = $connection->quote($this->sId);

            $oVouchers = TdbShopVoucherList::GetList("SELECT * FROM `shop_voucher` WHERE `shop_voucher_series_id` = {$quotedSeriesId}");
            $oVouchers->GoToStart();
            $count = 0;
            $aCsvVouchers = [];
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

            $translator = \ChameleonSystem\CoreBundle\ServiceLocator::get('translator');
            $sCsv = '"'.TGlobal::OutHTML($translator->trans('chameleon_system_shop.voucher.export_column_code')).'";"'.TGlobal::OutHTML($translator->trans('chameleon_system_shop.voucher.export_column_created')).'";"'.TGlobal::OutHTML($translator->trans('chameleon_system_shop.voucher.export_column_spent')).'";"'.TGlobal::OutHTML($translator->trans('chameleon_system_shop.voucher.export_column_spent_date'))."\"\n".$sCsv;

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

    private function getCache(): CacheInterface
    {
        return ServiceLocator::get('chameleon_system_core.cache');
    }
}
