<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

class ShopAttributeValue
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopAttribute|null - Belongs to the attribute */
        private ?ShopAttribute $shopAttribute = null,
        // TCMSFieldVarchar
        /** @var string - Value */
        private string $name = '',
        // TCMSFieldPosition
        /** @var int - Sorting */
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
    public function getShopAttribute(): ?ShopAttribute
    {
        return $this->shopAttribute;
    }

    public function setShopAttribute(?ShopAttribute $shopAttribute): self
    {
        $this->shopAttribute = $shopAttribute;

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
