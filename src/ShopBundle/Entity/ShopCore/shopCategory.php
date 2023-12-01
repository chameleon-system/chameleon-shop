<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTree;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopListfilter;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategoryTab;

class shopCategory {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopCategory|null - Subcategory of */
private ?shopCategory $ShopCategory = null
, 
    // TCMSFieldTreeNode
/** @var cmsTree|null - Template for the details page */
private ?cmsTree $DetailPageCmsTree = null
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Icon for navigation */
private ?cmsMedia $NaviIconCmsMedia = null
, 
    // TCMSFieldText
/** @var string - URL path */
private string $UrlPath = '', 
    // TCMSFieldVarchar
/** @var string - Category name */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = true, 
    // TCMSFieldBoolean
/** @var bool - Is the tree active up to this category? */
private bool $TreeActive = true, 
    // TCMSFieldVarchar
/** @var string - Additional product name */
private string $NameProduct = '', 
    // TCMSFieldVarchar
/** @var string - SEO pattern */
private string $SeoPattern = '', 
    // TCMSFieldLookup
/** @var shopVat|null - VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldColorpicker
/** @var string - Color code */
private string $Colorcode = '', 
    // TCMSFieldBoolean
/** @var bool - Highlight category */
private bool $CategoryHightlight = false, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Category image */
private ?cmsMedia $Im = null
, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopCategory> - Subcategories */
private Collection $ShopCategoryCollection = new ArrayCollection()
, 
    // TCMSFieldWYSIWYG
/** @var string - Short description of the category */
private string $DescriptionShort = '', 
    // TCMSFieldWYSIWYG
/** @var string - Detailed description of the category */
private string $Description = '', 
    // TCMSFieldVarchar
/** @var string - Meta keywords */
private string $MetaKeywords = '', 
    // TCMSFieldLookup
/** @var pkgShopListfilter|null - List filter for the category */
private ?pkgShopListfilter $PkgShopListfilter = null
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopCategoryTab> - Category */
private Collection $ShopCategoryTabCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Meta description */
private string $MetaDescription = ''  ) {}

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
public function getShopCategory(): ?shopCategory
{
    return $this->ShopCategory;
}

public function setShopCategory(?shopCategory $ShopCategory): self
{
    $this->ShopCategory = $ShopCategory;

    return $this;
}


  
    // TCMSFieldTreeNode
public function getDetailPageCmsTree(): ?cmsTree
{
    return $this->DetailPageCmsTree;
}

public function setDetailPageCmsTree(?cmsTree $DetailPageCmsTree): self
{
    $this->DetailPageCmsTree = $DetailPageCmsTree;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getNaviIconCmsMedia(): ?cmsMedia
{
    return $this->NaviIconCmsMedia;
}

public function setNaviIconCmsMedia(?cmsMedia $NaviIconCmsMedia): self
{
    $this->NaviIconCmsMedia = $NaviIconCmsMedia;

    return $this;
}


  
    // TCMSFieldText
public function geturlPath(): string
{
    return $this->UrlPath;
}
public function seturlPath(string $UrlPath): self
{
    $this->UrlPath = $UrlPath;

    return $this;
}


  
    // TCMSFieldVarchar
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

    return $this;
}


  
    // TCMSFieldBoolean
public function isactive(): bool
{
    return $this->Active;
}
public function setactive(bool $Active): self
{
    $this->Active = $Active;

    return $this;
}


  
    // TCMSFieldBoolean
public function istreeActive(): bool
{
    return $this->TreeActive;
}
public function settreeActive(bool $TreeActive): self
{
    $this->TreeActive = $TreeActive;

    return $this;
}


  
    // TCMSFieldVarchar
public function getnameProduct(): string
{
    return $this->NameProduct;
}
public function setnameProduct(string $NameProduct): self
{
    $this->NameProduct = $NameProduct;

    return $this;
}


  
    // TCMSFieldVarchar
