<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopVariantType
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopVariantSet|null - Belongs to variant set */
        private ?ShopVariantSet $shopVariantSet = null
        ,
        // TCMSFieldSEOURLTitle
        /** @var string - URL name */
        private string $urlName = '',
        // TCMSFieldPosition
        /** @var int - Sorting */
        private int $position = 0,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Image or icon for variant type (optional) */
        private ?CmsMedia $cmsMedia = null
        ,
        // TCMSFieldOption
        /** @var string - Input type of variant values in the CMS */
        private string $valueSelectType = 'SelectBox',
        // TCMSFieldTablefieldname
        /** @var string - Order values by */
        private string $shopVariantTypeValueCmsfieldname = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopVariantTypeValue> - Available variant values */
        private Collection $shopVariantTypeValueCollection = new ArrayCollection()
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Identifier */
        private string $identifier = ''
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
    public function getShopVariantSet(): ?ShopVariantSet
    {
        return $this->shopVariantSet;
    }

    public function setShopVariantSet(?ShopVariantSet $shopVariantSet): self
    {
        $this->shopVariantSet = $shopVariantSet;

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


    // TCMSFieldOption
    public function getValueSelectType(): string
    {
        return $this->valueSelectType;
    }

    public function setValueSelectType(string $valueSelectType): self
    {
        $this->valueSelectType = $valueSelectType;

        return $this;
    }


    // TCMSFieldTablefieldname
    public function getShopVariantTypeValueCmsfieldname(): string
    {
        return $this->shopVariantTypeValueCmsfieldname;
    }

    public function setShopVariantTypeValueCmsfieldname(string $shopVariantTypeValueCmsfieldname): self
    {
        $this->shopVariantTypeValueCmsfieldname = $shopVariantTypeValueCmsfieldname;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopVariantTypeValue>
     */
    public function getShopVariantTypeValueCollection(): Collection
    {
        return $this->shopVariantTypeValueCollection;
    }

    public function addShopVariantTypeValueCollection(ShopVariantTypeValue $shopVariantTypeValue): self
    {
        if (!$this->shopVariantTypeValueCollection->contains($shopVariantTypeValue)) {
            $this->shopVariantTypeValueCollection->add($shopVariantTypeValue);
            $shopVariantTypeValue->setShopVariantType($this);
        }

        return $this;
    }

    public function removeShopVariantTypeValueCollection(ShopVariantTypeValue $shopVariantTypeValue): self
    {
        if ($this->shopVariantTypeValueCollection->removeElement($shopVariantTypeValue)) {
            // set the owning side to null (unless already changed)
            if ($shopVariantTypeValue->getShopVariantType() === $this) {
                $shopVariantTypeValue->setShopVariantType(null);
            }
        }

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


    // TCMSFieldVarchar
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }


}
