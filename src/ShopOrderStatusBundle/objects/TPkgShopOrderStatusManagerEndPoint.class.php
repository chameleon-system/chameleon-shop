<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!defined('PKG_SHOP_ORDER_STATUS_SEND_STATUS_NOTIFICATION_MAIL')) {
    define('PKG_SHOP_ORDER_STATUS_SEND_STATUS_NOTIFICATION_MAIL', true);
}
class TPkgShopOrderStatusManagerEndPoint
{
    /**
     * @var IPkgCmsCoreLog
     */
    private $logger = null;

    public function setLogger(IPkgCmsCoreLog $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return IPkgCmsCoreLog
     */
    protected function getLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        return \ChameleonSystem\CoreBundle\ServiceLocator::get('cmsPkgCore.logChannel.standard');
    }

    /**
     * return a array of methods that should be called after the status was added. Some things to node
     * * takes an instance of TdbShopOrderStatus as parameter
     * * may throw exceptions of type TPkgCmsException (or any of its children).
     *
     * @return array
     */
    protected function getPostOrderStatusAddedHookList()
    {
        if (false === PKG_SHOP_ORDER_STATUS_SEND_STATUS_NOTIFICATION_MAIL) {
            return array();
        } else {
            return array('sendStatusMailToCustomer');
        }
    }

    /**
     * @param TPkgShopOrderStatusData $oData
     *
     * @throws TPkgCmsException_Log
     * @throws TPkgShopOrderStatusException_PostOrderStatusAddedExceptions
     *
     * @return \TdbShopOrderStatus
     */
    final public function addStatus(TPkgShopOrderStatusData $oData)
    {
        $this->getLogger()->info('add status', __FILE__, __LINE__, array('oData' => $oData));
        $this->validateStatusData($oData);
        $this->getLogger()->info('status validated ok', __FILE__, __LINE__);
        $aData = $oData->getDataAsTdbArray();
        $oStatus = TdbShopOrderStatus::GetNewInstance($aData);
        $oStatus->AllowEditByAll(true);
        $oStatus->SaveFieldsFast($aData);
        $oStatus->AllowEditByAll(false);
        $this->getLogger()->info('status saved', __FILE__, __LINE__);

        $aItems = $oData->getItems();
        $itemCount = count($aItems);
        $count = 0;
        /** @var TPkgShopOrderStatusItemData $item */
        foreach ($aItems as $item) {
            ++$count;
            $this->getLogger()->info("process status  {$count}/{$itemCount}", __FILE__, __LINE__);
            $aItem = $item->getDataAsTdbArray();
            $aItem['shop_order_status_id'] = $oStatus->id;

            $oItem = TdbShopOrderStatusItem::GetNewInstance($aItem);
            $oItem->AllowEditByAll(true);
            $oItem->SaveFieldsFast($aItem);
            $oItem->AllowEditByAll(false);
        }

        $aExceptionList = $this->orderStatusAddedHook($oStatus);
        if (count($aExceptionList) > 0) {
            $orderId = $oData->getOrder()->id;
            $e = new TPkgShopOrderStatusException_PostOrderStatusAddedExceptions(
                "post order status added hook throw multiple exceptions for order {$orderId} with status {$oStatus->id}",
                array('oData' => $oData, 'status' => $oStatus->sqlData)
            );
            $e->setExceptionList($aExceptionList)->setOrderStatus($oStatus);
            throw $e;
        }

        return $oStatus;
    }

    /**
     * validates the input.
     *
     * @param TPkgShopOrderStatusData $oData
     *
     * @throws TPkgCmsException_Log
     */
    protected function validateStatusData(TPkgShopOrderStatusData $oData)
    {
        // does the order exists?
        $order = $oData->getOrder();
        if (!$order) {
            throw new TPkgCmsException_Log('order status update: missing order object', array('statusdata' => $oData));
        }
        // do the items exists and belong to the order? and is the amount shippable?
        $aItems = $oData->getItems();
        foreach ($aItems as $oItem) {
            /** @var TPkgShopOrderStatusItemData $oItem */
            $oOrderItem = TdbShopOrderItem::GetNewInstance();
            if (false === $oOrderItem->LoadFromFields(
                    array('shop_order_id' => $order->id, 'id' => $oItem->getShopOrderItemId())
                )
            ) {
                throw new TPkgCmsException_Log(
                    'order status update: order item invalid',
                    array('statusdata' => $oData, 'item' => $oItem)
                );
            }
        }
    }

    private function orderStatusAddedHook(TdbShopOrderStatus $oStatus)
    {
        $aExceptionList = array();
        $this->getLogger()->info('orderStatusAddedHook', __FILE__, __LINE__);
        foreach ($this->getPostOrderStatusAddedHookList() as $sMethodName) {
            try {
                $this->getLogger()->info("calling \$this->{$sMethodName} on status", __FILE__, __LINE__);
                call_user_func(array($this, $sMethodName), $oStatus);
                $this->getLogger()->info("called \$this->{$sMethodName} on status", __FILE__, __LINE__);
            } catch (TPkgCmsException $e) {
                $aExceptionList[] = $e;
            }
        }

        return $aExceptionList;
    }

