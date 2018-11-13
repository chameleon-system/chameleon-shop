<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Psr\Log\LoggerInterface;

if (!defined('PKG_SHOP_ORDER_STATUS_SEND_STATUS_NOTIFICATION_MAIL')) {
    define('PKG_SHOP_ORDER_STATUS_SEND_STATUS_NOTIFICATION_MAIL', true);
}
class TPkgShopOrderStatusManagerEndPoint
{
    /**
     * @var IPkgCmsCoreLog
     */
    private $logger = null;

    /**
     * @param IPkgCmsCoreLog $logger
     *
     * @deprecated - is not supported anymore; use only getShopLogger() or do your own logging
     */
    public function setLogger(IPkgCmsCoreLog $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return IPkgCmsCoreLog
     *
     * @deprecated - use getShopLogger()
     */
    protected function getLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        return \ChameleonSystem\CoreBundle\ServiceLocator::get('cmsPkgCore.logChannel.standard');
    }

    protected function getShopLogger(): LoggerInterface
    {
        return \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.shop_order');
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
        $this->getShopLogger()->info('add status', array('oData' => $oData));
        $this->validateStatusData($oData);
        $this->getShopLogger()->info('status validated ok');
        $aData = $oData->getDataAsTdbArray();
        $oStatus = TdbShopOrderStatus::GetNewInstance($aData);
        $oStatus->AllowEditByAll(true);
        $oStatus->SaveFieldsFast($aData);
        $oStatus->AllowEditByAll(false);
        $this->getShopLogger()->info('status saved');

        $aItems = $oData->getItems();
        $itemCount = count($aItems);
        $count = 0;
        /** @var TPkgShopOrderStatusItemData $item */
        foreach ($aItems as $item) {
            ++$count;
            $this->getShopLogger()->info("process status  {$count}/{$itemCount}");
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
        $this->getShopLogger()->info('orderStatusAddedHook');
        foreach ($this->getPostOrderStatusAddedHookList() as $sMethodName) {
            try {
                $this->getShopLogger()->info("calling \$this->{$sMethodName} on status");
                call_user_func(array($this, $sMethodName), $oStatus);
                $this->getShopLogger()->info("called \$this->{$sMethodName} on status");
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
        $this->getShopLogger()->info('start sendStatusMailToCustomer');
        $statusCode = $oStatus->GetFieldShopOrderStatusCode();

        if (null !== $statusCode && false === $statusCode->fieldSendMailNotification) {
            $this->getShopLogger()->info('no mail needed');

            return true;
        }

        $oGlobal = TGlobal::instance();
        if ($oGlobal->isCMSMode()) {
            $this->getShopLogger()->info('sending mail using front end action');
            $bSuccess = $this->sendStatusMailToCustomerWithFrontEndAction($oStatus);
            $this->getShopLogger()->info('done sending mail using front end action');
        } else {
            $this->getShopLogger()->info('sending mail classic');
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
            $this->getShopLogger()->info('sent mail classic');
            $bSuccess = $oMailProfile->SendUsingObjectView('emails', 'Customer');
            $this->getShopLogger()->info('done sending mail classic');
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
        $this->getShopLogger()->info(sprintf('sending mail via frontend action using URL: %s', $sURL));
        $executeRequestResponse = $oToHostHandler->executeRequest();
        if (preg_match('#{.*}#', $executeRequestResponse, $aMatches)) {
            $this->getShopLogger()->info('done sending mail via frontend action. got response ', array('response' => $aMatches[0]));
            $aResponse = json_decode($aMatches[0], true);
            if (is_array($aResponse) && count(
                $aResponse
            ) > 0 && isset($aResponse['sMessageType']) && 'MESSAGE' == $aResponse['sMessageType']
            ) {
                $bSuccess = true;
            }
        } else {
            $this->getShopLogger()->error('failed sending mail via frontend action. got response', array('response' => $executeRequestResponse));
        }

        return $bSuccess;
    }
}
