<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

class ShopBundleArticle
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticle|null - Belongs to bundle article */
        private ?ShopArticle $shopArticle = null,
        // TCMSFieldExtendedLookup
        /** @var ShopArticle|null - Article */
        private ?ShopArticle $bundleArticle = null,
        // TCMSFieldNumber
        /** @var int - Units */
        private int $amount = 1,
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
    public function getShopArticle(): ?ShopArticle
    {
        return $this->shopArticle;
    }

    public function setShopArticle(?ShopArticle $shopArticle): self
    {
        $this->shopArticle = $shopArticle;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getBundleArticle(): ?ShopArticle
    {
        return $this->bundleArticle;
    }

    public function setBundleArticle(?ShopArticle $bundleArticle): self
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
