<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\SystemPageServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * varchar field with javascript to set the blog post url onblur.
 * /**/
class TCMSFieldText_ShowExportURL extends TCMSFieldVarchar
{
    /**
     * sets methods that are allowed to be called via URL (ajax call).
     *
     * @return void
     */
    protected function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'GetExportURLList';
    }

    /**
     * get list of all possible export urls as html.
     *
     * @return string
     */
    public function GetExportURLList()
    {
        $sReturn = '';
        if (isset($this->oTableRow) && $this->oTableRow instanceof TdbShop) {
            /* @var $oShop TdbShop */
            $oShop = $this->oTableRow;
            $oPortalList = $oShop->GetFieldCmsPortalList();
            $systemPageService = $this->getSystemPageService();
            while ($oPortal = $oPortalList->Next()) {
                $sExportPageURL = $systemPageService->getLinkToSystemPageRelative('productexport', array(), $oPortal);
                if (strstr($sExportPageURL, 'javascript:alert')) {
                    $sReturn = '<div>'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_product_export.field_show_export_url.error_export_page_missing')).'</div>';
                    continue;
                }
                $sExportPageId = $oPortal->GetSystemPageId('productexport');
                $sSpotName = $this->getExportModuleSpotName($sExportPageId);
                if ('' == $sSpotName) {
                    $sReturn = '<div>'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_product_export.field_show_export_url.error_export_module_missing')).'</div>';
                    continue;
                }
                $aViewList = $this->getViewNameList($sExportPageId);
                if (0 === count($aViewList)) {
                    $sReturn = '<div>'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_product_export.field_show_export_url.error_export_views_missing')).'</div>';
                    continue;
                }
                $sReturn .= '<div><h5>'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_product_export.field_show_export_url.headline', array('%portalName%' => $oPortal->GetName()))).'</h5></div>';
                foreach ($aViewList as $sView) {
                    $sURL = $sExportPageURL.'sModuleSpotName/'.$sSpotName.'/view/'.$sView.'/key/'.$oShop->fieldExportKey;
                    $sReturn .= '<div><b>'.$sView.' -></b> <a href="'.$sURL.'" title="export" target="_blank">'.$sURL.'</a>';
                }
            }
        } else {
            $sReturn = $sReturn = '<div>'.TGlobal::OutHTML(\ChameleonSystem\CoreBundle\ServiceLocator::get('translator')->trans('chameleon_system_shop_product_export.field_show_export_url.error_invalid_field_owner')).'</div>';
        }

        return $sReturn;
    }

    /**
     * get spot name for export module on given page.
     *
     * @param string $sPageId
     *
     * @return string
     */
    protected function getExportModuleSpotName($sPageId)
    {
        $sSpotName = '';
        $sQuery = "SELECT `cms_master_pagedef_spot`.`name` FROM `cms_tpl_page_cms_master_pagedef_spot`
                    INNER JOIN `cms_master_pagedef_spot`ON `cms_master_pagedef_spot`.`id` = `cms_tpl_page_cms_master_pagedef_spot`.`cms_master_pagedef_spot_id`
                     WHERE `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_page_id` = '".$sPageId."'
                       AND `static` = '0'";
        $oRes = MySqlLegacySupport::getInstance()->query($sQuery);
        if (MySqlLegacySupport::getInstance()->num_rows($oRes) > 0) {
            while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($oRes)) {
                $sSpotName = $aRow['name'];
            }
        }

        return $sSpotName;
    }

    /**
     * get all possible export views  for export module on given page.
     *
     * @param string $sPageId
     *
     * @return array
     */
    protected function getViewNameList($sPageId)
    {
        $aViewNameList = array();
        $sQuery = "SELECT `cms_tpl_page_cms_master_pagedef_spot`.`model` FROM `cms_tpl_page_cms_master_pagedef_spot`
                    INNER JOIN `cms_master_pagedef_spot`ON `cms_master_pagedef_spot`.`id` = `cms_tpl_page_cms_master_pagedef_spot`.`cms_master_pagedef_spot_id`
                     WHERE `cms_tpl_page_cms_master_pagedef_spot`.`cms_tpl_page_id` = '".$sPageId."'
                       AND `static` = '0'";
        $oRes = MySqlLegacySupport::getInstance()->query($sQuery);
        if (MySqlLegacySupport::getInstance()->num_rows($oRes) > 0) {
            $sModuleName = '';
            while ($aRow = MySqlLegacySupport::getInstance()->fetch_assoc($oRes)) {
                $sModuleName = $aRow['model'];
            }
            $sModulePath = realpath(PATH_CUSTOMER_FRAMEWORK.'/modules/'.$sModuleName.'/views/');
            if (false != $sModuleName && is_dir($sModulePath)) {
                if ($oDirHandle = opendir($sModulePath)) {
                    while (false !== ($sFile = readdir($oDirHandle))) {
                        if ('.' !== $sFile && '..' !== $sFile && true === is_file($sModulePath.'/'.$sFile)) {
                            $sFile = str_replace('.view.php', '', $sFile);
                            $aViewNameList[] = $sFile;
                        }
                    }
                }
            }
        }

        return $aViewNameList;
    }

    /**
     * adds js to get url list.
     *
     * @return string
     */
    public function GetHTML()
    {
        $sHtml = parent::GetHTML();
        $sHtml .= '<input id="showExportURLList" type="button" class="btn btn-sm btn-secondary mt-2" value="'.TGlobal::OutHTML($this->getTranslator()->trans('chameleon_system_shop_product_export.field_show_export_url.show_list_button_title')).'"/>';
        $sHtml .= '<div id="exportURLListcontainer" class="mt-2"></div>';
        $sHtml .= "
        <script type=\"text/javascript\">

        $(document).ready(function() {
          $('#showExportURLList').click(function(){
            GetAjaxCallTransparent('".$this->GenerateAjaxURL(
                array('_fnc' => 'GetExportURLList', '_fieldName' => $this->name)
            )."', GetExportURLList);
            return false;});
        });

        function GetExportURLList(data,statusText) {
        $('#exportURLListcontainer').html('');
          $('#exportURLListcontainer').append(data);
        }
        </script>
        ";

        return $sHtml;
    }

    /**
     * @return SystemPageServiceInterface
     */
    private function getSystemPageService()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.system_page_service');
    }

    private function getTranslator(): TranslatorInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('translator');
    }
}
