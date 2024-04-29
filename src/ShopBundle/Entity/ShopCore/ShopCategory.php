<?php

namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\CmsMedia;
use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\CmsTree;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ShopCategory
{
    public function __construct(
        private string $id,
        private int|null $cmsident = null,

        // TCMSFieldLookupParentID
        /** @var ShopCategory|null - Subcategory of */
        private ?ShopCategory $shopCategory = null
        ,
        // TCMSFieldTreeNode
        /** @var CmsTree|null - Template for the details page */
        private ?CmsTree $detailPageCmsTree = null
        ,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Icon for navigation */
        private ?CmsMedia $naviIconCmsMedia = null
        ,
        // TCMSFieldText
        /** @var string - URL path */
        private string $urlPath = '',
        // TCMSFieldVarchar
        /** @var string - Category name */
        private string $name = '',
        // TCMSFieldBoolean
        /** @var bool - Active */
        private bool $active = true,
        // TCMSFieldBoolean
        /** @var bool - Is the tree active up to this category? */
        private bool $treeActive = true,
        // TCMSFieldVarchar
        /** @var string - Additional product name */
        private string $nameProduct = '',
        // TCMSFieldVarchar
        /** @var string - SEO pattern */
        private string $seoPattern = '',
        // TCMSFieldLookup
        /** @var ShopVat|null - VAT group */
        private ?ShopVat $shopVat = null
        ,
        // TCMSFieldColorpicker
        /** @var string - Color code */
        private string $colorcode = '',
        // TCMSFieldBoolean
        /** @var bool - Highlight category */
        private bool $categoryHightlight = false,
        // TCMSFieldExtendedLookupMedia
        /** @var CmsMedia|null - Category image */
        private ?CmsMedia $image = null
        ,
        // TCMSFieldPosition
        /** @var int - Position */
        private int $position = 0,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopCategory> - Subcategories */
        private Collection $shopCategoryCollection = new ArrayCollection()
        ,
        // TCMSFieldWYSIWYG
        /** @var string - Short description of the category */
        private string $descriptionShort = '',
        // TCMSFieldWYSIWYG
        /** @var string - Detailed description of the category */
        private string $description = '',
        // TCMSFieldVarchar
        /** @var string - Meta keywords */
        private string $metaKeywords = '',
        // TCMSFieldLookup
        /** @var PkgShopListfilter|null - List filter for the category */
        private ?PkgShopListfilter $pkgShopListfilter = null
        ,
        // TCMSFieldPropertyTable
        /** @var Collection<int, ShopCategoryTab> - Category */
        private Collection $shopCategoryTabCollection = new ArrayCollection()
        ,
        // TCMSFieldVarchar
        /** @var string - Meta description */
        private string $metaDescription = ''
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

    public function getDetailPageCmsTree(): ?CmsTree
    {
        return $this->detailPageCmsTree;
    }

    public function setDetailPageCmsTree(?CmsTree $detailPageCmsTree): self
    {
        $this->detailPageCmsTree = $detailPageCmsTree;

        return $this;
    }


    // TCMSFieldTreeNode

    public function getNaviIconCmsMedia(): ?CmsMedia
    {
        return $this->naviIconCmsMedia;
    }

    public function setNaviIconCmsMedia(?CmsMedia $naviIconCmsMedia): self
    {
        $this->naviIconCmsMedia = $naviIconCmsMedia;

        return $this;
    }


    // TCMSFieldExtendedLookupMedia

    public function getUrlPath(): string
    {
        return $this->urlPath;
    }

    public function setUrlPath(string $urlPath): self
    {
        $this->urlPath = $urlPath;

        return $this;
    }


    // TCMSFieldText

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

    public function isTreeActive(): bool
    {
        return $this->treeActive;
    }

    public function setTreeActive(bool $treeActive): self
    {
        $this->treeActive = $treeActive;

        return $this;
    }


    // TCMSFieldBoolean

    public function getNameProduct(): string
    {
        return $this->nameProduct;
    }

    public function setNameProduct(string $nameProduct): self
    {
        $this->nameProduct = $nameProduct;

        return $this;
    }


    // TCMSFieldVarchar

    public function getSeoPattern(): string
    {
        return $this->seoPattern;
    }

    public function setSeoPattern(string $seoPattern): self
    {
        $this->seoPattern = $seoPattern;

        return $this;
    }


    // TCMSFieldVarchar

    public function getShopVat(): ?ShopVat
    {
        return $this->shopVat;
    }

    public function setShopVat(?ShopVat $shopVat): self
    {
        $this->shopVat = $shopVat;

        return $this;
    }


    // TCMSFieldLookup

    public function getColorcode(): string
    {
        return $this->colorcode;
    }

    public function setColorcode(string $colorcode): self
    {
        $this->colorcode = $colorcode;

        return $this;
    }


    // TCMSFieldColorpicker

    public function isCategoryHightlight(): bool
    {
        return $this->categoryHightlight;
    }

    public function setCategoryHightlight(bool $categoryHightlight): self
    {
        $this->categoryHightlight = $categoryHightlight;

        return $this;
    }


    // TCMSFieldBoolean

    public function getImage(): ?CmsMedia
    {
        return $this->image;
    }

    public function setImage(?CmsMedia $image): self
    {
        $this->image = $image;

        return $this;
    }


    // TCMSFieldExtendedLookupMedia

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }


    // TCMSFieldPosition

    /**
     * @return Collection<int, ShopCategory>
     */
    public function getShopCategoryCollection(): Collection
    {
        return $this->shopCategoryCollection;
    }

    public function addShopCategoryCollection(ShopCategory $shopCategory): self
    {
        if (!$this->shopCategoryCollection->contains($shopCategory)) {
            $this->shopCategoryCollection->add($shopCategory);
            $shopCategory->setShopCategory($this);
        }

        return $this;
    }



    // TCMSFieldPropertyTable

    public function setShopCategory(?ShopCategory $shopCategory): self
    {
        $this->shopCategory = $shopCategory;

        return $this;
    }

    public function removeShopCategoryCollection(ShopCategory $shopCategory): self
    {
        if ($this->shopCategoryCollection->removeElement($shopCategory)) {
            // set the owning side to null (unless already changed)
            if ($shopCategory->getShopCategory() === $this) {
                $shopCategory->setShopCategory(null);
            }
        }

        return $this;
    }

    public function getShopCategory(): ?ShopCategory
    {
        return $this->shopCategory;
    }


    // TCMSFieldWYSIWYG

    public function getDescriptionShort(): string
    {
        return $this->descriptionShort;
    }

    public function setDescriptionShort(string $descriptionShort): self
    {
        $this->descriptionShort = $descriptionShort;

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


    // TCMSFieldVarchar
    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(string $metaKeywords): self
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }


    // TCMSFieldLookup
    public function getPkgShopListfilter(): ?PkgShopListfilter
    {
        return $this->pkgShopListfilter;
    }

    public function setPkgShopListfilter(?PkgShopListfilter $pkgShopListfilter): self
    {
        $this->pkgShopListfilter = $pkgShopListfilter;

        return $this;
    }



    // TCMSFieldPropertyTable

    /**
     * @return Collection<int, ShopCategoryTab>
     */
    public function getShopCategoryTabCollection(): Collection
    {
        return $this->shopCategoryTabCollection;
    }

    public function addShopCategoryTabCollection(ShopCategoryTab $shopCategoryTab): self
    {
        if (!$this->shopCategoryTabCollection->contains($shopCategoryTab)) {
            $this->shopCategoryTabCollection->add($shopCategoryTab);
            $shopCategoryTab->setShopCategory($this);
        }

        return $this;
    }

    public function removeShopCategoryTabCollection(ShopCategoryTab $shopCategoryTab): self
    {
        if ($this->shopCategoryTabCollection->removeElement($shopCategoryTab)) {
            // set the owning side to null (unless already changed)
            if ($shopCategoryTab->getShopCategory() === $this) {
                $shopCategoryTab->setShopCategory(null);
            }
        }

        return $this;
    }


    // TCMSFieldVarchar
    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): self
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }


}
