<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace esono\pkgshoppaymenttransaction;

interface PaymentTransactionHandlerInterface
{
    /**
     * @throws \InvalidArgumentException
     */
    public function __construct(\IPkgShopOrderPaymentConfig $config);

    /**
     * if the request results in a payment (capture) and not just in an authorization for a later capture, then the method
     * must create a transaction and return it.
     *
     * @return \TdbPkgShopPaymentTransaction|null
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function captureOrder(\TPkgShopPaymentTransactionManager $transactionManager, \TdbShopOrder $order);

    /**
     * on success a transaction is created and returned by the method.
     *
     * @param float $value - the value to capture (should be >0)
     * @param array $orderItemList - assoc array [shop_order_item_id] = [quantity]
     * @param string $invoiceNumber - 16 char id shown on the payment statement of the buy (usually the order number or the bill number). will be passed to AmazonPaymentGroupConfig::getSellerAuthorizationNote
     *
     * @return \TdbPkgShopPaymentTransaction
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function captureShipment(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder $order,
        $value,
        $invoiceNumber = null,
        ?array $orderItemList = null
    );

    /**
     * on success a transaction is created and returned by the method.
     *
     * @param float $value - the value to refund (should be >0)
     * @param string $invoiceNumber - 16 char id shown on the payment statement of the buy (usually the order number or the bill number). will be passed to AmazonPaymentGroupConfig::getSellerAuthorizationNote
     * @param string $sellerRefundNote - a reason for the refund
     * @param array $orderItemList - assoc array [shop_order_item_id] = [quantity]
     *
     * @return \TdbPkgShopPaymentTransaction[]
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function refund(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder $order,
        $value,
        $invoiceNumber = null,
        $sellerRefundNote = null,
        ?array $orderItemList = null
    );

    /**
     * cancel any pending transactions with the payment provider (if the api of the payment provider allows for that) (will NOT cancel the order in the shop itself).
     *
     * @param string $cancellationReason
     *
     * @return void
     *
     * @throws \TPkgCmsException_LogAndMessage
     */
    public function cancelOrder(
        \TPkgShopPaymentTransactionManager $transactionManager,
        \TdbShopOrder $order,
        $cancellationReason = null
    );
}