    /**
     * @param TdbShopOrderStatus $oStatus
     *
     * @return bool
     *
     * @throws TPkgCmsException_Log
     */
    public function sendStatusMailToCustomer(TdbShopOrderStatus $oStatus)
    {
        $this->getLogger()->info('start sendStatusMailToCustomer', __FILE__, __LINE__);
        $statusCode = $oStatus->GetFieldShopOrderStatusCode();

        if (null !== $statusCode && false === $statusCode->fieldSendMailNotification) {
            $this->getLogger()->info('no mail needed', __FILE__, __LINE__);

            return true;
        }

        $oGlobal = TGlobal::instance();
        if ($oGlobal->isCMSMode()) {
            $this->getLogger()->info('sending mail using front end action', __FILE__, __LINE__);
            $bSuccess = $this->sendStatusMailToCustomerWithFrontEndAction($oStatus);
            $this->getLogger()->info('done sending mail using front end action', __FILE__, __LINE__);
        } else {
            $this->getLogger()->info('sending mail classic', __FILE__, __LINE__);
            $oMailProfile = null;
            if ('' !== $statusCode->fieldDataMailProfileId) {
                $oMailProfile = $statusCode->GetFieldDataMailProfile();
                $oMailProfile->SetSubject($oMailProfile->fieldSubject);
            } else {
                $oMailProfile = TdbDataMailProfile::GetProfile(TdbShopOrder::MAIL_STATUS_UPDATE);
            }
            if (null === $oMailProfile) {
                throw new TPkgCmsException_Log(
                    'unable to send update mail because matching profile does not exists: '.TdbShopOrder::MAIL_STATUS_UPDATE,
                    array('oStatus' => $oStatus)
                );
            }
            $oOrder = $oStatus->GetFieldShopOrder();
            $aOrderInfo = $oOrder->GetSQLWithTablePrefix();
            $oMailProfile->AddDataArray($aOrderInfo);
            $aOrderStatusInfo = $oStatus->GetSQLWithTablePrefix();
            $oMailProfile->AddDataArray($aOrderStatusInfo);
            $oViewRenderer = new ViewRenderer();
            $oViewRenderer->AddMapper(new TPkgShopOrderStatusMapper_Status());
            $oViewRenderer->addMapperFromIdentifier('chameleon_system_shop_currency.mapper.shop_currency_mapper');
            $oViewRenderer->AddSourceObject('oObject', $oStatus);
            $sOrderStatusText = $oViewRenderer->Render('/pkgShop/shopOrder/status/oneOrderStatusEntry.html.twig');
            $oMailProfile->AddData('sOrderStatusText', $sOrderStatusText);
            $oMailProfile->ChangeToAddress(
                $oOrder->fieldUserEmail,
                $oOrder->fieldAdrBillingFirstname.' '.$oOrder->fieldAdrBillingLastname
            );
            $this->getLogger()->info('sent mail classic', __FILE__, __LINE__);
            $bSuccess = $oMailProfile->SendUsingObjectView('emails', 'Customer');
            $this->getLogger()->info('done sending mail classic', __FILE__, __LINE__);
        }

        return $bSuccess;
    }

    /**
     * send status mail simulating a front end action to use twig templates from customer.
     *
     * @param TdbShopOrderStatus $oStatus
     *
     * @return bool
     */
    protected function sendStatusMailToCustomerWithFrontEndAction(TdbShopOrderStatus $oStatus)
    {
        $bSuccess = false;
        $sPortalId = null;
        $oOrder = $oStatus->GetFieldShopOrder();
        if (!is_null($oOrder) && '' != $oOrder->fieldCmsPortalId) {
            $sPortalId = $oOrder->fieldCmsPortalId;
        }
        $oAction = TdbPkgRunFrontendAction::CreateAction(
            'TPkgRunFrontendAction_SendOrderStatusEMail',
            $sPortalId,
            array('order_status_id' => $oStatus->id, 'order_id' => $oStatus->fieldShopOrderId)
        );
        $sURL = $oAction->getUrlToRunAction();
        $sURL = str_replace('&amp;', '&', $sURL); // remove encoding
        $oToHostHandler = new TPkgCmsCoreSendToHost();
        $oToHostHandler->setConfigFromUrl($sURL);
        $this->getLogger()->info('sending mail vai frontend action using URL: '.$sURL, __FILE__, __LINE__);
        $executeRequestResponse = $oToHostHandler->executeRequest();
        if (preg_match('#{.*}#', $executeRequestResponse, $aMatches)) {
            $this->getLogger()->info('done sending mail vai frontend action. got response ', __FILE__, __LINE__, array('response' => $aMatches[0]));
            $aResponse = json_decode($aMatches[0], true);
            if (is_array($aResponse) && count(
                $aResponse
            ) > 0 && isset($aResponse['sMessageType']) && 'MESSAGE' == $aResponse['sMessageType']
            ) {
                $bSuccess = true;
            }
        } else {
            $this->getLogger()->error('failed sending mail via frontend action. got response', __FILE__, __LINE__, array('response' => $executeRequestResponse));
        }

        return $bSuccess;
    }
}
