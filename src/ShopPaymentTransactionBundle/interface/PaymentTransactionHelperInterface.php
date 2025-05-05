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

interface PaymentTransactionHelperInterface
{
    /**
     * @param bool $isCaptureOnShipment
     *
     * @return array key = order item id, value = amount
     */
    public function getProductsCaptureOnOrderCreation(\TdbShopOrder $order, $isCaptureOnShipment);

    /**
     * @param bool $isCaptureOnShipment
     *
     * @return array key = order item id, value = amount
     */
    public function getProductsCaptureOnShipping(\TdbShopOrder $order, $isCaptureOnShipment);

    /**
     * @return bool
     */
    public function allowProductCaptureOnShipment(\TdbShopOrderItem $orderedProduct);
}
