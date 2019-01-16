<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentTransaction_TPkgShopOrderStatusManager extends TPkgShopPaymentTransaction_TPkgShopOrderStatusManagerAutoParent
{
    protected function getPostOrderStatusAddedHookList()
    {
        $aList = parent::getPostOrderStatusAddedHookList();
        $aList[] = 'triggerTransactionBasedOnStatus';

        return $aList;
    }

    protected function triggerTransactionBasedOnStatus(TdbShopOrderStatus $oStatus)
    {
        $oStatusCode = $oStatus->GetFieldShopOrderStatusCode();
        if (!$oStatusCode || empty($oStatusCode->fieldPkgShopPaymentTransactionTypeId)) {
            return;
        }

        $oTransactionType = $oStatusCode->GetFieldPkgShopPaymentTransactionType();
        if (!$oTransactionType) {
            throw new TPkgCmsException_Log('unable to finde transaction type connected to order status code',
                array('status' => $oStatus)
            );
        }

        $oOrder = $oStatus->GetFieldShopOrder();
        if (!$oOrder) {
            throw new TPkgCmsException_Log('status has no order (invalid order id or no order id set)',
                array('status' => $oStatus)
            );
        }

        /** @var $paymentHandler \esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface|\TdbShopPaymentHandler */
        $paymentHandler = $oOrder->GetPaymentHandler();

        if (false === ($paymentHandler instanceof \esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface)) {
            throw new TPkgCmsException_Log('payment handler '.get_class($paymentHandler).' must implement \esono\pkgshoppaymenttransaction\PaymentHandlerWithTransactionSupportInterface',
                array('status' => $oStatus)
            );
        }

        $oTransactionManager = new TPkgShopPaymentTransactionManager($oOrder);

        $aOrderItemRestriction = null;
        $oStatusItemList = $oStatus->GetFieldShopOrderStatusItemList();
        if ($oStatusItemList->Length() > 0) {
            $aOrderItemRestriction = array();
            while ($oStatusItem = $oStatusItemList->Next()) {
                $aOrderItemRestriction[$oStatusItem->fieldShopOrderItemId] = $oStatusItem->fieldAmount;
            }
        }

        $oTransactionData = $oTransactionManager->getTransactionDataFromOrder(
            $oTransactionType->fieldSystemName,
            $aOrderItemRestriction
        );

        $paymentTransactionHandler = $paymentHandler->paymentTransactionHandlerFactory($oOrder->fieldCmsPortalId);

        if (TPkgShopPaymentTransactionData::TYPE_PAYMENT === $oTransactionType->fieldSystemName) {
            if (true === $paymentHandler->isCaptureOnShipment()) {
                $paymentTransactionHandler->captureShipment(
                    $oTransactionManager,
                    $oOrder,
                    $oTransactionData->getTotalValue(), // we should allow a concrete value
                    null,
                    $aOrderItemRestriction
                );
            } else {
                \ChameleonSystem\CoreBundle\ServiceLocator::get('monolog.logger.order')->warning(
                    "no transaction executed for {$oOrder->id} because allowTransaction returned false for type {$oTransactionType->fieldSystemName}"
                );

                return;
            }
        } elseif (TPkgShopPaymentTransactionData::TYPE_CREDIT === $oTransactionType->fieldSystemName) {
            $paymentTransactionHandler->refund(
                $oTransactionManager,
                $oOrder,
                $oTransactionData->getTotalValue(), // we should allow a concrete value,
                null,
                null,
                $aOrderItemRestriction
            );
        }
    }
}
