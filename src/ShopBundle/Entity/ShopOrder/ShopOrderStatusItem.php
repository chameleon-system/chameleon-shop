<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

class ShopOrderStatusItem
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrderStatus|null - Belongs to status */
        private ?ShopOrderStatus $shopOrderStatus = null
        ,
        // TCMSFieldExtendedLookup
        /** @var ShopOrderItem|null - Product */
        private ?ShopOrderItem $shopOrderItem = null
        ,
        // TCMSFieldDecimal
        /** @var float - Amount */
        private float $amount = 0
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCmsident(): ?int
    {
        return $this->cmsident;
    }

    public function setCmsident(int $cmsident): self
    {
        $this->cmsident = $cmsident;

        return $this;
    }

    // TCMSFieldLookupParentID
    public function getShopOrderStatus(): ?ShopOrderStatus
    {
        return $this->shopOrderStatus;
    }

    public function setShopOrderStatus(?ShopOrderStatus $shopOrderStatus): self
    {
        $this->shopOrderStatus = $shopOrderStatus;

        return $this;
    }


    // TCMSFieldExtendedLookup
    public function getShopOrderItem(): ?ShopOrderItem
    {
        return $this->shopOrderItem;
    }

    public function setShopOrderItem(?ShopOrderItem $shopOrderItem): self
    {
        $this->shopOrderItem = $shopOrderItem;

        return $this;
    }


    // TCMSFieldDecimal
    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }


}
