<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentTransactionItemDataEndPoint
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_VOUCHER = 'voucher';
    public const TYPE_DISCOUNT_VOUCHER = 'discount-voucher';
    public const TYPE_DISCOUNT = 'discount';
    public const TYPE_SHIPPING = 'shipping';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_OTHER = 'other'; // everything that is not covered by one of the other options

    /**
     * @var int
     */
    private $amount;
    /**
     * @var float
     */
    private $value;
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $orderItemId;

    /**
     * @var TdbShopOrderItem|null
     */
    private $orderItem;

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
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
     * @param string $orderItemId
     *
     * @return $this
     */
    public function setOrderItemId($orderItemId)
    {
        $this->orderItemId = $orderItemId;

        return $this;
    }

    /**
     * @param string $type - must be one of self::TYPE_*
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * @return TdbShopOrderItem|null
     *
     * @throws TPkgCmsException_LogAndMessage
     */
    public function getOrderItem()
    {
        if (null === $this->orderItem && null !== $this->getOrderItemId()) {
            $oOrderItem = TdbShopOrderItem::GetNewInstance();
            if (false === $oOrderItem->Load($this->getOrderItemId())) {
                $sMsg = "unable to load the shop_order_item [{$this->getOrderItemId()}]";
                throw new TPkgCmsException_LogAndMessage(
                    TPkgShopPaymentTransactionManager::MESSAGE_ERROR,
                    ['sMessage' => $sMsg],
                    $sMsg,
                    ['item' => $this]
                );
            } else {
                $this->orderItem = $oOrderItem;
            }
        }

        return $this->orderItem;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
