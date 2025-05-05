<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CoreTableConfiguration\CmsFieldConf;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopVariantSet
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopVariantType> - Variant types of variant set */
        private Collection $shopVariantTypeCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable
        /** @var Collection<int, CmsFieldConf> - Fields of variant which may differ from parent item */
        private Collection $cmsFieldConfCollection = new ArrayCollection(),
        // TCMSFieldLookup
        /** @var ShopVariantDisplayHandler|null - Display handler for variant selection in  shop */
        private ?ShopVariantDisplayHandler $shopVariantDisplayHandler = null
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

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopVariantType>
     */
    public function getShopVariantTypeCollection(): Collection
    {
        return $this->shopVariantTypeCollection;
    }

    public function addShopVariantTypeCollection(ShopVariantType $shopVariantType): self
    {
        if (!$this->shopVariantTypeCollection->contains($shopVariantType)) {
            $this->shopVariantTypeCollection->add($shopVariantType);
            $shopVariantType->setShopVariantSet($this);
        }

        return $this;
    }

    public function removeShopVariantTypeCollection(ShopVariantType $shopVariantType): self
    {
        if ($this->shopVariantTypeCollection->removeElement($shopVariantType)) {
            // set the owning side to null (unless already changed)
            if ($shopVariantType->getShopVariantSet() === $this) {
                $shopVariantType->setShopVariantSet(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxesSelectFieldsFromTable

    /**
     * @return Collection<int, CmsFieldConf>
     */
    public function getCmsFieldConfCollection(): Collection
    {
        return $this->cmsFieldConfCollection;
    }

    public function addCmsFieldConfCollection(CmsFieldConf $cmsFieldConfMlt): self
    {
        if (!$this->cmsFieldConfCollection->contains($cmsFieldConfMlt)) {
            $this->cmsFieldConfCollection->add($cmsFieldConfMlt);
            $cmsFieldConfMlt->set($this);
        }

        return $this;
    }

    public function removeCmsFieldConfCollection(CmsFieldConf $cmsFieldConfMlt): self
    {
        if ($this->cmsFieldConfCollection->removeElement($cmsFieldConfMlt)) {
            // set the owning side to null (unless already changed)
            if ($cmsFieldConfMlt->get() === $this) {
                $cmsFieldConfMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldLookup
    public function getShopVariantDisplayHandler(): ?ShopVariantDisplayHandler
    {
        return $this->shopVariantDisplayHandler;
    }

    public function setShopVariantDisplayHandler(?ShopVariantDisplayHandler $shopVariantDisplayHandler): self
    {
        $this->shopVariantDisplayHandler = $shopVariantDisplayHandler;

        return $this;
    }
}
