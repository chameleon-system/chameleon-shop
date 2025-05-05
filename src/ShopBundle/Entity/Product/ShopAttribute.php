<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopAttribute
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldBoolean
        /** @var bool - System attributes */
        private bool $isSystemAttribute = false,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopAttributeValue> - Attribute values */
        private Collection $shopAttributeValueCollection = new ArrayCollection(),
        // TCMSFieldVarchar
        /** @var string - Internal name */
        private string $systemName = '',
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = ''
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

    // TCMSFieldBoolean
    public function isIsSystemAttribute(): bool
    {
        return $this->isSystemAttribute;
    }

    public function setIsSystemAttribute(bool $isSystemAttribute): self
    {
        $this->isSystemAttribute = $isSystemAttribute;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopAttributeValue>
     */
    public function getShopAttributeValueCollection(): Collection
    {
        return $this->shopAttributeValueCollection;
    }

    public function addShopAttributeValueCollection(ShopAttributeValue $shopAttributeValue): self
    {
        if (!$this->shopAttributeValueCollection->contains($shopAttributeValue)) {
            $this->shopAttributeValueCollection->add($shopAttributeValue);
            $shopAttributeValue->setShopAttribute($this);
        }

        return $this;
    }

    public function removeShopAttributeValueCollection(ShopAttributeValue $shopAttributeValue): self
    {
        if ($this->shopAttributeValueCollection->removeElement($shopAttributeValue)) {
            // set the owning side to null (unless already changed)
            if ($shopAttributeValue->getShopAttribute() === $this) {
                $shopAttributeValue->setShopAttribute(null);
            }
        }

        return $this;
    }

    // TCMSFieldVarchar
    public function getSystemName(): string
    {
        return $this->systemName;
    }

    public function setSystemName(string $systemName): self
    {
        $this->systemName = $systemName;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
