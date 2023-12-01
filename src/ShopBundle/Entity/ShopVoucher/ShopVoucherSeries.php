<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ExtranetBundle\Entity\DataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\Product\ShopManufacturer;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopCategory;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopVat;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopVoucherSeries
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldLookup
        /** @var ShopVoucherSeriesSponsor|null - Voucher sponsor */
        private ?ShopVoucherSeriesSponsor $shopVoucherSeriesSponsor = null
        ,
        // TCMSFieldPrice
        /** @var float - Value */
        private float $value = 0,
        // TCMSFieldOption
        /** @var string - Value type */
        private string $valueType = 'absolut',
        // TCMSFieldLookup
        /** @var ShopVat|null - VAT group */
        private ?ShopVat $shopVat = null
        ,
        // TCMSFieldBoolean
        /** @var bool - Free shipping */
        private bool $freeShipping = false,
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active from */
        private ?DateTime $activeFrom = null,
        // TCMSFieldDateTime
        /** @var DateTime|null - Active until */
        private ?DateTime $activeTo = null,
        // TCMSFieldPrice
        /** @var float - Minimum order value */
        private float $restrictToValue = 0,
        // TCMSFieldBoolean
        /** @var bool - Allow with other series only */
        private bool $restrictToOtherSeries = true,
        // TCMSFieldBoolean
        /** @var bool - Do not allow in combination with other vouchers */
        private bool $allowNoOtherVouchers = true,
        // TCMSFieldBoolean
        /** @var bool - Allow one voucher per customer only */
        private bool $restrictToOnePerUser = false,
        // TCMSFieldBoolean
        /** @var bool - Only allow at first order of a customer */
        private bool $restrictToFirstOrder = false,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetUser> - Restrict to following customers */
        private Collection $dataExtranetUserCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataExtranetGroup> - Restrict to following customer groups */
        private Collection $dataExtranetGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopManufacturer> - Restrict to products from this manufacturer */
        private Collection $shopManufacturerCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticleGroup> - Restrict to products from these product groups */
        private Collection $shopArticleGroupCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Restrict to products from these product categories */
        private Collection $shopCategoryCollection = new ArrayCollection()
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticle> - Restrict to these products */
        private Collection $shopArticleCollection = new ArrayCollection()
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopVoucher> - Vouchers belonging to the series */
        private Collection $shopVoucherCollection = new ArrayCollection()
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


    // TCMSFieldLookup
    public function getShopVoucherSeriesSponsor(): ?ShopVoucherSeriesSponsor
    {
        return $this->shopVoucherSeriesSponsor;
    }

    public function setShopVoucherSeriesSponsor(?ShopVoucherSeriesSponsor $shopVoucherSeriesSponsor): self
    {
        $this->shopVoucherSeriesSponsor = $shopVoucherSeriesSponsor;

        return $this;
    }


    // TCMSFieldPrice
    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
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


    // TCMSFieldBoolean
    public function isFreeShipping(): bool
    {
        return $this->freeShipping;
    }

    public function setFreeShipping(bool $freeShipping): self
    {
        $this->freeShipping = $freeShipping;

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
    public function getRestrictToValue(): float
    {
        return $this->restrictToValue;
    }

    public function setRestrictToValue(float $restrictToValue): self
    {
        $this->restrictToValue = $restrictToValue;

        return $this;
    }


    // TCMSFieldBoolean
    public function isRestrictToOtherSeries(): bool
    {
        return $this->restrictToOtherSeries;
    }

    public function setRestrictToOtherSeries(bool $restrictToOtherSeries): self
    {
        $this->restrictToOtherSeries = $restrictToOtherSeries;

        return $this;
    }


    // TCMSFieldBoolean
    public function isAllowNoOtherVouchers(): bool
    {
        return $this->allowNoOtherVouchers;
    }

    public function setAllowNoOtherVouchers(bool $allowNoOtherVouchers): self
    {
        $this->allowNoOtherVouchers = $allowNoOtherVouchers;

        return $this;
    }


    // TCMSFieldBoolean
    public function isRestrictToOnePerUser(): bool
    {
        return $this->restrictToOnePerUser;
    }

    public function setRestrictToOnePerUser(bool $restrictToOnePerUser): self
    {
        $this->restrictToOnePerUser = $restrictToOnePerUser;

        return $this;
    }


    // TCMSFieldBoolean
    public function isRestrictToFirstOrder(): bool
    {
        return $this->restrictToFirstOrder;
    }

    public function setRestrictToFirstOrder(bool $restrictToFirstOrder): self
    {
        $this->restrictToFirstOrder = $restrictToFirstOrder;

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
     * @return Collection<int, ShopManufacturer>
     */
    public function getShopManufacturerCollection(): Collection
    {
        return $this->shopManufacturerCollection;
    }

    public function addShopManufacturerCollection(ShopManufacturer $shopManufacturerMlt): self
    {
        if (!$this->shopManufacturerCollection->contains($shopManufacturerMlt)) {
            $this->shopManufacturerCollection->add($shopManufacturerMlt);
            $shopManufacturerMlt->set($this);
        }

        return $this;
    }

    public function removeShopManufacturerCollection(ShopManufacturer $shopManufacturerMlt): self
    {
        if ($this->shopManufacturerCollection->removeElement($shopManufacturerMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopManufacturerMlt->get() === $this) {
                $shopManufacturerMlt->set(null);
            }
        }

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



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopVoucher>
     */
    public function getShopVoucherCollection(): Collection
    {
        return $this->shopVoucherCollection;
    }

    public function addShopVoucherCollection(ShopVoucher $shopVoucher): self
    {
        if (!$this->shopVoucherCollection->contains($shopVoucher)) {
            $this->shopVoucherCollection->add($shopVoucher);
            $shopVoucher->setShopVoucherSeries($this);
        }

        return $this;
    }

    public function removeShopVoucherCollection(ShopVoucher $shopVoucher): self
    {
        if ($this->shopVoucherCollection->removeElement($shopVoucher)) {
            // set the owning side to null (unless already changed)
            if ($shopVoucher->getShopVoucherSeries() === $this) {
                $shopVoucher->setShopVoucherSeries(null);
            }
        }

        return $this;
    }


}
