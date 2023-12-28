<?php

namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\DataAccessBundle\Entity\Core\DataCountry;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\CmsPortal;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopCategory;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopVat;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopPaymentMethod
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopPaymentHandlerGroup|null - Belongs to payment provider */
        private ?ShopPaymentHandlerGroup $shopPaymentHandlerGroup = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldVarchar
        /** @var string - Internal system name */
        private string $nameInternal = '',
        // TCMSFieldLookup
        /** @var ShopPaymentHandler|null - Payment handler */
        private ?ShopPaymentHandler $shopPaymentHandler = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldBoolean
        /** @var bool - Allow for Packstation delivery addresses */
        private bool $pkgDhlPackstationAllowForPackstation = true,
        // TCMSFieldPosition
        /** @var int - Sorting */
        private int $position = 0,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, CmsPortal> - Restrict to the following portals */
        private Collection $cmsPortalCollection = new ArrayCollection()
        ,
        // TCMSFieldPrice
        /** @var string - Available from merchandise value */
        private string $restrictToValueFrom = '',
        // TCMSFieldPrice
        /** @var string - Available until merchandise value */
        private string $restrictToValueTo = '',
        // TCMSFieldDecimal
        /** @var string - Available from basket value */
        private string $restrictToBasketValueFrom = '',
        // TCMSFieldDecimal
        /** @var string - Available to basket value */
        private string $restrictToBasketValueTo = '',
        // TCMSFieldPrice
        /** @var string - Additional costs */
        private string $value = '',
        // TCMSFieldOption
        /** @var string - Additional costs type */
        private string $valueType = 'absolut',
        // TCMSFieldLookup
        /** @var ShopVat|null - VAT group */
        private ?ShopVat $shopVat = null
        ,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Icon */
        private ?CmsMedia $cmsMedia = null
        ,
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = '',
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetUser> - Restrict to following customers */
        private Collection $dataExtranetUserCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetGroup> - Restrict to following customer groups */
        private Collection $dataExtranetGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataCountry> - Restrict to following shipping countries */
        private Collection $dataCountryCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataCountry> - Restrict to following billing countries */
        private Collection $dataCountryBillingCollection = new ArrayCollection()
        ,
        // TCMSFieldBoolean
        /** @var bool - Use not fixed positive list match */
        private bool $positivListLooseMatch = false,
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
        /** @var Collection<int, ShopArticleGroup> - Do not allow for following product groups */
        private Collection $shopArticleGroup1Collection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Do not allow for following product categories */
        private Collection $shopCategory1Collection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Do not allow for following products */
        private Collection $shopArticle1Collection = new ArrayCollection()
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


    // TCMSFieldLookup
    public function getShopPaymentHandler(): ?ShopPaymentHandler
    {
        return $this->shopPaymentHandler;
    }

    public function setShopPaymentHandler(?ShopPaymentHandler $shopPaymentHandler): self
    {
        $this->shopPaymentHandler = $shopPaymentHandler;

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


    // TCMSFieldBoolean
    public function isPkgDhlPackstationAllowForPackstation(): bool
    {
        return $this->pkgDhlPackstationAllowForPackstation;
    }

    public function setPkgDhlPackstationAllowForPackstation(bool $pkgDhlPackstationAllowForPackstation): self
    {
        $this->pkgDhlPackstationAllowForPackstation = $pkgDhlPackstationAllowForPackstation;

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


    // TCMSFieldDecimal
    public function getRestrictToBasketValueFrom(): string
    {
        return $this->restrictToBasketValueFrom;
    }

    public function setRestrictToBasketValueFrom(string $restrictToBasketValueFrom): self
    {
        $this->restrictToBasketValueFrom = $restrictToBasketValueFrom;

        return $this;
    }


    // TCMSFieldDecimal
    public function getRestrictToBasketValueTo(): string
    {
        return $this->restrictToBasketValueTo;
    }

    public function setRestrictToBasketValueTo(string $restrictToBasketValueTo): self
    {
        $this->restrictToBasketValueTo = $restrictToBasketValueTo;

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


    // TCMSFieldLookup
    public function getShopVat(): ?ShopVat
    {
        return $this->shopVat;
    }

    public function setShopVat(?ShopVat $shopVat): self
    {
        $this->shopVat = $shopVat;

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
     * @return Collection<int, DataCountry>
     */
    public function getDataCountryBillingCollection(): Collection
    {
        return $this->dataCountryBillingCollection;
    }

    public function addDataCountryBillingCollection(DataCountry $dataCountryBilling): self
    {
        if (!$this->dataCountryBillingCollection->contains($dataCountryBilling)) {
            $this->dataCountryBillingCollection->add($dataCountryBilling);
            $dataCountryBilling->set($this);
        }

        return $this;
    }

    public function removeDataCountryBillingCollection(DataCountry $dataCountryBilling): self
    {
        if ($this->dataCountryBillingCollection->removeElement($dataCountryBilling)) {
            // set the owning side to null (unless already changed)
            if ($dataCountryBilling->get() === $this) {
                $dataCountryBilling->set(null);
            }
        }

        return $this;
    }


    // TCMSFieldBoolean
    public function isPositivListLooseMatch(): bool
    {
        return $this->positivListLooseMatch;
    }

    public function setPositivListLooseMatch(bool $positivListLooseMatch): self
    {
        $this->positivListLooseMatch = $positivListLooseMatch;

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
     * @return Collection<int, ShopArticleGroup>
     */
    public function getShopArticleGroup1Collection(): Collection
    {
        return $this->shopArticleGroup1Collection;
    }

    public function addShopArticleGroup1Collection(ShopArticleGroup $shopArticleGroup1Mlt): self
    {
        if (!$this->shopArticleGroup1Collection->contains($shopArticleGroup1Mlt)) {
            $this->shopArticleGroup1Collection->add($shopArticleGroup1Mlt);
            $shopArticleGroup1Mlt->set($this);
        }

        return $this;
    }

    public function removeShopArticleGroup1Collection(ShopArticleGroup $shopArticleGroup1Mlt): self
    {
        if ($this->shopArticleGroup1Collection->removeElement($shopArticleGroup1Mlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleGroup1Mlt->get() === $this) {
                $shopArticleGroup1Mlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopCategory>
     */
    public function getShopCategory1Collection(): Collection
    {
        return $this->shopCategory1Collection;
    }

    public function addShopCategory1Collection(ShopCategory $shopCategory1Mlt): self
    {
        if (!$this->shopCategory1Collection->contains($shopCategory1Mlt)) {
            $this->shopCategory1Collection->add($shopCategory1Mlt);
            $shopCategory1Mlt->set($this);
        }

        return $this;
    }

    public function removeShopCategory1Collection(ShopCategory $shopCategory1Mlt): self
    {
        if ($this->shopCategory1Collection->removeElement($shopCategory1Mlt)) {
            // set the owning side to null (unless already changed)
            if ($shopCategory1Mlt->get() === $this) {
                $shopCategory1Mlt->set(null);
            }
        }

        return $this;
    }



    // TCMSFieldLookupMultiselect

    /**
     * @return Collection<int, ShopArticle>
     */
    public function getShopArticle1Collection(): Collection
    {
        return $this->shopArticle1Collection;
    }

    public function addShopArticle1Collection(ShopArticle $shopArticle1Mlt): self
    {
        if (!$this->shopArticle1Collection->contains($shopArticle1Mlt)) {
            $this->shopArticle1Collection->add($shopArticle1Mlt);
            $shopArticle1Mlt->set($this);
        }

        return $this;
    }

    public function removeShopArticle1Collection(ShopArticle $shopArticle1Mlt): self
    {
        if ($this->shopArticle1Collection->removeElement($shopArticle1Mlt)) {
            // set the owning side to null (unless already changed)
            if ($shopArticle1Mlt->get() === $this) {
                $shopArticle1Mlt->set(null);
            }
        }

        return $this;
    }


}
