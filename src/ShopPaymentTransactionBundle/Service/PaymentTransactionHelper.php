<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChameleonSystem\ShopPaymentTransactionBundle\Service;

use esono\pkgshoppaymenttransaction\PaymentTransactionHelperInterface;
use TdbShopOrder;
use TdbShopOrderItem;

class PaymentTransactionHelper implements PaymentTransactionHelperInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProductsCaptureOnOrderCreation(TdbShopOrder $order, $isCaptureOnShipment)
    {
        $orderItems = $order->GetFieldShopOrderItemList();
        $orderItems->GoToStart();
        $captureItems = array();
        while ($orderedProduct = $orderItems->Next()) {
            if (false === $isCaptureOnShipment || false === $this->allowProductCaptureOnShipment($orderedProduct)) {
                $captureItems[$orderedProduct->id] = $orderedProduct->fieldOrderAmount;
            }
        }

        return $captureItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCaptureOnShipping(TdbShopOrder $order, $isCaptureOnShipment)
    {
        if (false === $isCaptureOnShipment) {
            return array();
        }
        $orderItems = $order->GetFieldShopOrderItemList();
        $orderItems->GoToStart();
        $captureOnShippingItems = array();
        while ($orderedProduct = $orderItems->Next()) {
            if (true === $this->allowProductCaptureOnShipment($orderedProduct)) {
                $captureOnShippingItems[$orderedProduct->id] = $orderedProduct->fieldOrderAmount;
            }
        }

        return $captureOnShippingItems;
    }

    /**
     * {@inheritdoc}
     */
    public function allowProductCaptureOnShipment(TdbShopOrderItem $orderedProduct)
    {
        return false === $orderedProduct->isDownload();
    }
}
