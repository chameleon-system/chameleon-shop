<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ChameleonSystem\CoreBundle\Service\PortalDomainServiceInterface;
use ChameleonSystem\CoreBundle\ServiceLocator;
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @property TdbShopOrder $oTable
 * @property TdbShopOrder $oTablePreChangeData
 */
class TCMSTableEditorShopOrderEndPoint extends TCMSTableEditor
{
    /**
     * {@inheritdoc}
     */
    protected function GetCustomMenuItems()
    {
        parent::GetCustomMenuItems();

        $sendConfirmMenuItem = $this->getSendConfirmMenuItem();
        $this->oMenuItems->AddItem($sendConfirmMenuItem);

        $exportMenuItem = $this->getExportMenuItem();
        $this->oMenuItems->AddItem($exportMenuItem);
    }

    private function getSendConfirmMenuItem(): \TCMSTableEditorMenuItem
    {
        $oMenuItem = new \TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'sendordermail';
        $oMenuItem->sDisplayName = TGlobal::Translate('chameleon_system_shop.orders.action_send_order_confirm_mail');
        $oMenuItem->sIcon = 'fas fa-envelope';

        $oGlobal = TGlobal::instance();
        $sURL = PATH_CMS_CONTROLLER.'?';
        $aParams = array('module_fnc' => array('contentmodule' => 'ExecuteAjaxCall'), '_fnc' => 'GetFrontendActionUrlToSendOrderEmail', '_noModuleFunction' => 'true', 'pagedef' => 'tableeditor', 'tableid' => $this->oTableConf->id, 'id' => $this->sId);
        $sURL .= TTools::GetArrayAsURLForJavascript($aParams);

        $oMenuItem->sOnClick = "ShopOrderSendConfirmOrderMail('{$sURL}', '".TGlobal::OutHTML($this->oTable->sqlData['user_email'])."');";

        return $oMenuItem;
    }

    private function getExportMenuItem(): \TCMSTableEditorMenuItem
    {
        $translator = $this->getTranslator();

        $menuItem = new \TCMSTableEditorMenuItem();
        $menuItem->sItemKey = 'exportorder';
        $menuItem->setTitle($translator->trans('chameleon_system_shop.orders.export_button_title'));
        $menuItem->sIcon = 'fas fa-file-export';

        $url = URL_CMS_CONTROLLER.'?';
        $params = [
            'module_fnc' => ['contentmodule' => 'ExecuteAjaxCall'],
            '_fnc' => 'exportOrder',
            '_noModuleFunction' => 'true',
            'pagedef' => 'tableeditor',
            'tableid' => $this->oTableConf->id,
            'id' => $this->sId,
        ];
        $url .= $this->getUrlUtil()->getArrayAsUrl($params, '', '&');

        $confirmText = \TGlobal::OutJS($translator->trans('chameleon_system_shop.orders.export_confirm'));
        $menuItem->sOnClick = "if (confirm('$confirmText')) { GetAjaxCall('".$url."',DisplayAjaxMessage); }";

        return $menuItem;
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();
        $aIncludes[] = '<script src="'.TGlobal::GetStaticURLToWebLib('/javascript/shop.js').'" type="text/javascript"></script>';

        return $aIncludes;
    }

    /**
     * send the current order to the email.
     *
     * @param string $sMail - can also be passed via get/post
     *
     * @return TCMSstdClass
     */
    public function ShopOrderSendConfirmOrderMail($sMail = null)
    {
        $oGlobal = TGlobal::instance();
        if (is_null($sMail)) {
            $sMail = $oGlobal->GetUserData('sTargetMail');
        }
        $oReturnData = $this->GetObjectShortInfo();
        $oReturnData->sTargetMail = $sMail;

        $bSuccess = false;
        $oOrder = TdbShopOrder::GetNewInstance();
        /** @var $oOrder TdbShopOrder */
        $oOrder->AllowEditByAll(true);
        if (!$oOrder->Load($this->sId)) {
            $oOrder = null;
        } else {
            $bSuccess = $oOrder->SendOrderNotification($sMail);
        }

        if (true === $bSuccess) {
            $oReturnData->bSuccess = true;
            $oReturnData->sMessage = TGlobal::Translate('chameleon_system_shop.orders.msg_order_confirm_sent', array('%mail%' => $sMail));
        } else {
            $oReturnData->bSuccess = false;
            $oReturnData->sMessage = TGlobal::Translate('chameleon_system_shop.orders.error_sending_confirm_mail', array('%error%' => $bSuccess));
        }

        return $oReturnData;
    }

