<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

class ShopOrderStatusItem
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrderStatus|null - Belongs to status */
        private ?ShopOrderStatus $shopOrderStatus = null,
        // TCMSFieldExtendedLookup
        /** @var ShopOrderItem|null - Product */
        private ?ShopOrderItem $shopOrderItem = null,
        // TCMSFieldDecimal
        /** @var string - Amount */
        private string $amount = ''
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
    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
