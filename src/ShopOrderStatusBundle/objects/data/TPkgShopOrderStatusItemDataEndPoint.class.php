<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopOrderStatusItemDataEndPoint implements IPkgShopOrderStatusData
{
    /**
     * @var string
     */
    private $shopOrderItemId = null;

    /**
     * @var int
     */
    private $amount = null;

    public function __construct($shopOrderItemId, $amount)
    {
        $this->setShopOrderItemId($shopOrderItemId)->setAmount($amount);
    }

    /**
     * @return string
     */
    public function getShopOrderItemId()
    {
        return $this->shopOrderItemId;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param string $shopOrderItemId
     *
     * @return $this
     */
    public function setShopOrderItemId($shopOrderItemId)
    {
        $this->shopOrderItemId = $shopOrderItemId;

        return $this;
    }

    /**
     * returns an assoc array with the data of the object mapped to to the tdb fields.
     *
     * @return array
     */
    public function getDataAsTdbArray()
    {
        return array(
            'shop_order_status_id' => '',
            'shop_order_item_id' => $this->getShopOrderItemId(),
            'amount' => $this->getAmount(),
        );
    }
}
