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
use ChameleonSystem\CoreBundle\Util\UrlUtil;
use esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface;

class TPkgShopPaymentTransaction_TCMSTableEditorShopOrder extends TPkgShopPaymentTransaction_TCMSTableEditorShopOrderAutoParent
{
    public function DefineInterface()
    {
        parent::DefineInterface();

        if (false === $this->allowTransactions()) {
            return;
        }

        $this->methodCallAllowed[] = 'paymentTransactionCollectAll';
        $this->methodCallAllowed[] = 'paymentTransactionRefundAll';
        $this->methodCallAllowed[] = 'pkgShopPaymentTransaction_getPartialCollectForm';
        $this->methodCallAllowed[] = 'pkgShopPaymentTransaction_getPartialRefundForm';
        $this->methodCallAllowed[] = 'pkgShopPaymentTransaction_PartialDebit';
    }

    public function paymentTransactionCollectAll()
    {
        if (false === $this->allowTransactions()) {
            return;
        }

        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;
        $dAmount = $oOrder->fieldValueTotal;

        $oTransactionManager = new TPkgShopPaymentTransactionManager($oOrder);

        try {
            $orderItems = $oOrder->GetFieldShopOrderItemList();
            $itemList = array();
            while ($orderItem = $orderItems->Next()) {
                $itemList[$orderItem->id] = $orderItem->fieldOrderAmount;
            }
            $orderItems->GoToStart();
            /** @var $paymentHandler PaymentHandlerWithTransactionSupportInterface|\TdbShopPaymentHandler */
            $paymentHandler = $oOrder->GetPaymentHandler();
            $paymentTransactionHandler = $paymentHandler->paymentTransactionHandlerFactory($oOrder->fieldCmsPortalId);

            $transaction = $paymentTransactionHandler->captureShipment(
                $oTransactionManager,
                $oOrder,
                $dAmount,
                null,
                $itemList
            );

            if (null !== $transaction) {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(
                    TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                    TPkgShopPaymentTransactionManager::MESSAGE_PAYMENT_EXECUTED,
                    array('value' => $transaction->fieldAmount)
                );
            }
        } catch (TPkgCmsException_LogAndMessage $e) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(
                TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                $e->getMessageCode(),
                $e->getAdditionalData()
            );
        }
        $aParam = TGlobal::instance()->GetUserData(null, array('module_fnc', '_noModuleFunction'));
        $sURL = URL_CMS_CONTROLLER.'?'.$this->getUrlUtil()->getArrayAsUrl($aParam, '', '&');
        $this->getRedirect()->redirect($sURL);
    }

    public function paymentTransactionRefundAll()
    {
        if (false === $this->allowTransactions()) {
            return;
        }

        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;
        $dAmount = $oOrder->fieldValueTotal;

        $oTransactionManager = new TPkgShopPaymentTransactionManager($oOrder);

        try {
            $orderItems = $oOrder->GetFieldShopOrderItemList();
            $itemList = array();
            while ($orderItem = $orderItems->Next()) {
                $itemList[$orderItem->id] = $orderItem->fieldOrderAmount;
            }
            $orderItems->GoToStart();
            /** @var $paymentHandler PaymentHandlerWithTransactionSupportInterface|\TdbShopPaymentHandler */
            $paymentHandler = $oOrder->GetPaymentHandler();
            $paymentTransactionHandler = $paymentHandler->paymentTransactionHandlerFactory($oOrder->fieldCmsPortalId);

            $transactionList = $paymentTransactionHandler->refund(
                $oTransactionManager,
                $oOrder,
                $dAmount,
                null,
                null,
                $itemList
            );

            if (null !== $transactionList) {
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(
                    TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                    TPkgShopPaymentTransactionManager::MESSAGE_CREDIT_EXECUTED,
                    array('value' => $dAmount)
                );
            }
        } catch (TPkgCmsException_LogAndMessage $e) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(
                TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                $e->getMessageCode(),
                $e->getAdditionalData()
            );
        }
        $aParam = TGlobal::instance()->GetUserData(null, array('module_fnc', '_noModuleFunction'));
        $sURL = URL_CMS_CONTROLLER.'?'.$this->getUrlUtil()->getArrayAsUrl($aParam, '', '&');
        $this->getRedirect()->redirect($sURL);
    }

    public function pkgShopPaymentTransaction_PartialDebit()
    {
        if (false === $this->allowTransactions()) {
            return;
        }

        $aAmount = TGlobal::instance()->GetUserData('amount');
        if (!is_array($aAmount)) {
            $aAmount = null;
        }
        $debitType = TGlobal::instance()->GetUserData('debitType');
        $transactionValue = (float) TGlobal::instance()->GetUserData('totalAmount');

        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;

        $oTransactionManager = new TPkgShopPaymentTransactionManager($oOrder);

        if (0.00 === $transactionValue) {
            $oTransactionData = $oTransactionManager->getTransactionDataFromOrder($debitType, $aAmount);
            $transactionValue = $oTransactionData->getTotalValue();
            if ($transactionValue < 0) {
                $transactionValue = 0;
            }
        }

        try {
            /** @var $paymentHandler PaymentHandlerWithTransactionSupportInterface|\TdbShopPaymentHandler */
            $paymentHandler = $oOrder->GetPaymentHandler();
            $paymentTransactionHandler = $paymentHandler->paymentTransactionHandlerFactory($oOrder->fieldCmsPortalId);

            $transactionList = null;
            if (TPkgShopPaymentTransactionData::TYPE_PAYMENT === $debitType) {
                $transaction = $paymentTransactionHandler->captureShipment(
                    $oTransactionManager,
                    $oOrder,
                    $transactionValue,
                    null,
                    $aAmount
                );
                $transactionList[] = $transaction;
            } elseif (TPkgShopPaymentTransactionData::TYPE_CREDIT === $debitType) {
                $transactionList = $paymentTransactionHandler->refund(
                    $oTransactionManager,
                    $oOrder,
                    $transactionValue,
                    null,
                    null,
                    $aAmount
                );
            }

            if (null !== $transactionList) {
                $sMessageCode = TPkgShopPaymentTransactionManager::MESSAGE_CREDIT_EXECUTED;
                if (TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT === $debitType) {
                    $sMessageCode = TPkgShopPaymentTransactionManager::MESSAGE_PAYMENT_EXECUTED;
                }
                $oMsgManager = TCMSMessageManager::GetInstance();
                $oMsgManager->AddMessage(
                    TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                    $sMessageCode,
                    array('value' => $transactionValue)
                );
            }
        } catch (TPkgCmsException_LogAndMessage $e) {
            $oMsgManager = TCMSMessageManager::GetInstance();
            $oMsgManager->AddMessage(
                TCMSTableEditorManager::MESSAGE_MANAGER_CONSUMER,
                $e->getMessageCode(),
                $e->getAdditionalData()
            );
        }
        $aParam = TGlobal::instance()->GetUserData(null, array('module_fnc', '_noModuleFunction', 'debitType'));
        $sURL = URL_CMS_CONTROLLER.'?'.$this->getUrlUtil()->getArrayAsUrl($aParam, '', '&');
        $this->getRedirect()->redirect($sURL);
    }

    /**
     * {@inheritdoc}
     */
    protected function GetCustomMenuItems()
    {
        parent::GetCustomMenuItems();

        if (false === is_object($this->oTable) || false === ($this->oTable instanceof TdbShopOrder)) {
            return;
        }
        if (false === $this->allowTransactions()) {
            return;
        }

        if ($this->allowPaymentTransactions()) {
            $this->oMenuItems->AddItem($this->getCustomMenuItem_CollectAll());
            $this->oMenuItems->AddItem($this->getCustomMenuItem_CollectPartial());
        }

        if ($this->allowRefundTransaction()) {
            $this->oMenuItems->AddItem($this->getCustomMenuItem_RefundAll());
            $this->oMenuItems->AddItem($this->getCustomMenuItem_RefundPartial());
        }
    }

    /**
     * @return TCMSTableEditorMenuItem
     */
    private function &getCustomMenuItem_CollectAll()
    {
        $oMenuItem = new TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'collectall';
        $oMenuItem->sDisplayName = TGlobal::Translate('chameleon_system_shop_payment_transaction.action.collect_all');
        $oMenuItem->sIcon = TGlobal::GetStaticURLToWebLib('/images/icons/coins_add.png');

        $sURL = URL_CMS_CONTROLLER.'?';
        $aParams = array(
            'module_fnc' => array('contentmodule' => 'paymentTransactionCollectAll'),
            '_noModuleFunction' => 'true',
            'pagedef' => 'tableeditor',
            'tableid' => $this->oTableConf->id,
            'id' => $this->sId,
        );
        $sURL .= TTools::GetArrayAsURLForJavascript($aParams);

        $oMenuItem->sOnClick = "if (true == confirm('".TGlobal::Translate(
                'chameleon_system_shop_payment_transaction.confirm.collect_all'
            )."')) {TPkgShopPaymentTransaction_closeForm();document.location.href='{$sURL}';}";

        return $oMenuItem;
    }

    /**
     * @return TCMSTableEditorMenuItem
     */
    private function &getCustomMenuItem_RefundAll()
    {
        $oMenuItem = new TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'refundall';
        $oMenuItem->sDisplayName = TGlobal::Translate('chameleon_system_shop_payment_transaction.action.refund_all');
        $oMenuItem->sIcon = TGlobal::GetStaticURLToWebLib('/images/icons/coins_delete.png');

        $sURL = URL_CMS_CONTROLLER.'?';
        $aParams = array(
            'module_fnc' => array('contentmodule' => 'paymentTransactionRefundAll'),
            '_noModuleFunction' => 'true',
            'pagedef' => 'tableeditor',
            'tableid' => $this->oTableConf->id,
            'id' => $this->sId,
        );
        $sURL .= TTools::GetArrayAsURLForJavascript($aParams);

        $oMenuItem->sOnClick = "if (true == confirm('".TGlobal::Translate(
                'chameleon_system_shop_payment_transaction.confirm.refund_all'
            )."')) {TPkgShopPaymentTransaction_closeForm();document.location.href='{$sURL}'};";

        return $oMenuItem;
    }

    /**
     * @return TCMSTableEditorMenuItem
     */
    private function &getCustomMenuItem_CollectPartial()
    {
        $oMenuItem = new TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'collectpartial';
        $oMenuItem->sDisplayName = TGlobal::Translate(
            'chameleon_system_shop_payment_transaction.action.collect_partial'
        );
        $oMenuItem->sIcon = TGlobal::GetStaticURLToWebLib('/images/icons/coins_add.png');

        $sURL = URL_CMS_CONTROLLER.'?';
        $aParams = array(
            'module_fnc' => array('contentmodule' => 'ExecuteAjaxCall'),
            '_fnc' => 'pkgShopPaymentTransaction_getPartialCollectForm',
            '_noModuleFunction' => 'true',
            'pagedef' => 'tableeditor',
            'tableid' => $this->oTableConf->id,
            'id' => $this->sId,
        );
        $sURL .= TTools::GetArrayAsURLForJavascript($aParams);
        $oMenuItem->sOnClick = "TPkgShopPaymentTransaction_closeForm();GetAjaxCallTransparent('".$sURL."', TPkgShopPaymentTransaction_showForm);";

        return $oMenuItem;
    }

    /**
     * @return TCMSTableEditorMenuItem
     */
    private function &getCustomMenuItem_RefundPartial()
    {
        $oMenuItem = new TCMSTableEditorMenuItem();
        $oMenuItem->sItemKey = 'refundpartial';
        $oMenuItem->sDisplayName = TGlobal::Translate(
            'chameleon_system_shop_payment_transaction.action.refund_partial'
        );
        $oMenuItem->sIcon = TGlobal::GetStaticURLToWebLib('/images/icons/coins_delete.png');

        $sURL = URL_CMS_CONTROLLER.'?';
        $aParams = array(
            'module_fnc' => array('contentmodule' => 'ExecuteAjaxCall'),
            '_fnc' => 'pkgShopPaymentTransaction_getPartialRefundForm',
            '_noModuleFunction' => 'true',
            'pagedef' => 'tableeditor',
            'tableid' => $this->oTableConf->id,
            'id' => $this->sId,
        );
        $sURL .= TTools::GetArrayAsURLForJavascript($aParams);
        $oMenuItem->sOnClick = "TPkgShopPaymentTransaction_closeForm();GetAjaxCallTransparent('".$sURL."', TPkgShopPaymentTransaction_showForm);";

        return $oMenuItem;
    }

    public function pkgShopPaymentTransaction_getPartialCollectForm()
    {
        $oViewRenderer = new ViewRenderer();
        $oViewRenderer->AddMapper(new TPkgShopPaymentTransactionMapper_CollectionFormForOrder());
        $oViewRenderer->AddSourceObject('order', $this->oTable);
        $oViewRenderer->AddSourceObject('paymentType', TPkgShopPaymentTransactionData::TYPE_PAYMENT);

        return $oViewRenderer->Render('pkgShopPaymentTransaction/collection-form.html.twig');
    }

    public function pkgShopPaymentTransaction_getPartialRefundForm()
    {
        $oViewRenderer = new ViewRenderer();
        $oViewRenderer->AddMapper(new TPkgShopPaymentTransactionMapper_CollectionFormForOrder());
        $oViewRenderer->AddSourceObject('order', $this->oTable);
        $oViewRenderer->AddSourceObject('paymentType', TPkgShopPaymentTransactionData::TYPE_CREDIT);

        return $oViewRenderer->Render('pkgShopPaymentTransaction/collection-form.html.twig');
    }

    public function GetHtmlHeadIncludes()
    {
        $aIncludes = parent::GetHtmlHeadIncludes();

        $aIncludesFromPackage = $this->getViewRendererSnippetDirectory()->getResourcesForSnippetPackage(
            'pkgShopPaymentTransaction'
        );
        $aIncludes = array_merge($aIncludes, $aIncludesFromPackage);

        return $aIncludes;
    }

    private function allowTransactions()
    {
        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;
        $paymentHandler = $oOrder->GetPaymentHandler();

        return $paymentHandler instanceof PaymentHandlerWithTransactionSupportInterface;
    }

    protected function allowRefundTransaction()
    {
        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;

        return false === $oOrder->fieldCanceled;
    }

    protected function allowPaymentTransactions()
    {
        /** @var TdbShopOrder $oOrder */
        $oOrder = $this->oTable;

        return false === $oOrder->fieldCanceled && false === $oOrder->fieldOrderIsPaid;
    }

    /**
     * @return TPkgViewRendererSnippetDirectoryInterface
     */
    private function getViewRendererSnippetDirectory()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_view_renderer.snippet_directory');
    }

    /**
     * @return ICmsCoreRedirect
     */
    private function getRedirect()
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('chameleon_system_core.redirect');
    }

    private function getUrlUtil(): UrlUtil
    {
        return ServiceLocator::get('chameleon_system_core.util.url');
    }
}
