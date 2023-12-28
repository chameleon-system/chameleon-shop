<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\Core\DataCountry;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticleGroup;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopShippingType
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldPrice
        /** @var string - Additional costs */
        private string $value = '',
        // TCMSFieldOption
        /** @var string - Addtional costs type */
        private string $valueType = 'absolut',
        // TCMSFieldBoolean
        /** @var bool - Value relates to the whole basket */
        private bool $valueBasedOnEntireBasket = false,
        // TCMSFieldPrice
        /** @var string - Additional charges */
        private string $valueAdditional = '',
        // TCMSFieldPrice
        /** @var string - Maximum additional charges */
        private string $valueMax = '',
        // TCMSFieldPrice
        /** @var string - Minimum additional charges */
        private string $valueMin = '',
        // TCMSFieldBoolean
        /** @var bool - Calculate shipping costs for each item separately */
        private bool $addValueForEachArticle = false,
        // TCMSFieldBoolean
        /** @var bool - Use for logged in users only */
        private bool $restrictToSignedInUsers = false,
        // TCMSFieldBoolean
        /** @var bool - Apply to all products with at least one match */
        private bool $applyToAllProducts = false,
        // TCMSFieldBoolean
        /** @var bool - When applied, ignore all other shipping costs types */
        private bool $endShippingTypeChain = false,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active as of */
        private ?DateTime $activeFrom = null,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active until */
        private ?DateTime $activeTo = null,
        // TCMSFieldPrice
        /** @var string - Minimum value of affected items (Euro) */
        private string $restrictToValueFrom = '',
        // TCMSFieldPrice
        /** @var string - Maximum value of affected items (Euro) */
        private string $restrictToValueTo = '',
        // TCMSFieldNumber
        /** @var int - Minimum amount of items affected */
        private int $restrictToArticlesFrom = 0,
        // TCMSFieldNumber
        /** @var int - Maximum amount of items affected */
        private int $restrictToArticlesTo = 0,
        // TCMSFieldNumber
        /** @var int - Minimum weight of affected items (grams) */
        private int $restrictToWeightFrom = 0,
        // TCMSFieldNumber
        /** @var int - Maximum weight of affected items (grams) */
        private int $restrictToWeightTo = 0,
        // TCMSFieldDecimal
        /** @var string - Minimum volume of affected items (cubic meters) */
        private string $restrictToVolumeFrom = '',
        // TCMSFieldDecimal
        /** @var string - Maximum volume of affected items (cubic meters) */
        private string $restrictToVolumeTo = '',
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticleGroup> - Restrict to following product groups */
        private Collection $shopArticleGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Restrict to following product categories */
        private Collection $shopCategoryCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Restrict to following items */
        private Collection $shopArticleCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataCountry> - Restrict to following shipping countries */
        private Collection $dataCountryCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetUser> - Restrict to following users */
        private Collection $dataExtranetUserCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetGroup> - Restrict to following customer groups */
        private Collection $dataExtranetGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, CmsPortal> - Restrict to following portals */
        private Collection $cmsPortalCollection = new ArrayCollection()
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


    // TCMSFieldPrice
    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }


    // TCMSFieldOption
    public function getValueType(): string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): self
    {
        $this->valueType = $valueType;

        return $this;
    }


    // TCMSFieldBoolean
    public function isValueBasedOnEntireBasket(): bool
    {
        return $this->valueBasedOnEntireBasket;
    }

    public function setValueBasedOnEntireBasket(bool $valueBasedOnEntireBasket): self
    {
        $this->valueBasedOnEntireBasket = $valueBasedOnEntireBasket;

        return $this;
    }


    // TCMSFieldPrice
    public function getValueAdditional(): string
    {
        return $this->valueAdditional;
    }

    public function setValueAdditional(string $valueAdditional): self
    {
        $this->valueAdditional = $valueAdditional;

        return $this;
    }


    // TCMSFieldPrice
    public function getValueMax(): string
    {
        return $this->valueMax;
    }

    public function setValueMax(string $valueMax): self
    {
        $this->valueMax = $valueMax;

        return $this;
    }


    // TCMSFieldPrice
    public function getValueMin(): string
    {
        return $this->valueMin;
    }

    public function setValueMin(string $valueMin): self
    {
        $this->valueMin = $valueMin;

        return $this;
    }


    // TCMSFieldBoolean
    public function isAddValueForEachArticle(): bool
    {
        return $this->addValueForEachArticle;
    }

    public function setAddValueForEachArticle(bool $addValueForEachArticle): self
    {
        $this->addValueForEachArticle = $addValueForEachArticle;

        return $this;
    }


    // TCMSFieldBoolean
    public function isRestrictToSignedInUsers(): bool
    {
        return $this->restrictToSignedInUsers;
    }

    public function setRestrictToSignedInUsers(bool $restrictToSignedInUsers): self
    {
        $this->restrictToSignedInUsers = $restrictToSignedInUsers;

        return $this;
    }


    // TCMSFieldBoolean
    public function isApplyToAllProducts(): bool
    {
        return $this->applyToAllProducts;
    }

    public function setApplyToAllProducts(bool $applyToAllProducts): self
    {
        $this->applyToAllProducts = $applyToAllProducts;

        return $this;
    }


    // TCMSFieldBoolean
    public function isEndShippingTypeChain(): bool
    {
        return $this->endShippingTypeChain;
    }

    public function setEndShippingTypeChain(bool $endShippingTypeChain): self
    {
        $this->endShippingTypeChain = $endShippingTypeChain;

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


    // TCMSFieldBoolean
    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }


    // TCMSFieldDateTime
    public function getActiveFrom(): ?DateTime
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?DateTime $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }


    // TCMSFieldDateTime
    public function getActiveTo(): ?DateTime
    {
        return $this->activeTo;
    }

    public function setActiveTo(?DateTime $activeTo): self
    {
        $this->activeTo = $activeTo;

        return $this;
    }


    // TCMSFieldPrice
    public function getRestrictToValueFrom(): string
    {
        return $this->restrictToValueFrom;
    }

    public function setRestrictToValueFrom(string $restrictToValueFrom): self
    {
        $this->restrictToValueFrom = $restrictToValueFrom;

        return $this;
    }


    // TCMSFieldPrice
    public function getRestrictToValueTo(): string
    {
        return $this->restrictToValueTo;
    }

    public function setRestrictToValueTo(string $restrictToValueTo): self
    {
        $this->restrictToValueTo = $restrictToValueTo;

        return $this;
    }


    // TCMSFieldNumber
    public function getRestrictToArticlesFrom(): int
    {
        return $this->restrictToArticlesFrom;
    }

    public function setRestrictToArticlesFrom(int $restrictToArticlesFrom): self
    {
        $this->restrictToArticlesFrom = $restrictToArticlesFrom;

        return $this;
    }


    // TCMSFieldNumber
    public function getRestrictToArticlesTo(): int
    {
        return $this->restrictToArticlesTo;
    }

    public function setRestrictToArticlesTo(int $restrictToArticlesTo): self
    {
        $this->restrictToArticlesTo = $restrictToArticlesTo;

        return $this;
    }


    // TCMSFieldNumber
    public function getRestrictToWeightFrom(): int
    {
        return $this->restrictToWeightFrom;
    }

    public function setRestrictToWeightFrom(int $restrictToWeightFrom): self
    {
        $this->restrictToWeightFrom = $restrictToWeightFrom;

        return $this;
    }


    // TCMSFieldNumber
    public function getRestrictToWeightTo(): int
    {
        return $this->restrictToWeightTo;
    }

    public function setRestrictToWeightTo(int $restrictToWeightTo): self
    {
        $this->restrictToWeightTo = $restrictToWeightTo;

        return $this;
    }


    // TCMSFieldDecimal
    public function getRestrictToVolumeFrom(): string
    {
        return $this->restrictToVolumeFrom;
    }

    public function setRestrictToVolumeFrom(string $restrictToVolumeFrom): self
    {
        $this->restrictToVolumeFrom = $restrictToVolumeFrom;

        return $this;
    }


    // TCMSFieldDecimal
    public function getRestrictToVolumeTo(): string
    {
        return $this->restrictToVolumeTo;
    }

    public function setRestrictToVolumeTo(string $restrictToVolumeTo): self
    {
        $this->restrictToVolumeTo = $restrictToVolumeTo;

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticleGroup>
     */
    public function getShopArticleGroupCollection(): Collection
    {
        return $this->shopArticleGroupCollection;
    }

    public function addShopArticleGroupCollection(ShopArticleGroup $shopArticleGroupMlt): self
    {
        if (!$this->shopArticleGroupCollection->contains($shopArticleGroupMlt)) {
            $this->shopArticleGroupCollection->add($shopArticleGroupMlt);
            $shopArticleGroupMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleGroupCollection(ShopArticleGroup $shopArticleGroupMlt): self
    {
        if ($this->shopArticleGroupCollection->removeElement($shopArticleGroupMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleGroupMlt->get() === $this) {
                $shopArticleGroupMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopCategory>
     */
    public function getShopCategoryCollection(): Collection
    {
        return $this->shopCategoryCollection;
    }

    public function addShopCategoryCollection(ShopCategory $shopCategoryMlt): self
    {
        if (!$this->shopCategoryCollection->contains($shopCategoryMlt)) {
            $this->shopCategoryCollection->add($shopCategoryMlt);
            $shopCategoryMlt->set($this);
        }

        return $this;
    }

    public function removeShopCategoryCollection(ShopCategory $shopCategoryMlt): self
    {
        if ($this->shopCategoryCollection->removeElement($shopCategoryMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopCategoryMlt->get() === $this) {
                $shopCategoryMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticleCollection(): Collection
    {
        return $this->shopArticleCollection;
    }

    public function addShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if (!$this->shopArticleCollection->contains($shopArticleMlt)) {
            $this->shopArticleCollection->add($shopArticleMlt);
            $shopArticleMlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleCollection(ShopArticle $shopArticleMlt): self
    {
        if ($this->shopArticleCollection->removeElement($shopArticleMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleMlt->get() === $this) {
                $shopArticleMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, DataCountry>
     */
    public function getDataCountryCollection(): Collection
    {
        return $this->dataCountryCollection;
    }

    public function addDataCountryCollection(DataCountry $dataCountryMlt): self
    {
        if (!$this->dataCountryCollection->contains($dataCountryMlt)) {
            $this->dataCountryCollection->add($dataCountryMlt);
            $dataCountryMlt->set($this);
        }

        return $this;
    }

    public function removeDataCountryCollection(DataCountry $dataCountryMlt): self
    {
        if ($this->dataCountryCollection->removeElement($dataCountryMlt)) {
            // set the owning side to null (unless already changed)
            if ($dataCountryMlt->get() === $this) {
                $dataCountryMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, DataExtranetUser>
     */
    public function getDataExtranetUserCollection(): Collection
    {
        return $this->dataExtranetUserCollection;
    }

    public function addDataExtranetUserCollection(DataExtranetUser $dataExtranetUserMlt): self
    {
        if (!$this->dataExtranetUserCollection->contains($dataExtranetUserMlt)) {
            $this->dataExtranetUserCollection->add($dataExtranetUserMlt);
            $dataExtranetUserMlt->set($this);
        }

        return $this;
    }

    public function removeDataExtranetUserCollection(DataExtranetUser $dataExtranetUserMlt): self
    {
        if ($this->dataExtranetUserCollection->removeElement($dataExtranetUserMlt)) {
            // set the owning side to null (unless already changed)
            if ($dataExtranetUserMlt->get() === $this) {
                $dataExtranetUserMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, DataExtranetGroup>
     */
    public function getDataExtranetGroupCollection(): Collection
    {
        return $this->dataExtranetGroupCollection;
    }

    public function addDataExtranetGroupCollection(DataExtranetGroup $dataExtranetGroupMlt): self
    {
        if (!$this->dataExtranetGroupCollection->contains($dataExtranetGroupMlt)) {
            $this->dataExtranetGroupCollection->add($dataExtranetGroupMlt);
            $dataExtranetGroupMlt->set($this);
        }

        return $this;
    }

    public function removeDataExtranetGroupCollection(DataExtranetGroup $dataExtranetGroupMlt): self
    {
        if ($this->dataExtranetGroupCollection->removeElement($dataExtranetGroupMlt)) {
            // set the owning side to null (unless already changed)
            if ($dataExtranetGroupMlt->get() === $this) {
                $dataExtranetGroupMlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, CmsPortal>
     */
    public function getCmsPortalCollection(): Collection
    {
        return $this->cmsPortalCollection;
    }

    public function addCmsPortalCollection(CmsPortal $cmsPortalMlt): self
    {
        if (!$this->cmsPortalCollection->contains($cmsPortalMlt)) {
            $this->cmsPortalCollection->add($cmsPortalMlt);
            $cmsPortalMlt->set($this);
        }

        return $this;
    }

    public function removeCmsPortalCollection(CmsPortal $cmsPortalMlt): self
    {
        if ($this->cmsPortalCollection->removeElement($cmsPortalMlt)) {
            // set the owning side to null (unless already changed)
            if ($cmsPortalMlt->get() === $this) {
                $cmsPortalMlt->set(null);
            }
        }

        return $this;
    }


}
