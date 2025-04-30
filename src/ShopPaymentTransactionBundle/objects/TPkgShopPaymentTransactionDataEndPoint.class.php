<?php

/*
 * This file is part of the Chameleon System (https://www.chameleonsystem.com).
 *
 * (c) ESONO AG (https://www.esono.de)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class TPkgShopPaymentTransactionDataEndPoint extends AbstractPkgCmsCoreParameterContainer
{
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_CREDIT = 'credit';
    public const TYPE_PAYMENT_REVERSAL = 'payment-reversal';

    /**
     * @var TdbShopOrder
     */
    private $order;

    /**
     * @var float
     */
    private $totalValue;
    /**
     * @var string - one of self::TYPE_*
     */
    private $type;
    /**
     * @var TPkgShopPaymentTransactionContext
     */
    private $context;
    /**
     * @var bool
     */
    private $confirmed = false;
    /**
     * @var int
     */
    private $confirmedTimestamp;
    /**
     * @var int
     */
    private $sequenceNumber;
    /**
     * @var array of TPkgShopPaymentTransactionItemData
     */
    private $items = [];

    /**
     * use $this->addRequirement to add the requirements of the container.
     */
    protected function defineRequirements()
    {
        $this
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('order', true, 'TdbShopOrder'))
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('totalValue', true))
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('type', true))
            ->addRequirement(new TPkgCmsCoreParameterContainerParameterDefinition('context', true, 'TPkgShopPaymentTransactionContext'));
    }

    /**
     * @param string $type - must be one of self::TYPE_*
     */
    public function __construct(TdbShopOrder $oOrder, $type)
    {
        $this->setOrder($oOrder)
            ->setType($type);
    }

    /**
     * @param TdbShopOrder $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @param bool $confirmed
     *
     * @return $this
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * @param int $confirmedTimestamp
     *
     * @return $this
     */
    public function setConfirmedTimestamp($confirmedTimestamp)
    {
        $this->confirmedTimestamp = $confirmedTimestamp;

        return $this;
    }

    /**
     * @return $this
     */
    public function setContext(TPkgShopPaymentTransactionContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return $this
     */
    public function addItem(TPkgShopPaymentTransactionItemData $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param int $sequenceNumber
     *
     * @return $this
     */
    public function setSequenceNumber($sequenceNumber)
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }

    /**
     * @param float $totalValue
     *
     * @return $this
     */
    public function setTotalValue($totalValue)
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @return int
     */
    public function getConfirmedTimestamp()
    {
        return $this->confirmedTimestamp;
    }

    /**
     * @return TPkgShopPaymentTransactionContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * @return float
     */
    public function getTotalValue()
    {
        return $this->totalValue;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return TdbShopOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * return the total value of all items with the given item type.
     *
     * @param string $sType - must be one of TPkgShopPaymentTransactionItemData::TYPE_*
     *
     * @psalm-param TPkgShopPaymentTransactionItemData::TYPE_* $sType
     *
     * @return float
     */
    public function getTotalValueForItemType($sType)
    {
        $dTotal = 0;
        reset($this->items);
        /** @var TPkgShopPaymentTransactionItemData $oItem */
        foreach ($this->items as $oItem) {
            if ($sType !== $oItem->getType()) {
                continue;
            }
            $dTotal = $dTotal + ($oItem->getAmount() * $oItem->getValue());
        }

        return $dTotal;
    }
}
