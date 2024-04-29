<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\ShopCore\Shop;

class ShopArticleImageSize
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var Shop|null - Belongs to shop */
        private ?Shop $shop = null
        ,
        // TCMSFieldVarchar
        /** @var string - System name */
        private string $nameInternal = '',
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldNumber
        /** @var int - Width */
        private int $width = 0,
        // TCMSFieldNumber
        /** @var int - Height */
        private int $height = 0,
        // TCMSFieldBoolean
        /** @var bool - Force size */
        private bool $forceSize = false
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
    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }


    // TCMSFieldVarchar
    public function getNameInternal(): string
    {
        return $this->nameInternal;
    }

    public function setNameInternal(string $nameInternal): self
    {
        $this->nameInternal = $nameInternal;

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


    // TCMSFieldNumber
    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }


    // TCMSFieldNumber
    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }


    // TCMSFieldBoolean
    public function isForceSize(): bool
    {
        return $this->forceSize;
    }

    public function setForceSize(bool $forceSize): self
    {
        $this->forceSize = $forceSize;

        return $this;
    }


}
