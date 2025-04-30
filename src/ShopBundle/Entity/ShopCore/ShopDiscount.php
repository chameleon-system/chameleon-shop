<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\Core\DataCountry;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\DataExtranetUser;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopDiscount
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldVarchar
        /** @var string - Name */
        private string $name = '',
        // TCMSFieldPrice
        /** @var string - Value */
        private string $value = '',
        // TCMSFieldOption
        /** @var string - Value type */
        private string $valueType = 'absolut',
        // TCMSFieldBoolean
        /** @var bool - Show percentual discount on detailed product page */
        private bool $showDiscountOnArticleDetailpage = false,
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = false,
        // TCMSFieldDateTime
        /** @var \DateTime|null - Valid from */
        private ?\DateTime $activeFrom = null,
        // TCMSFieldDateTime
        /** @var \DateTime|null - Active until */
        private ?\DateTime $activeTo = null,
        // TCMSFieldPosition
        /** @var int - Sorting */
        private int $position = 0,
        // TCMSFieldNumber
        /** @var int - Min. amount of products affected */
        private int $restrictToArticlesFrom = 0,
        // TCMSFieldNumber
        /** @var int - Max. amount of products affected */
        private int $restrictToArticlesTo = 0,
        // TCMSFieldPrice
        /** @var string - Minimum value of affected products (Euro) */
        private string $restrictToValueFrom = '',
        // TCMSFieldPrice
        /** @var string - Maximum value of affected products (Euro) */
        private string $restrictToValueTo = '',
        // TCMSFieldLookupMultiSelectRestriction
        /** @var Collection<int, ShopCategory> - Restrict to following product categories */
        private Collection $shopCategoryCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiSelectRestriction
        /** @var bool - Restrict to following product categories */
        private bool $shopCategoryMltInverseEmpty = false,
        // TCMSFieldLookupMultiSelectRestriction
        /** @var Collection<int, ShopArticle> - Restrict to following products */
        private Collection $shopArticleCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiSelectRestriction
        /** @var bool - Restrict to following products */
        private bool $shopArticleMltInverseEmpty = false,
        // TCMSFieldLookupMultiSelectRestriction
        /** @var Collection<int, DataExtranetGroup> - Restrict to following customer groups */
        private Collection $dataExtranetGroupCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiSelectRestriction
        /** @var bool - Restrict to following customer groups */
        private bool $dataExtranetGroupMltInverseEmpty = false,
        // TCMSFieldLookupMultiSelectRestriction
        /** @var Collection<int, DataExtranetUser> - Restrict to following customers */
        private Collection $dataExtranetUserCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiSelectRestriction
        /** @var bool - Restrict to following customers */
        private bool $dataExtranetUserMltInverseEmpty = false,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, DataCountry> - Restrict to following shipping countries */
        private Collection $dataCountryCollection = new ArrayCollection(),
        // TCMSFieldWYSIWYG
        /** @var string - Description */
        private string $description = '',
        // TCMSFieldDateTime
        /** @var \DateTime|null - When has the cache of the affected products been cleared the last time? */
        private ?\DateTime $cacheClearLastExecuted = null
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
    public function isShowDiscountOnArticleDetailpage(): bool
    {
        return $this->showDiscountOnArticleDetailpage;
    }

    public function setShowDiscountOnArticleDetailpage(bool $showDiscountOnArticleDetailpage): self
    {
        $this->showDiscountOnArticleDetailpage = $showDiscountOnArticleDetailpage;

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
    public function getActiveFrom(): ?\DateTime
    {
        return $this->activeFrom;
    }

    public function setActiveFrom(?\DateTime $activeFrom): self
    {
        $this->activeFrom = $activeFrom;

        return $this;
    }

    // TCMSFieldDateTime
    public function getActiveTo(): ?\DateTime
    {
        return $this->activeTo;
    }

    public function setActiveTo(?\DateTime $activeTo): self
    {
        $this->activeTo = $activeTo;

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

    // TCMSFieldLookupMultiSelectRestriction

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

    // TCMSFieldLookupMultiSelectRestriction
    public function isShopCategoryMltInverseEmpty(): bool
    {
        return $this->shopCategoryMltInverseEmpty;
    }

    public function setShopCategoryMltInverseEmpty(bool $shopCategoryMltInverseEmpty): self
    {
        $this->shopCategoryMltInverseEmpty = $shopCategoryMltInverseEmpty;

        return $this;
    }

    // TCMSFieldLookupMultiSelectRestriction

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

    // TCMSFieldLookupMultiSelectRestriction
    public function isShopArticleMltInverseEmpty(): bool
    {
        return $this->shopArticleMltInverseEmpty;
    }

    public function setShopArticleMltInverseEmpty(bool $shopArticleMltInverseEmpty): self
    {
        $this->shopArticleMltInverseEmpty = $shopArticleMltInverseEmpty;

        return $this;
    }

    // TCMSFieldLookupMultiSelectRestriction

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

    // TCMSFieldLookupMultiSelectRestriction
    public function isDataExtranetGroupMltInverseEmpty(): bool
    {
        return $this->dataExtranetGroupMltInverseEmpty;
    }

    public function setDataExtranetGroupMltInverseEmpty(bool $dataExtranetGroupMltInverseEmpty): self
    {
        $this->dataExtranetGroupMltInverseEmpty = $dataExtranetGroupMltInverseEmpty;

        return $this;
    }

    // TCMSFieldLookupMultiSelectRestriction

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

    // TCMSFieldLookupMultiSelectRestriction
    public function isDataExtranetUserMltInverseEmpty(): bool
    {
        return $this->dataExtranetUserMltInverseEmpty;
    }

    public function setDataExtranetUserMltInverseEmpty(bool $dataExtranetUserMltInverseEmpty): self
    {
        $this->dataExtranetUserMltInverseEmpty = $dataExtranetUserMltInverseEmpty;

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

    // TCMSFieldDateTime
    public function getCacheClearLastExecuted(): ?\DateTime
    {
        return $this->cacheClearLastExecuted;
    }

    public function setCacheClearLastExecuted(?\DateTime $cacheClearLastExecuted): self
    {
        $this->cacheClearLastExecuted = $cacheClearLastExecuted;

        return $this;
    }
}
