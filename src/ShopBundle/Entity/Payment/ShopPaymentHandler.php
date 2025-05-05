<?php

namespace ChameleonSystem\ShopBundle\Entity\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopPaymentHandler
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandlerGroup|null - Belongs to payment provider */
        private ?ShopPaymentHandlerGroup $shopPaymentHandlerGroup = null,
        // TCMSFieldVarchar
        /** @var string - Internal name for payment handler */
        private string $name = '',
        // TCMSFieldBoolean
        /** @var bool - Block user selection */
        private bool $blockUserSelection = false,
        // TCMSFieldVarchar
        /** @var string - Class name */
        private string $class = '',
        // TCMSFieldOption
        /** @var string - Class type */
        private string $classType = 'Core',
        // TCMSFieldVarchar
        /** @var string - Class subtype */
        private string $classSubtype = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopPaymentHandlerParameter> - Configuration settings */
        private Collection $shopPaymentHandlerParameterCollection = new ArrayCollection()
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
    public function getShopPaymentHandlerGroup(): ?ShopPaymentHandlerGroup
    {
        return $this->shopPaymentHandlerGroup;
    }

    public function setShopPaymentHandlerGroup(?ShopPaymentHandlerGroup $shopPaymentHandlerGroup): self
    {
        $this->shopPaymentHandlerGroup = $shopPaymentHandlerGroup;

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
    public function isBlockUserSelection(): bool
    {
        return $this->blockUserSelection;
    }

    public function setBlockUserSelection(bool $blockUserSelection): self
    {
        $this->blockUserSelection = $blockUserSelection;

        return $this;
    }

    // TCMSFieldVarchar
    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    // TCMSFieldOption
    public function getClassType(): string
    {
        return $this->classType;
    }

    public function setClassType(string $classType): self
    {
        $this->classType = $classType;

        return $this;
    }

    // TCMSFieldVarchar
    public function getClassSubtype(): string
    {
        return $this->classSubtype;
    }

    public function setClassSubtype(string $classSubtype): self
    {
        $this->classSubtype = $classSubtype;

        return $this;
    }

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopPaymentHandlerParameter>
     */
    public function getShopPaymentHandlerParameterCollection(): Collection
    {
        return $this->shopPaymentHandlerParameterCollection;
    }

    public function addShopPaymentHandlerParameterCollection(ShopPaymentHandlerParameter $shopPaymentHandlerParameter
    ): self {
        if (!$this->shopPaymentHandlerParameterCollection->contains($shopPaymentHandlerParameter)) {
            $this->shopPaymentHandlerParameterCollection->add($shopPaymentHandlerParameter);
            $shopPaymentHandlerParameter->setShopPaymentHandler($this);
        }

        return $this;
    }

    public function removeShopPaymentHandlerParameterCollection(ShopPaymentHandlerParameter $shopPaymentHandlerParameter
    ): self {
        if ($this->shopPaymentHandlerParameterCollection->removeElement($shopPaymentHandlerParameter)) {
            // set the owning side to null (unless already changed)
            if ($shopPaymentHandlerParameter->getShopPaymentHandler() === $this) {
                $shopPaymentHandlerParameter->setShopPaymentHandler(null);
            }
        }

        return $this;
    }
}
