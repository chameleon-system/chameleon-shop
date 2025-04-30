<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

class PkgShopFooterCategory
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Main category / heading */
        private string $name = '',
        // TCMSFieldExtendedLookup
        /** @var ShopCategory|null - Product category */
        private ?ShopCategory $shopCategory = null,
        // TCMSFieldPosition
        /** @var int - Sorting */
        private int $sortOrder = 0,
        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
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

    // TCMSFieldVarchar
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    // TCMSFieldExtendedLookup
    public function getShopCategory(): ?ShopCategory
    {
        return $this->shopCategory;
    }

    public function setShopCategory(?ShopCategory $shopCategory): self
    {
        $this->shopCategory = $shopCategory;

        return $this;
    }

    // TCMSFieldPosition
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    // TCMSFieldLookupParentID
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
