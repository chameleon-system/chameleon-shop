<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;

class ShopVariantTypeValue
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopVariantType|null - Belongs to variant type */
        private ?ShopVariantType $shopVariantType = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldSEOURLTitle
        /** @var string - URL name (for article link) */
        private string $urlName = '',
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldColorpicker
        /** @var string - Color value (optional) */
        private string $colorCode = '',
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Optional image or icon */
        private ?CmsMedia $cmsMedia = null
        ,
        // TCMSFieldVarchar
        /** @var string - Alternative name (grouping) */
        private string $nameGrouped = '',
        // TCMSFieldPrice
        /** @var string - Surcharge / reduction */
        private string $surcharge = ''
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
    public function getShopVariantType(): ?ShopVariantType
    {
        return $this->shopVariantType;
    }

    public function setShopVariantType(?ShopVariantType $shopVariantType): self
    {
        $this->shopVariantType = $shopVariantType;

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


    // TCMSFieldSEOURLTitle
    public function getUrlName(): string
    {
        return $this->urlName;
    }

    public function setUrlName(string $urlName): self
    {
        $this->urlName = $urlName;

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


    // TCMSFieldColorpicker
    public function getColorCode(): string
    {
        return $this->colorCode;
    }

    public function setColorCode(string $colorCode): self
    {
        $this->colorCode = $colorCode;

        return $this;
    }


    // TCMSFieldExtendedLookupMedia
    public function getCmsMedia(): ?CmsMedia
    {
        return $this->cmsMedia;
    }

    public function setCmsMedia(?CmsMedia $cmsMedia): self
    {
        $this->cmsMedia = $cmsMedia;

        return $this;
    }


    // TCMSFieldVarchar
    public function getNameGrouped(): string
    {
        return $this->nameGrouped;
    }

    public function setNameGrouped(string $nameGrouped): self
    {
        $this->nameGrouped = $nameGrouped;

        return $this;
    }


    // TCMSFieldPrice
    public function getSurcharge(): string
    {
        return $this->surcharge;
    }

    public function setSurcharge(string $surcharge): self
    {
        $this->surcharge = $surcharge;

        return $this;
    }


}
