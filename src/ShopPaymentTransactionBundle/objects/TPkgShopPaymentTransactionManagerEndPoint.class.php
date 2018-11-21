<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentTransactionManagerEndPoint
{
    const LOG = '/logs/pkgShopPaymentTransaction.log';
    const MESSAGE_CREDIT_EXECUTED = 'TPkgShopPaymentTransaction-CREDIT-EXECUTED';
    const MESSAGE_PAYMENT_EXECUTED = 'TPkgShopPaymentTransaction-PAYMENT-EXECUTED';

    const MESSAGE_ERROR = 'TPkgShopPaymentTransaction-ERROR';
    const MESSAGE_INVALID_AMOUNT = 'TPkgShopPaymentTransaction-ERROR-INVALID-VALUE';
    const TRANSACTION_TYPE_PAYMENT = 'payment';
    const TRANSACTION_TYPE_CREDIT = 'credit';
    const TRANSACTION_TYPE_PAYMENT_REVERSAL = 'payment-reversal';
    private $order = null;

    /**
     * @param TdbShopOrder $oOrder
     */
    public function __construct(TdbShopOrder $oOrder)
    {
        $this->order = $oOrder;
    }

    /**
     * important: the transaction will go through no matter what - make sure you validate the amount using getMaxAllowedValueFor
     * before calling this method.
     *
     * @param TPkgShopPaymentTransactionData $transactionData
     *
     * @return TdbPkgShopPaymentTransaction
     */
    public function addTransaction(TPkgShopPaymentTransactionData $transactionData)
    {
        $transactionData->checkRequirements();

        $sTransactionType = $transactionData->getType();
        $amount = $transactionData->getTotalValue();
        $oContext = $transactionData->getContext();
        $iSequenceNumber = $transactionData->getSequenceNumber();

        $oTransactionType = $this->getTransactionTypeObject($sTransactionType);

        if (null === $iSequenceNumber) {
            $iSequenceNumber = $this->getNextTransactionSequenceNumber();
        }
        if (self::TRANSACTION_TYPE_CREDIT == $sTransactionType || self::TRANSACTION_TYPE_PAYMENT_REVERSAL === $sTransactionType) {
            $amount = -1 * $amount;
        }
        $aData = array(
            'shop_order_id' => $this->order->id,
            'data_extranet_user_id' => (null !== $oContext->getExtranetUser()) ? ($oContext->getExtranetUser(
            )->id) : (''),
            'cms_user_id' => (null !== $oContext->getCmsUser()) ? ($oContext->getCmsUser()->id) : (''),
            'datecreated' => date('Y-m-d H:i:s'),
            'ip' => $oContext->getIp(),
            'amount' => $amount,
            'context' => $oContext->getContext(),
            'pkg_shop_payment_transaction_type_id' => $oTransactionType->id,
            'sequence_number' => $iSequenceNumber,
            'confirmed' => '0',
        );

        $oTransaction = TdbPkgShopPaymentTransaction::GetNewInstance($aData);

        $oTransaction->AllowEditByAll(true);
        $oTransaction->SaveFieldsFast($aData);
        $oTransaction->AllowEditByAll(false);

        // add items
        $aItems = $transactionData->getItems();
        /** @var TPkgShopPaymentTransactionItemData $oItem */
        $itemAmountMultiplier = 1;
        if (self::TRANSACTION_TYPE_CREDIT == $sTransactionType || self::TRANSACTION_TYPE_PAYMENT_REVERSAL === $sTransactionType) {
            $itemAmountMultiplier = -1;
        }

        foreach ($aItems as $oItem) {
            $aData = array(
                'pkg_shop_payment_transaction_id' => $oTransaction->id,
                'amount' => ($itemAmountMultiplier * $oItem->getAmount()),
                'value' => $oItem->getValue(),
                'shop_order_item_id' => $oItem->getOrderItemId(),
                'type' => $oItem->getType(),
            );
            $oTransactionPosition = TdbPkgShopPaymentTransactionPosition::GetNewInstance($aData);
            $oTransactionPosition->AllowEditByAll(true);
            $oTransactionPosition->SaveFieldsFast($aData);
        }

        if (true === $transactionData->getConfirmed()) {
            $this->confirmTransaction($iSequenceNumber, $transactionData->getConfirmedTimestamp());
        }

        return $oTransaction;
    }

    /**
     * @param TdbPkgShopPaymentTransaction $transaction
     */
    public function deleteTransaction($transaction)
    {
        $transaction->AllowEditByAll(true);
        $transaction->Delete();
    }

    /**
     * searches for a transaction with matching id number and confirms it. returns the transaction if found, null if not.
     *
     * @param $transactionId
     * @param $iConfirmedDate
     *
     * @return TdbPkgShopPaymentTransaction
     */
    public function confirmTransactionById($transactionId, $iConfirmedDate)
    {
        $oTransaction = TdbPkgShopPaymentTransaction::GetNewInstance();
        if (false === $oTransaction->Load($transactionId)) {
            return null;
        }

        return $this->confirmTransactionObject($oTransaction, $iConfirmedDate);
    }

    /**
     * searches for a transaction with matching sequence number and confirms it. returns the transaction if found, null if not.
     *
     * @param int $iSequenceNumber
     * @param int $iConfirmedDate  - unix timestamp
     *
     * @return null|\TdbPkgShopPaymentTransaction
     */
    public function confirmTransaction($iSequenceNumber, $iConfirmedDate)
    {
        $oTransaction = TdbPkgShopPaymentTransaction::GetNewInstance();
        $loadData = array('shop_order_id' => $this->order->id, 'sequence_number' => $iSequenceNumber);
        if (true === $oTransaction->LoadFromFields($loadData)) {
            $oTransaction = $this->confirmTransactionObject($oTransaction, $iConfirmedDate);
        } else {
            $oTransaction = null;
        }

        return $oTransaction;
    }

    /**
     * confirm given transaction object.
     *
     * @param TdbPkgShopPaymentTransaction $transaction
     * @param $iConfirmedDate
     *
     * @return TdbPkgShopPaymentTransaction
     */
    public function confirmTransactionObject(TdbPkgShopPaymentTransaction $transaction, $iConfirmedDate)
    {
        if (false === $transaction->fieldConfirmed) {
            $transaction->AllowEditByAll(true);
            $transaction->SaveFieldsFast(
                array('confirmed' => '1', 'confirmed_date' => date('Y-m-d H:i:s', $iConfirmedDate))
            );
            $transaction->AllowEditByAll(false);
            // mark order as paid/unpaid depending on the remaining balance
            $this->updateOrderPaidStatusBasedOnCurrentBalance();
            $this->updateRealUsedVoucherValueBasedOnTransactions($transaction);
        }

        return $transaction;
    }

    /**
     * @param $sTransactionType
     *
     * @return TdbPkgShopPaymentTransactionType
     *
     * @throws TPkgShopPaymentTransactionException_InvalidTransactionType
     */
    private function getTransactionTypeObject($sTransactionType)
    {
        static $aTypeCache = array();
        if (false === isset($aTypeCache[$sTransactionType])) {
            $oTransactionType = TdbPkgShopPaymentTransactionType::GetNewInstance();
            if (false === $oTransactionType->LoadFromField('system_name', $sTransactionType)) {
                $sMessage = "transaction type {$sTransactionType} does not exists in DB";
                throw new TPkgShopPaymentTransactionException_InvalidTransactionType(
                    self::MESSAGE_ERROR, array('sMessage' => $sMessage),
                    $sMessage,
                    array(
                        'order' => $this->order->sqlData,
                        'type' => $sTransactionType,
                    ),
                    1,
                    self::LOG
                );
            }
            $aTypeCache[$sTransactionType] = $oTransactionType;
        }

        return $aTypeCache[$sTransactionType];
    }

    /**
     * changes the paid status of the order (if required) based on the current balance
     * returns true if the state of the order was changed.
     *
     * @return bool
     */
    protected function updateOrderPaidStatusBasedOnCurrentBalance()
    {
        $bOrderPaidStatusChanged = false;
        $bIsPaid = $this->getTransactionBalance(false) >= $this->order->fieldValueTotal;

        if (true == $bIsPaid && false === $this->order->fieldOrderIsPaid) {
            $this->order->SetStatusPaid(true);
            $bOrderPaidStatusChanged = true;
        }

        return $bOrderPaidStatusChanged;
    }

    private function updateRealUsedVoucherValueBasedOnTransactions(TdbPkgShopPaymentTransaction $transaction): void
    {
        $oTransactionData = $this->getTransactionDataFromOrder(
            TPkgShopPaymentTransactionData::TYPE_PAYMENT,
            null,
            true
        );
        $valueToCapture = $oTransactionData->getTotalValueForItemType(TPkgShopPaymentTransactionItemData::TYPE_PRODUCT);

        // There are products no captured yet. So no voucher handling is needed because not captured products can use open/refunded voucher values.
        if ($valueToCapture > 0) {
            return;
        }

        $realVoucherValueUsed = $this->getTransactionPositionTotalForType(
                TPkgShopPaymentTransactionItemData::TYPE_VOUCHER,
                true
            ) * -1;
        $shopOrder = $transaction->GetFieldShopOrder();
        if (null === $shopOrder) {
            return;
        }

        $voucherUsedDifference = $shopOrder->fieldValueVouchers - $realVoucherValueUsed;
        if ($voucherUsedDifference > 0) {
            $voucherUseList = $shopOrder->GetFieldShopVoucherUseList();
            while ($voucherUse = $voucherUseList->Next()) {
                $newVoucherUsedValue = $voucherUse->fieldValueUsed - $voucherUsedDifference;
                if ($newVoucherUsedValue < 0) {
                    $newVoucherUsedValue = 0;
                    $voucherUsedDifference = $voucherUsedDifference - $voucherUse->fieldValueUsed;
                } else {
                    $voucherUsedDifference = 0;
                }
                $voucherUse->AllowEditByAll(true);
                $voucherUse->SaveFieldsFast(['value_used' => $newVoucherUsedValue]);
                $voucherUse->AllowEditByAll(false);
                $voucher = $voucherUse->GetFieldShopVoucher();
                if (null === $voucher) {
                    return;
                }
                $voucher->AllowEditByAll(true);
                if (true === $voucher->fieldIsUsedUp) {
                    $voucher->AllowEditByAll(true);
                    $voucher->SaveFieldsFast(['is_used_up' => '0', 'date_used_up' => '0000-00-00 00:00:00']);
                    $voucher->AllowEditByAll(true);
                }
                if (0 === $voucherUsedDifference) {
                    break;
                }
            }
        }
    }

    /**
     * returns the balance of all transactions for an order (without the order value).
     *
     * @param bool $bIncludeUnconfirmedTransactions
     *
     * @return float
     */
    public function getTransactionBalance($bIncludeUnconfirmedTransactions = false)
    {
        $dTotal = 0.00;
        $sRestriction = '';
        if (false === $bIncludeUnconfirmedTransactions) {
            $sRestriction .= " AND `pkg_shop_payment_transaction`.`confirmed` = '1'";
        }
        $query = "SELECT SUM(`pkg_shop_payment_transaction`.`amount`) AS total
                    FROM `pkg_shop_payment_transaction`
                   WHERE `pkg_shop_payment_transaction`.`shop_order_id` = '".MySqlLegacySupport::getInstance(
            )->real_escape_string(
                $this->order->id
            )."'
                    {$sRestriction}
                GROUP BY `pkg_shop_payment_transaction`.`shop_order_id`
                 ";
        if ($aSum = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $dTotal = $aSum['total'];
        }

        return (float) round($dTotal, 2);
    }

    /**
     * returns the transaction sequence for the next transaction.
     *
     * @return int
     */
    protected function getNextTransactionSequenceNumber()
    {
        $query = "SELECT MAX(sequence_number) AS max_sequence_number
                    FROM `pkg_shop_payment_transaction`
                   WHERE `pkg_shop_payment_transaction`.`shop_order_id` = '".MySqlLegacySupport::getInstance(
            )->real_escape_string(
                $this->order->id
            )."'
                GROUP BY `pkg_shop_payment_transaction`.`shop_order_id`
                 ";
        if ($aSum = MySqlLegacySupport::getInstance()->fetch_assoc(MySqlLegacySupport::getInstance()->query($query))) {
            $iSequenceNumber = $aSum['max_sequence_number'] + 1;
        } else {
            $iSequenceNumber = 1;
        }

        return $iSequenceNumber;
    }

    /**
     * the max value is always an absolute value (ie positive).
     *
     * @param string $sTransactionType - must be one of self::TRANSACTION_TYPE_*
     *
     * @return float
     */
    public function getMaxAllowedValueFor($sTransactionType)
    {
        $dMaxValue = 0;
        switch ($sTransactionType) {
            case self::TRANSACTION_TYPE_PAYMENT:
                $currentTransactionBalance = $this->getTransactionBalance(true);
                $dMaxValue = $this->order->fieldValueTotal - $currentTransactionBalance;
                break;
            case self::TRANSACTION_TYPE_PAYMENT_REVERSAL:
            case self::TRANSACTION_TYPE_CREDIT:
                $dMaxValue = $this->getTransactionBalance(true);
                break;
            default:
                break;
        }

        return $dMaxValue;
    }

    /**
     * returns true if there are transactions for the order (pending or not).
     *
     * @param null $sTransactionType - must be one of self::TRANSACTION_TYPE_*
     *
     * @return bool
     */
    public function hasTransactions($sTransactionType = null)
    {
        $iNumberOfTransactions = 0;
        $sTransactionTypeRestriction = '';
        if (null !== $sTransactionType) {
            $oTransactionType = $this->getTransactionTypeObject($sTransactionType);
            $sTransactionId = MySqlLegacySupport::getInstance()->real_escape_string($oTransactionType->id);
            $sTransactionTypeRestriction = " AND `pkg_shop_payment_transaction`.`pkg_shop_payment_transaction_type_id` = '{$sTransactionId}'";
        }
        $query = "SELECT COUNT(*) AS transactions
                    FROM `pkg_shop_payment_transaction`
                   WHERE `pkg_shop_payment_transaction`.`shop_order_id` = '".MySqlLegacySupport::getInstance(
            )->real_escape_string(
                $this->order->id
            )."'
                    {$sTransactionTypeRestriction}
                 ";
        if ($aRow = MySqlLegacySupport::getInstance()->fetch_row(MySqlLegacySupport::getInstance()->query($query))) {
            $iNumberOfTransactions = intval($aRow[0]);
        }

        return 0 !== $iNumberOfTransactions;
    }

    /**
     * returns an array with all products that have not been billed via a transaction.
     *
     * @param $bIncludeUnconfirmedTransactions
     *
     * @return array [shop_order_item_id] = amount
     */
    public function getBillableProducts($bIncludeUnconfirmedTransactions = true)
    {
        $aProductList = array();
        $oOrderItemList = $this->order->GetFieldShopOrderItemList();
        $oOrderItemList->GoToStart();
        while ($oOrderItem = $oOrderItemList->Next()) {
            $iBilled = $this->getProductAmountWithTransactionType(
                $oOrderItem->id,
                self::TRANSACTION_TYPE_PAYMENT,
                $bIncludeUnconfirmedTransactions
            );
            $iRemaining = round($oOrderItem->fieldOrderAmount - $iBilled);
            if ($iRemaining > 0) {
                $aProductList[$oOrderItem->id] = $iRemaining;
            }
        }
        $oOrderItemList->GoToStart();

        return $aProductList;
    }

    /**
     * returns an array of all products that have been billed and not refunded.
     *
     * @param $bIncludeUnconfirmedTransactions
     *
     * @return array [shop_order_item_id] = amount
     */
    public function getRefundableProducts($bIncludeUnconfirmedTransactions = true)
    {
        $aProductList = array();
        $oOrderItemList = $this->order->GetFieldShopOrderItemList();
        $oOrderItemList->GoToStart();
        while ($oOrderItem = $oOrderItemList->Next()) {
            $iBilled = $this->getProductAmountWithTransactionType(
                $oOrderItem->id,
                self::TRANSACTION_TYPE_PAYMENT,
                false
            );
            $iRefunded = $this->getProductAmountWithTransactionType(
                $oOrderItem->id,
                self::TRANSACTION_TYPE_CREDIT,
                $bIncludeUnconfirmedTransactions
            );
            $iRefunded += $this->getProductAmountWithTransactionType(
                $oOrderItem->id,
                self::TRANSACTION_TYPE_PAYMENT_REVERSAL,
                $bIncludeUnconfirmedTransactions
            );
            $iRefundable = round($iBilled + $iRefunded);
            if ($iRefundable > 0) {
                $aProductList[$oOrderItem->id] = $iRefundable;
            }
        }
        $oOrderItemList->GoToStart();

        return $aProductList;
    }

    /**
     * returns true if all products are refundable. That is the case if.
     *
     * a) no product has been refunded
     * b) ALL products have been paid
     */
    public function allProductsAreRefundable()
    {
        $bProductsHaveBeenRefunded = $this->hasTransactions(self::TRANSACTION_TYPE_PAYMENT_REVERSAL)
            || $this->hasTransactions(self::TRANSACTION_TYPE_CREDIT);
        if (true === $bProductsHaveBeenRefunded) {
            return false;
        }

        if ($this->order->fieldValueTotal == $this->getMaxAllowedValueFor(self::TRANSACTION_TYPE_CREDIT)) {
            return true;
        }

        return false;
    }

    /**
     * @param $sOrderItemId
     * @param string $sTransactionTypeSystemName      - should be one of self::TRANSACTION_TYPE_*
     * @param bool   $bIncludeUnconfirmedTransactions
     *
     * @return int
     */
    public function getProductAmountWithTransactionType(
        $sOrderItemId,
        $sTransactionTypeSystemName,
        $bIncludeUnconfirmedTransactions = true
    ) {
        $sTransactionTypeId = $this->getTransactionTypeObject($sTransactionTypeSystemName)->id;
        $iTotal = 0;
        $sOrderItemId = MySqlLegacySupport::getInstance()->real_escape_string($sOrderItemId);
        $sTransactionTypeId = MySqlLegacySupport::getInstance()->real_escape_string($sTransactionTypeId);
        $sOrderId = MySqlLegacySupport::getInstance()->real_escape_string($this->order->id);
        $sIncompleteRestriction = '';
        if (false === $bIncludeUnconfirmedTransactions) {
            $sIncompleteRestriction = " AND `pkg_shop_payment_transaction`.`confirmed` = '1'";
        }
        $query = "SELECT SUM(`pkg_shop_payment_transaction_position`.`amount`) AS amount_total
                    FROM `pkg_shop_payment_transaction_position`
              INNER JOIN `pkg_shop_payment_transaction` ON `pkg_shop_payment_transaction_position`.`pkg_shop_payment_transaction_id` = `pkg_shop_payment_transaction`.`id`
                   WHERE `pkg_shop_payment_transaction_position`.`shop_order_item_id` = '{$sOrderItemId}'
                     AND `pkg_shop_payment_transaction`.`shop_order_id` = '{$sOrderId}'
                     AND `pkg_shop_payment_transaction`.`pkg_shop_payment_transaction_type_id` = '{$sTransactionTypeId}'
                     {$sIncompleteRestriction}
                GROUP BY `pkg_shop_payment_transaction`.`shop_order_id`
        ";
        if ($aRow = MySqlLegacySupport::getInstance()->fetch_row(MySqlLegacySupport::getInstance()->query($query))) {
            $iTotal = intval($aRow[0]);
        }

        return $iTotal;
    }

    /**
     * return the sum for the non-product entry in the positions with position type $sTransactionPositionType.
     *
     * @param $sTransactionPositionType - must be one of TPkgShopPaymentTransactionItemData::TYPE_
     * @param bool $bIncludeUnconfirmedTransactions
     * @param null $sShopOrderItemId
     */
    public function getTransactionPositionTotalForType(
        $sTransactionPositionType,
        $bIncludeUnconfirmedTransactions = true
    ) {
        $iTotal = 0;
        $sOrderId = MySqlLegacySupport::getInstance()->real_escape_string($this->order->id);
        $sIncompleteRestriction = '';
        if (false === $bIncludeUnconfirmedTransactions) {
            $sIncompleteRestriction = " AND `pkg_shop_payment_transaction`.`confirmed` = '1'";
        }
        $sTransactionPositionType = MySqlLegacySupport::getInstance()->real_escape_string($sTransactionPositionType);
        $query = "SELECT SUM(`pkg_shop_payment_transaction_position`.`amount` * `pkg_shop_payment_transaction_position`.`value`) AS value_total
                    FROM `pkg_shop_payment_transaction_position`
              INNER JOIN `pkg_shop_payment_transaction` ON `pkg_shop_payment_transaction_position`.`pkg_shop_payment_transaction_id` = `pkg_shop_payment_transaction`.`id`
                   WHERE `pkg_shop_payment_transaction_position`.`type` = '{$sTransactionPositionType}'
                     AND `pkg_shop_payment_transaction`.`shop_order_id` = '{$sOrderId}'
                     {$sIncompleteRestriction}
                GROUP BY `pkg_shop_payment_transaction`.`shop_order_id`
        ";
        if ($aRow = MySqlLegacySupport::getInstance()->fetch_row(MySqlLegacySupport::getInstance()->query($query))) {
            $iTotal = $aRow[0];
        }

        return round($iTotal, 2);
    }

    /**
     * return the transaction details for a completed order.
     *
     * @param string $sTransactionType          - must be one of TPkgShopPaymentTransactionData::TYPE_*
     * @param null   $aProductAmountRestriction
     *
     * @return TPkgShopPaymentTransactionData
     */
    public function getTransactionDataFromOrder(
        $sTransactionType = TPkgShopPaymentTransactionData::TYPE_PAYMENT,
        $aProductAmountRestriction = null,
        $bUseConfirmedTransactionsOnly = true
    ) {
        $dSignMultiplier = 1;
        if (TPkgShopPaymentTransactionData::TYPE_PAYMENT === $sTransactionType) {
            $aItemBaseToUse = $this->getBillableProducts($bUseConfirmedTransactionsOnly);
        } else {
            $aItemBaseToUse = $this->getRefundableProducts($bUseConfirmedTransactionsOnly);
        }

        $oTransactionData = new TPkgShopPaymentTransactionData($this->order, $sTransactionType);
        $dOrderTotal = 0;
        $dProductValue = 0;
        $bAllRemainingItemsSelected = true;
        $dDiscount = 0;

        $orderItems = $this->order->GetFieldShopOrderItemList();
        foreach ($aItemBaseToUse as $sShopOrderItemId => $iAmountAllowedForUse) {
            $iAmountForTransaction = $iAmountAllowedForUse;
            if (null !== $aProductAmountRestriction) {
                if (false === isset($aProductAmountRestriction[$sShopOrderItemId])) {
                    $bAllRemainingItemsSelected = false; // some items were skipped
                    continue;
                }

                $iAmountForTransaction = $dSignMultiplier * $aProductAmountRestriction[$sShopOrderItemId];

                if ($iAmountForTransaction > $iAmountAllowedForUse) {
                    $sMsg = "trying to use {$iAmountForTransaction} from item {$sShopOrderItemId} when only {$iAmountAllowedForUse} are available";
                    throw new TPkgCmsException_LogAndMessage(
                        TPkgShopPaymentTransactionManager::MESSAGE_ERROR,
                        array('sMessage' => $sMsg),
                        $sMsg,
                        array('order' => $this->order->sqlData)
                    );
                }
            }
            $oOrderItem = $orderItems->FindItemWithProperty('id', $sShopOrderItemId);
            if (false === $oOrderItem) {
                $sMsg = "unable to load the shop_order_item [{$this->order->id}] in order [{$sShopOrderItemId}]";
                throw new TPkgCmsException_LogAndMessage(
                    TPkgShopPaymentTransactionManager::MESSAGE_ERROR,
                    array('sMessage' => $sMsg),
                    $sMsg,
                    array('order' => $this->order->sqlData)
                );
            }

            //$totalQuantityForTransaction = $this->gettot
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_PRODUCT)
                ->setAmount($dSignMultiplier * $iAmountForTransaction)
                ->setOrderItemId($sShopOrderItemId)
                ->setValue($oOrderItem->fieldOrderPrice);
            if (0 != $oItem->getAmount()) {
                $oTransactionData->addItem($oItem);
                $dOrderTotal += $oItem->getAmount() * $oItem->getValue();
                $dProductValue += $oItem->getAmount() * $oItem->getValue();
                $dDiscount += round(
                        (($oOrderItem->fieldOrderPriceAfterDiscounts / $oOrderItem->fieldOrderAmount) * $iAmountForTransaction),
                        2
                    ) - ($dSignMultiplier * $oItem->getAmount() * $oItem->getValue());
            }
            $bAllRemainingItemsSelected = $bAllRemainingItemsSelected && ($iAmountForTransaction == $iAmountAllowedForUse);
        }

        if (true === $bAllRemainingItemsSelected && TPkgShopPaymentTransactionData::TYPE_PAYMENT === $sTransactionType) {
            // use the complete remaining discount
            $dOriginalDiscount = -1 * ($this->order->fieldValueVouchersNotSponsored + $this->order->fieldValueDiscounts);
            $dDiscountUsed = $this->getTransactionPositionTotalForType(
                    TPkgShopPaymentTransactionItemData::TYPE_DISCOUNT,
                    true
                )
                + $this->getTransactionPositionTotalForType(
                    TPkgShopPaymentTransactionItemData::TYPE_DISCOUNT_VOUCHER,
                    true
                );
            $dRemainingDiscount = $dOriginalDiscount - $dDiscountUsed;
            $dDiscount = $dRemainingDiscount;
        }

        if ($dDiscount > 0 || $dDiscount < 0) {
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_DISCOUNT)
                ->setAmount($dSignMultiplier)
                ->setValue($dDiscount);
            $oTransactionData->addItem($oItem);
            $dOrderTotal = $dOrderTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        // shipping, payment, vouchers and "other" should always be used as quickly as possible
        $dShippingPrice = $this->order->fieldShopShippingGroupPrice - $this->getTransactionPositionTotalForType(
                TPkgShopPaymentTransactionItemData::TYPE_SHIPPING,
                true
            );
        if ($dShippingPrice > 0 || $dShippingPrice < 0) {
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_SHIPPING)
                ->setAmount($dSignMultiplier)
                ->setValue($dShippingPrice);
            $oTransactionData->addItem($oItem);
            $dOrderTotal = $dOrderTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        $dPaymentMethodPrice = $this->order->fieldShopPaymentMethodPrice - $this->getTransactionPositionTotalForType(
                TPkgShopPaymentTransactionItemData::TYPE_PAYMENT,
                true
            );
        if ($dPaymentMethodPrice > 0 || $dPaymentMethodPrice < 0) {
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_PAYMENT)
                ->setAmount($dSignMultiplier)
                ->setValue($dPaymentMethodPrice);
            $oTransactionData->addItem($oItem);
            $dOrderTotal = $dOrderTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        $voucherValueUsed = $this->getTransactionPositionTotalForType(
            TPkgShopPaymentTransactionItemData::TYPE_VOUCHER,
            true
        );
        $dVoucher = (-1 * $this->order->fieldValueVouchers) - $voucherValueUsed;
        if (TPkgShopPaymentTransactionData::TYPE_CREDIT === $sTransactionType) {
            $maxAmountAllowedForCredit = $this->getMaxAllowedValueFor(self::TRANSACTION_TYPE_CREDIT);
            if ($dOrderTotal > $maxAmountAllowedForCredit && $voucherValueUsed * -1 > 0) {
                $dVoucher = ($dOrderTotal - $maxAmountAllowedForCredit) * -1;
                if ($dVoucher < $voucherValueUsed) {
                    $dVoucher = $voucherValueUsed;
                }
            }
        }

        if ($dVoucher * -1 > $dOrderTotal) {
            $dVoucher = $dOrderTotal * -1;
        }

        if ($dVoucher > 0 || $dVoucher < 0) {
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_VOUCHER)
                ->setAmount($dSignMultiplier)
                ->setValue($dVoucher);
            $oTransactionData->addItem($oItem);
            $dOrderTotal = $dOrderTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        $dExpectedTotal = $dSignMultiplier * $this->getOrderTotalOtherValue();

        $dOther = $dExpectedTotal - $this->getTransactionPositionTotalForType(
                TPkgShopPaymentTransactionItemData::TYPE_OTHER,
                true
            );

        if ($dOther > 0 || $dOther < 0) {
            $oItem = new TPkgShopPaymentTransactionItemData();
            $oItem
                ->setType(TPkgShopPaymentTransactionItemData::TYPE_OTHER)
                ->setAmount($dSignMultiplier)
                ->setValue($dOther);
            $oTransactionData->addItem($oItem);
            $dOrderTotal = $dOrderTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        $oTransactionData->setTotalValue($dOrderTotal);

        return $oTransactionData;
    }

    private function getOrderTotalOtherValue()
    {
        // return the "other" value of the original order
        $dOrderTotal = $this->order->fieldValueTotal;
        // now remove all "know" quantities
        $dOrderTotal = $dOrderTotal - $this->order->fieldValueArticle;
        $dOrderTotal = $dOrderTotal + $this->order->fieldValueDiscounts + $this->order->fieldValueVouchersNotSponsored;
        $dOrderTotal = $dOrderTotal - $this->order->fieldShopShippingGroupPrice;
        $dOrderTotal = $dOrderTotal - $this->order->fieldShopPaymentMethodPrice;
        $dOrderTotal = $dOrderTotal + $this->order->fieldValueVouchers;
        $dOrderTotal = round($dOrderTotal, 2);

        return $dOrderTotal;
    }
}
