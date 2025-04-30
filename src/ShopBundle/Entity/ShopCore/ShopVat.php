<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

class ShopVat
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldDecimal
        /** @var string - Percentage */
        private string $vatPercent = ''
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

    // TCMSFieldDecimal
    public function getVatPercent(): string
    {
        return $this->vatPercent;
    }

    public function setVatPercent(string $vatPercent): self
    {
        $this->vatPercent = $vatPercent;

        return $this;
    }
}
