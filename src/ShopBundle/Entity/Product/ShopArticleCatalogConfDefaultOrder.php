<?php

namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\ProductList\ShopModuleArticlelistOrderby;
use ChameleonSystem\ShopBundle\Entity\ShopCore\ShopCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopArticleCatalogConfDefaultOrder
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopArticleCatalogConf|null - Belongs to configuration */
        private ?ShopArticleCatalogConf $shopArticleCatalogConf = null
        ,
        // TCMSFieldVarchar
        /** @var string - Name (description) */
        private string $name = '',
        // TCMSFieldLookup
        /** @var ShopModuleArticlelistOrderby|null - Sorting */
        private ?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby = null
        ,
        // TCMSFieldLookupMultiselect
        /** @var Collection<int, ShopCategory> - Category */
        private Collection $shopCategoryCollection = new ArrayCollection()
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
    public function getShopArticleCatalogConf(): ?ShopArticleCatalogConf
    {
        return $this->shopArticleCatalogConf;
    }

    public function setShopArticleCatalogConf(?ShopArticleCatalogConf $shopArticleCatalogConf): self
    {
        $this->shopArticleCatalogConf = $shopArticleCatalogConf;

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
    public function getShopModuleArticlelistOrderby(): ?ShopModuleArticlelistOrderby
    {
        return $this->shopModuleArticlelistOrderby;
    }

    public function setShopModuleArticlelistOrderby(?ShopModuleArticlelistOrderby $shopModuleArticlelistOrderby): self
    {
        $this->shopModuleArticlelistOrderby = $shopModuleArticlelistOrderby;

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


}
