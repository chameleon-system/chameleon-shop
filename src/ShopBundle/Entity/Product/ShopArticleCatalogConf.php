<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTplModuleInstance;
use ChameleonSystem\ShopBundle\Entity\ProductList\ShopModuleArticlelistOrderby;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopArticleCatalogConf
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var CmsTplModuleInstance|null - Belongs to module instance */
        private ?CmsTplModuleInstance $cmsTplModuleInstance = null
        ,
        // TCMSFieldVarchar
        /** @var string - Title / headline */
        private string $name = '',
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopArticleCatalogConfDefaultOrder> - Alternative default sorting */
        private Collection $shopArticleCatalogConfDefaultOrderCollection = new ArrayCollection()
        ,
        // TCMSFieldBoolean
        /** @var bool - Offer Reserving at 0 stock */
        private bool $showSubcategoryProducts = false,
        // TCMSFieldNumber
        /** @var int - Articles per page */
        private int $pageSize = 20,
        // TCMSFieldLookup
        /** @var ShopModuleArticlelistOrderby|null - Default sorting */
        private ?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby = null
        ,
        // TCMSFieldLookupMultiselectCheckboxes
        /** @var Collection<int, ShopModuleArticlelistOrderby> - Available sortings */
        private Collection $shopModuleArticlelistOrderbyCollection = new ArrayCollection()
        ,
        // TCMSFieldWYSIWYG
        /** @var string - Introduction text */
        private string $intro = ''
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
     * @return Collection<int, ShopArticleCatalogConfDefaultOrder>
     */
    public function getShopArticleCatalogConfDefaultOrderCollection(): Collection
    {
        return $this->shopArticleCatalogConfDefaultOrderCollection;
    }

    public function addShopArticleCatalogConfDefaultOrderCollection(
        ShopArticleCatalogConfDefaultOrder $shopArticleCatalogConfDefaultOrder
    ): self {
        if (!$this->shopArticleCatalogConfDefaultOrderCollection->contains($shopArticleCatalogConfDefaultOrder)) {
            $this->shopArticleCatalogConfDefaultOrderCollection->add($shopArticleCatalogConfDefaultOrder);
            $shopArticleCatalogConfDefaultOrder->setShopArticleCatalogConf($this);
        }

        return $this;
    }

    public function removeShopArticleCatalogConfDefaultOrderCollection(
        ShopArticleCatalogConfDefaultOrder $shopArticleCatalogConfDefaultOrder
    ): self {
        if ($this->shopArticleCatalogConfDefaultOrderCollection->removeElement($shopArticleCatalogConfDefaultOrder)) {
            // set the owning side to null (unless already changed)
            if ($shopArticleCatalogConfDefaultOrder->getShopArticleCatalogConf() === $this) {
                $shopArticleCatalogConfDefaultOrder->setShopArticleCatalogConf(null);
            }
        }

        return $this;
    }


    // TCMSFieldBoolean
    public function isShowSubcategoryProducts(): bool
    {
        return $this->showSubcategoryProducts;
    }

    public function setShowSubcategoryProducts(bool $showSubcategoryProducts): self
    {
        $this->showSubcategoryProducts = $showSubcategoryProducts;

        return $this;
    }


    // TCMSFieldNumber
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): self
    {
        $this->pageSize = $pageSize;

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


    // TCMSFieldWYSIWYG
    public function getIntro(): string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }


}
