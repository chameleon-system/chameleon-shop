<?php

namespace ChameleonSystem\ShopBundle\Entity\ProductList;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;
use ChameleonSystem\ShopBundle\Entity\Product\ShopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopModuleArticleList
{
    public function __construct(
        private string $id,
        private ?int $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Belongs to module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null,
        // TCMSFieldBoolean
        /** @var bool - Release for the Post-Search-Filter */
        private bool $canBeFiltered = false,
        // TCMSFieldVarchar
        /** @var string - Headline */
        private string $name = '',
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Icon */
        private ?CmsMedia $icon = null,
        // TCMSFieldLookup
        /** @var ShopModuleArticleListFilter|null - Filter / content */
        private ?ShopModuleArticleListFilter $shopModuleArticleListFilter = null,
        // TCMSFieldLookup
        /** @var ShopModuleArticlelistOrderby|null - Sorting */
        private ?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby = null,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, ShopModuleArticlelistOrderby> - Available sortings */
        private Collection $shopModuleArticlelistOrderbyCollection = new ArrayCollection(),
        // TCMSFieldNumber
        /** @var int - Number of articles shown */
        private int $numberOfArticles = -1,
        // TCMSFieldNumber
        /** @var int - Number of articles per page */
        private int $numberOfArticlesPerPage = 10,
        // TCMSFieldWYSIWYG
        /** @var string - Introduction text */
        private string $descriptionStart = '',
        // TCMSFieldWYSIWYG
        /** @var string - Closing text */
        private string $descriptionEnd = '',
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopArticleGroup> - Show articles from these article groups */
        private Collection $shopArticleGroupCollection = new ArrayCollection(),
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Show articles from these product categories */
        private Collection $shopCategoryCollection = new ArrayCollection(),
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopModuleArticleListArticle> - Show these articles */
        private Collection $shopModuleArticleListArticleCollection = new ArrayCollection()
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
    public function getCmsTplModuleInstance(): ?CmsTplModuleInstance
    {
        return $this->cmsTplModuleInstance;
    }

    public function setCmsTplModuleInstance(?CmsTplModuleInstance $cmsTplModuleInstance): self
    {
        $this->cmsTplModuleInstance = $cmsTplModuleInstance;

        return $this;
    }

    // TCMSFieldBoolean
    public function isCanBeFiltered(): bool
    {
        return $this->canBeFiltered;
    }

    public function setCanBeFiltered(bool $canBeFiltered): self
    {
        $this->canBeFiltered = $canBeFiltered;

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

    // TCMSFieldExtendedLookupMedia
    public function getIcon(): ?CmsMedia
    {
        return $this->icon;
    }

    public function setIcon(?CmsMedia $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopModuleArticleListFilter(): ?ShopModuleArticleListFilter
    {
        return $this->shopModuleArticleListFilter;
    }

    public function setShopModuleArticleListFilter(?ShopModuleArticleListFilter $shopModuleArticleListFilter): self
    {
        $this->shopModuleArticleListFilter = $shopModuleArticleListFilter;

        return $this;
    }

    // TCMSFieldLookup
    public function getShopModuleArticlelistOrderby(): ?ShopModuleArticlelistOrderby
    {
        return $this->shopModuleArticlelistOrderby;
    }

    public function setShopModuleArticlelistOrderby(?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby): self
    {
        $this->shopModuleArticlelistOrderby = $shopModuleArticlelistOrderby;

        return $this;
    }

    // TCMSFieldLookupMultiselectCheckboxes

    /**
     * @return Collection<int, ShopModuleArticlelistOrderby>
     */
    public function getShopModuleArticlelistOrderbyCollection(): Collection
    {
        return $this->shopModuleArticlelistOrderbyCollection;
    }

    public function addShopModuleArticlelistOrderbyCollection(
        ShopModuleArticlelistOrderby $shopModuleArticlelistOrderbyMlt
    ): self {
        if (!$this->shopModuleArticlelistOrderbyCollection->contains($shopModuleArticlelistOrderbyMlt)) {
            $this->shopModuleArticlelistOrderbyCollection->add($shopModuleArticlelistOrderbyMlt);
            $shopModuleArticlelistOrderbyMlt->set($this);
        }

        return $this;
    }

    public function removeShopModuleArticlelistOrderbyCollection(
        ShopModuleArticlelistOrderby $shopModuleArticlelistOrderbyMlt
    ): self {
        if ($this->shopModuleArticlelistOrderbyCollection->removeElement($shopModuleArticlelistOrderbyMlt)) {
            // set the owning side to null (unless already changed)
            if ($shopModuleArticlelistOrderbyMlt->get() === $this) {
                $shopModuleArticlelistOrderbyMlt->set(null);
            }
        }

        return $this;
    }

    // TCMSFieldNumber
    public function getNumberOfArticles(): int
    {
        return $this->numberOfArticles;
    }

    public function setNumberOfArticles(int $numberOfArticles): self
    {
        $this->numberOfArticles = $numberOfArticles;

        return $this;
    }

    // TCMSFieldNumber
    public function getNumberOfArticlesPerPage(): int
    {
        return $this->numberOfArticlesPerPage;
    }

    public function setNumberOfArticlesPerPage(int $numberOfArticlesPerPage): self
    {
        $this->numberOfArticlesPerPage = $numberOfArticlesPerPage;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getDescriptionStart(): string
    {
        return $this->descriptionStart;
    }

    public function setDescriptionStart(string $descriptionStart): self
    {
        $this->descriptionStart = $descriptionStart;

        return $this;
    }

    // TCMSFieldWYSIWYG
    public function getDescriptionEnd(): string
    {
        return $this->descriptionEnd;
    }

    public function setDescriptionEnd(string $descriptionEnd): self
    {
        $this->descriptionEnd = $descriptionEnd;

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

    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopModuleArticleListArticle>
     */
    public function getShopModuleArticleListArticleCollection(): Collection
    {
        return $this->shopModuleArticleListArticleCollection;
    }

    public function addShopModuleArticleListArticleCollection(ShopModuleArticleListArticle $shopModuleArticleListArticle
    ): self {
        if (!$this->shopModuleArticleListArticleCollection->contains($shopModuleArticleListArticle)) {
            $this->shopModuleArticleListArticleCollection->add($shopModuleArticleListArticle);
            $shopModuleArticleListArticle->setShopModuleArticleList($this);
        }

        return $this;
    }

    public function removeShopModuleArticleListArticleCollection(
        ShopModuleArticleListArticle $shopModuleArticleListArticle
    ): self {
        if ($this->shopModuleArticleListArticleCollection->removeElement($shopModuleArticleListArticle)) {
            // set the owning side to null (unless already changed)
            if ($shopModuleArticleListArticle->getShopModuleArticleList() === $this) {
                $shopModuleArticleListArticle->setShopModuleArticleList(null);
            }
        }

        return $this;
    }
}