public function getseoPattern(): string
{
    return $this->SeoPattern;
}
public function setseoPattern(string $SeoPattern): self
{
    $this->SeoPattern = $SeoPattern;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopVat(): ?shopVat
{
    return $this->ShopVat;
}

public function setShopVat(?shopVat $ShopVat): self
{
    $this->ShopVat = $ShopVat;

    return $this;
}


  
    // TCMSFieldColorpicker
public function getcolorcode(): string
{
    return $this->Colorcode;
}
public function setcolorcode(string $Colorcode): self
{
    $this->Colorcode = $Colorcode;

    return $this;
}


  
    // TCMSFieldBoolean
public function iscategoryHightlight(): bool
{
    return $this->CategoryHightlight;
}
public function setcategoryHightlight(bool $CategoryHightlight): self
{
    $this->CategoryHightlight = $CategoryHightlight;

    return $this;
}


  
    // TCMSFieldExtendedLookupMedia
public function getIm(): ?cmsMedia
{
    return $this->Im;
}

public function setIm(?cmsMedia $Im): self
{
    $this->Im = $Im;

    return $this;
}


  
    // TCMSFieldPosition
public function getposition(): int
{
    return $this->Position;
}
public function setposition(int $Position): self
{
    $this->Position = $Position;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopCategory>
*/
public function getShopCategoryCollection(): Collection
{
    return $this->ShopCategoryCollection;
}

public function addShopCategoryCollection(shopCategory $ShopCategory): self
{
    if (!$this->ShopCategoryCollection->contains($ShopCategory)) {
        $this->ShopCategoryCollection->add($ShopCategory);
        $ShopCategory->setShopCategory($this);
    }

    return $this;
}

public function removeShopCategoryCollection(shopCategory $ShopCategory): self
{
    if ($this->ShopCategoryCollection->removeElement($ShopCategory)) {
        // set the owning side to null (unless already changed)
        if ($ShopCategory->getShopCategory() === $this) {
            $ShopCategory->setShopCategory(null);
        }
    }

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescriptionShort(): string
{
    return $this->DescriptionShort;
}
public function setdescriptionShort(string $DescriptionShort): self
{
    $this->DescriptionShort = $DescriptionShort;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescription(): string
{
    return $this->Description;
}
public function setdescription(string $Description): self
{
    $this->Description = $Description;

    return $this;
}


  
    // TCMSFieldVarchar
public function getmetaKeywords(): string
{
    return $this->MetaKeywords;
}
public function setmetaKeywords(string $MetaKeywords): self
{
    $this->MetaKeywords = $MetaKeywords;

    return $this;
}


  
    // TCMSFieldLookup
public function getPkgShopListfilter(): ?pkgShopListfilter
{
    return $this->PkgShopListfilter;
}

public function setPkgShopListfilter(?pkgShopListfilter $PkgShopListfilter): self
{
    $this->PkgShopListfilter = $PkgShopListfilter;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopCategoryTab>
*/
public function getShopCategoryTabCollection(): Collection
{
    return $this->ShopCategoryTabCollection;
}

public function addShopCategoryTabCollection(shopCategoryTab $ShopCategoryTab): self
{
    if (!$this->ShopCategoryTabCollection->contains($ShopCategoryTab)) {
        $this->ShopCategoryTabCollection->add($ShopCategoryTab);
        $ShopCategoryTab->setShopCategory($this);
    }

    return $this;
}

public function removeShopCategoryTabCollection(shopCategoryTab $ShopCategoryTab): self
{
    if ($this->ShopCategoryTabCollection->removeElement($ShopCategoryTab)) {
        // set the owning side to null (unless already changed)
        if ($ShopCategoryTab->getShopCategory() === $this) {
            $ShopCategoryTab->setShopCategory(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getmetaDescription(): string
{
    return $this->MetaDescription;
}
public function setmetaDescription(string $MetaDescription): self
{
    $this->MetaDescription = $MetaDescription;

    return $this;
}


  
}
