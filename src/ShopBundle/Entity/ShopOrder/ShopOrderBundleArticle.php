<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

class ShopOrderBundleArticle
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrderItem|null - Bundle articles of the order */
        private ?ShopOrderItem $shopOrderItem = null,
        // TCMSFieldExtendedLookup
        /** @var ShopOrderItem|null - Article belonging to bundle */
        private ?ShopOrderItem $bundleArticle = null,
        // TCMSFieldNumber
        /** @var int - Units */
        private int $amount = 0,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0
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
    public function getShopOrderItem(): ?ShopOrderItem
    {
        return $this->shopOrderItem;
    }

    public function setShopOrderItem(?ShopOrderItem $shopOrderItem): self
    {
        $this->shopOrderItem = $shopOrderItem;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getBundleArticle(): ?ShopOrderItem
    {
        return $this->bundleArticle;
    }

    public function setBundleArticle(?ShopOrderItem $bundleArticle): self
    {
        $this->bundleArticle = $bundleArticle;

        return $this;
    }

    // TCMSFieldNumber
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    // TCMSFieldPosition
    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }
}