    /**
     * send order notification for current order from backend - uses frontend action,
     * so portal and snippets are set correctly.
     *
     * @param string $sMail - can also be passed via get/post
     *
     * @return string
     */
    public function GetFrontendActionUrlToSendOrderEmail($sMail = null)
    {
        $sURL = '';
        if (is_null($sMail)) {
            $oGlobal = \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.global');
            $sMail = $oGlobal->GetUserData('sTargetMail');
        }
        if (!empty($sMail) && is_object($this->oTable)) {
            $sPortalId = $this->oTable->fieldCmsPortalId;
            if (empty($this->oTable->fieldCmsPortalId)) {
                $oPortal = $this->getPortalDomainService()->getActivePortal();
                $sPortalId = $oPortal->id;
            }
            $oAction = TdbPkgRunFrontendAction::CreateAction('TPkgRunFrontendAction_SendOrderEMail', $sPortalId, array('email' => $sMail, 'order_id' => $this->sId));
            $sURL = $oAction->getUrlToRunAction();
        }

        return $sURL;
    }

    public function exportOrder(): array
    {
        $translator = $this->getTranslator();

        if (false !== $this->oTable->ExportOrderForWaWiHook($this->oTable->GetPaymentHandler())) {
            $this->oTable->MarkOrderAsExportedToWaWi(true);

            return [
                'messageType' => 'SUCCESS',
                'message' => $translator->trans('chameleon_system_shop.orders.export_success')
            ];
        }

        // TODO this should however be an http error response; which would then use AjaxError() on js side
        return [
            'messageType' => 'ERROR',
            'message' => $translator->trans('chameleon_system_shop.orders.export_failure')
        ];

    }

    /**
     * set public methods here that may be called from outside.
     *
     * @return void
     */
    public function DefineInterface()
    {
        parent::DefineInterface();
        $this->methodCallAllowed[] = 'ShopOrderSendConfirmOrderMail';
        $this->methodCallAllowed[] = 'GetFrontendActionUrlToSendOrderEmail';
        $this->methodCallAllowed[] = 'exportOrder';
    }

    /**
     * gets called after save if all posted data was valid.
     *
     * @param TIterator  $oFields    holds an iterator of all field classes from DB table with the posted values or default if no post data is present
     * @param TCMSRecord $oPostTable holds the record object of all posted data
     */
    protected function PostSaveHook(&$oFields, &$oPostTable)
    {
        parent::PostSaveHook($oFields, $oPostTable);
        $bStornoChanged = ($this->oTablePreChangeData->sqlData['canceled'] != $this->oTable->sqlData['canceled']);
        $bValueChanged = ($this->oTablePreChangeData->sqlData['value_total'] != $this->oTable->sqlData['value_total']);

        $bUserChanged = ($this->oTablePreChangeData->sqlData['data_extranet_user_id'] != $this->oTable->sqlData['data_extranet_user_id']);
        if ($bStornoChanged || $bUserChanged || $bValueChanged) {
            if (!empty($this->oTable->fieldDataExtranetUserId)) {
                $oTmpuser = null;
                $oTmpuser = $this->oTable->GetFieldDataExtranetUser();
                if (!is_null($oTmpuser)) {
                    TdbDataExtranetGroup::UpdateAutoAssignToUser($oTmpuser);
                }
            }
            if ($bStornoChanged) {
                $this->oTable->UpdateUsedVouchers($this->oTable->sqlData['canceled']);
                $this->oTable->UpdateStock('1' !== $this->oTable->sqlData['canceled']);
            }
        }
        if ($bUserChanged && !empty($this->oTablePreChangeData->sqlData['data_extranet_user_id'])) {
            $oTmpuser = TdbDataExtranetUser::GetNewInstance();
            $oTmpuser->Load($this->oTablePreChangeData->sqlData['data_extranet_user_id']);
            if (!is_null($oTmpuser)) {
                TdbDataExtranetGroup::UpdateAutoAssignToUser($oTmpuser);
            }
        }
    }

    /**
     * is called only from Delete method and calls all delete relevant methods
     * executes the final SQL Delete Query.
     *
     * @return void
     * @psalm-suppress AssignmentToVoid, InvalidReturnStatement
     * @FIXME Saving the result of `parent::DeleteExecute()` and returning does not make sense for a `void` return
     */
    protected function DeleteExecute()
    {
        $oOrder = clone $this->oTable;
        $bReturn = parent::DeleteExecute();
        if (!empty($oOrder->sqlData['data_extranet_user_id'])) {
            $oTmpuser = TdbDataExtranetUser::GetNewInstance();
            $oTmpuser->Load($oOrder->sqlData['data_extranet_user_id']);
            if (!is_null($oTmpuser)) {
                TdbDataExtranetGroup::UpdateAutoAssignToUser($oTmpuser);
            }
        }

        return $bReturn;
    }

    private function getPortalDomainService(): PortalDomainServiceInterface
    {
        return ServiceLocator::get('chameleon_system_core.portal_domain_service');
    }

    private function getUrlUtil(): UrlUtil
    {
        return ServiceLocator::get('chameleon_system_core.util.url');
    }

    private function getTranslator(): TranslatorInterface
    {
        return ServiceLocator::get('translator');
    }
}
