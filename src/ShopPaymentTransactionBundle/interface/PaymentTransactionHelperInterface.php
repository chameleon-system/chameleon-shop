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

use TdbShopOrder;
use TdbShopOrderItem;

interface PaymentTransactionHelperInterface
{
    /**
     * @param TdbShopOrder $order
     * @param bool         $isCaptureOnShipment
     *
     * @return array key = order item id, value = amount
     */
    public function getProductsCaptureOnOrderCreation(TdbShopOrder $order, $isCaptureOnShipment);

    /**
     * @param TdbShopOrder $order
     * @param bool         $isCaptureOnShipment
     *
     * @return array key = order item id, value = amount
     */
    public function getProductsCaptureOnShipping(TdbShopOrder $order, $isCaptureOnShipment);

    /**
     * @param TdbShopOrderItem $orderedProduct
     *
     * @return bool
     */
    public function allowProductCaptureOnShipment(TdbShopOrderItem $orderedProduct);
}
