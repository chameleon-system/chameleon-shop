<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

class ShopOrderVat
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopOrder|null - Belongs to order */
        private ?ShopOrder $shopOrder = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldDecimal
        /** @var float - Percent */
        private float $vatPercent = 0,
        // TCMSFieldDecimal
        /** @var float - Value for order */
        private float $value = 0
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
    public function getShopOrder(): ?ShopOrder
    {
        return $this->shopOrder;
    }

    public function setShopOrder(?ShopOrder $shopOrder): self
    {
        $this->shopOrder = $shopOrder;

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


    // TCMSFieldDecimal
    public function getVatPercent(): float
    {
        return $this->vatPercent;
    }

    public function setVatPercent(float $vatPercent): self
    {
        $this->vatPercent = $vatPercent;

        return $this;
    }


    // TCMSFieldDecimal
    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }


}
