<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentTransactionMapper_CollectionFormForOrder extends AbstractViewMapper
{
    /**
     * A mapper has to specify its requirements by providing th passed MapperRequirements instance with the
     * needed information and returning it.
     *
     * example:
     *
     * $oRequirements->NeedsSourceObject("foo",'stdClass','default-value');
     * $oRequirements->NeedsSourceObject("bar");
     * $oRequirements->NeedsMappedValue("baz");
     *
     * @param IMapperRequirementsRestricted $oRequirements
     */
    public function GetRequirements(IMapperRequirementsRestricted $oRequirements)
    {
        $oRequirements->NeedsSourceObject('order', 'TdbShopOrder');
        $oRequirements->NeedsSourceObject('paymentType'); // must be one of TPkgShopPaymentTransactionData::TYPE_*
    }

    /**
     * To map values from models to views the mapper has to implement iVisitable.
     * The ViewRender will pass a prepared MapperVisitor instance to the mapper.
     *
     * The mapper has to fill the values it is responsible for in the visitor.
     *
     * example:
     *
     * $foo = $oVisitor->GetSourceObject("foomodel")->GetFoo();
     * $oVisitor->SetMapperValue("foo", $foo);
     *
     *
     * To be able to access the desired source object in the visitor, the mapper has
     * to declare this requirement in its GetRequirements method (see IViewMapper)
     *
     * @param \IMapperVisitorRestricted     $oVisitor
     * @param bool                          $bCachingEnabled      - if set to true, you need to define your cache trigger that invalidate the view rendered via mapper. if set to false, you should NOT set any trigger
     * @param IMapperCacheTriggerRestricted $oCacheTriggerManager
     */
    public function Accept(
        IMapperVisitorRestricted $oVisitor,
        $bCachingEnabled,
        IMapperCacheTriggerRestricted $oCacheTriggerManager
    ) {
        /** @var TdbShopOrder $order */
        $order = $oVisitor->GetSourceObject('order');

        $sTransactionType = $oVisitor->GetSourceObject('paymentType');
        $sHeadline = '';
        switch ($sTransactionType) {
            case TPkgShopPaymentTransactionData::TYPE_PAYMENT:
                $sHeadline = TGlobal::Translate('chameleon_system_shop_payment_transaction.collection_form.headline_payment');
                break;
            case TPkgShopPaymentTransactionData::TYPE_CREDIT:
            case TPkgShopPaymentTransactionData::TYPE_PAYMENT_REVERSAL:
                $sHeadline = TGlobal::Translate('chameleon_system_shop_payment_transaction.collection_form.headline_refund');
                break;
            default:
                $sHeadline = TGlobal::Translate('chameleon_system_shop_payment_transaction.error.invalid_transaction_type', array('%sTransactionType%' => $sTransactionType));
                break;
        }

        $oVisitor->SetMappedValue('sHeadline', $sHeadline);

        $oTransactionManager = new TPkgShopPaymentTransactionManager($order);

        //$sTransactionType
        $oTransactionData = $oTransactionManager->getTransactionDataFromOrder($sTransactionType, null, false);

        $aItemList = array();

        $aItems = $oTransactionData->getItems();
        foreach ($aItems as $oItem) {
            /** @var TPkgShopPaymentTransactionItemData $oItem */
            if (TPkgShopPaymentTransactionItemData::TYPE_PRODUCT !== $oItem->getType()) {
                continue;
            }
            $aItem = $this->getItemDetails($order, $oTransactionManager, $oItem);
            $aItemList[] = $aItem;
        }

        $oVisitor->SetMappedValue('items', $aItemList);

        $aSummaryData = array(
            'valueProducts' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_PRODUCT),
            'valueDiscount' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_DISCOUNT),
            'valueDiscountVouchers' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_DISCOUNT_VOUCHER),
            'valueShipping' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_SHIPPING),
            'valuePayment' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_PAYMENT),
            'valueOther' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_OTHER),
            'valueVoucher' => $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_VOUCHER),
            'valueGrandTotal' => $oTransactionData->getTotalValue(),
            'order_total' => $order->fieldValueTotal,
        );

        $orderTransactionList = $order->GetFieldPkgShopPaymentTransactionList();
        $orderTransactionList->ChangeOrderBy(array('datecreated' => 'ASC'));
        $transactionList = array();
        $sum = 0;
        $local = TCMSLocal::GetActive();
        while ($transaction = $orderTransactionList->Next()) {
            $sum += $transaction->fieldAmount;
            $transactionList[] = array(
                'date' => $local->FormatDate($transaction->fieldDatecreated),
                'amount' => $transaction->fieldAmountFormated,
                'confirmed' => $transaction->fieldConfirmed,
                'type' => $transaction->GetFieldPkgShopPaymentTransactionType()->fieldName,
                'sum' => $local->FormatNumber($sum, 2),
                'orderBalance' => $local->FormatNumber(($order->fieldValueTotal - $sum), 2),
            );
        }

        $oVisitor->SetMappedValueFromArray($aSummaryData);

        $oVisitor->SetMappedValue('transactionList', $transactionList);
        $oVisitor->SetMappedValue('sTargetURL', URL_CMS_CONTROLLER);
        $sHiddenFields = TTools::GetArrayAsFormInput(
            array(
                 'module_fnc' => array('contentmodule' => 'pkgShopPaymentTransaction_PartialDebit'),
                 '_noModuleFunction' => 'true',
                 'pagedef' => 'tableeditor',
                 'tableid' => $order->GetTableConf()->id,
                 'id' => $order->id,
                 'debitType' => $sTransactionType,
            )
        );
        $oVisitor->SetMappedValue('sHiddenFields', $sHiddenFields);

        if (0 != $order->fieldValueVouchers) {
            $oVisitor->SetMappedValue('bHasSponsoredVouchers', true);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getItemDetails(
        TdbShopOrder $order,
        TPkgShopPaymentTransactionManager $oTransactionManager,
        TPkgShopPaymentTransactionItemData $oTransactionItem
    ) {
        $shopOrderItem = $oTransactionItem->getOrderItem();
        if ($shopOrderItem->fieldShopOrderId !== $order->id) {
            $sMsg = 'unable to load the shop_order_item ['.$shopOrderItem->id."] in order [{$order->id}]";
            throw new TPkgCmsException_LogAndMessage(
                TPkgShopPaymentTransactionManager::MESSAGE_ERROR,
                array('sMessage' => $sMsg),
                $sMsg,
                array('order' => $order)
            );
        }

        $iQuantityPayment = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT,
            true
        );
        $iQuantityPaymentConfirmed = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT,
            false
        );
        $iQuantityCredit = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_CREDIT,
            true
        );
        $iQuantityCreditConfirmed = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_CREDIT,
            false
        );
        $iQuantityPaymentRefund = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT_REVERSAL,
            true
        );
        $iQuantityPaymentRefundConfirmed = $oTransactionManager->getProductAmountWithTransactionType(
            $oTransactionItem->getOrderItemId(),
            TPkgShopPaymentTransactionManager::TRANSACTION_TYPE_PAYMENT_REVERSAL,
            false
        );

        $totalQuantityCanceled = $iQuantityCredit + $iQuantityPaymentRefund;
        if ($totalQuantityCanceled < 0) {
            $totalQuantityCanceled = $totalQuantityCanceled * -1;
        }

        $totalQuantityCanceledConfirmed = $iQuantityCreditConfirmed + $iQuantityPaymentRefundConfirmed;
        if ($totalQuantityCanceledConfirmed < 0) {
            $totalQuantityCanceledConfirmed = $totalQuantityCanceledConfirmed * -1;
        }

        $aItem = array(
            'id' => $oTransactionItem->getOrderItemId(),
            'articlenumber' => $shopOrderItem->fieldArticlenumber,
            'name' => $shopOrderItem->fieldName,
            'totalQuantityOrdered' => intval($shopOrderItem->fieldOrderAmount),
            'totalQuantityPaid' => $iQuantityPayment,
            'totalQuantityPaidConfirmed' => $iQuantityPaymentConfirmed,
            'totalQuantityCanceled' => $totalQuantityCanceled,
            'totalQuantityCanceledConfirmed' => $totalQuantityCanceledConfirmed,
            'totalQuantityForTransaction' => $oTransactionItem->getAmount(),
            'price' => $oTransactionItem->getValue(),
            'totalValueForTransaction' => round($oTransactionItem->getAmount() * $oTransactionItem->getValue(), 2),
        );

        if ('' != $shopOrderItem->fieldNameVariantInfo) {
            $aItem['name'] = $aItem['name'].' - '.$shopOrderItem->fieldNameVariantInfo;
        }

        return $aItem;
    }
}
