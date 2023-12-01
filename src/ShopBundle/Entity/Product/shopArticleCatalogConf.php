<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleCatalogConfDefaultOrder;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticlelistOrderby;

class shopArticleCatalogConf {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldVarchar
/** @var string - Title / headline */
private string $Name = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopArticleCatalogConfDefaultOrder> - Alternative default sorting */
private Collection $ShopArticleCatalogConfDefaultOrderCollection = new ArrayCollection()
, 
    // TCMSFieldBoolean
/** @var bool - Offer Reserving at 0 stock */
private bool $ShowSubcategoryProducts = false, 
    // TCMSFieldNumber
/** @var int - Articles per page */
private int $PageSize = 20, 
    // TCMSFieldLookup
/** @var shopModuleArticlelistOrderby|null - Default sorting */
private ?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby = null
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shopModuleArticlelistOrderby> - Available sortings */
private Collection $ShopModuleArticlelistOrderbyCollection = new ArrayCollection()
, 
    // TCMSFieldWYSIWYG
/** @var string - Introduction text */
private string $Intro = ''  ) {}

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
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

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


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopArticleCatalogConfDefaultOrder>
*/
public function getShopArticleCatalogConfDefaultOrderCollection(): Collection
{
    return $this->ShopArticleCatalogConfDefaultOrderCollection;
}

public function addShopArticleCatalogConfDefaultOrderCollection(shopArticleCatalogConfDefaultOrder $ShopArticleCatalogConfDefaultOrder): self
{
    if (!$this->ShopArticleCatalogConfDefaultOrderCollection->contains($ShopArticleCatalogConfDefaultOrder)) {
        $this->ShopArticleCatalogConfDefaultOrderCollection->add($ShopArticleCatalogConfDefaultOrder);
        $ShopArticleCatalogConfDefaultOrder->setShopArticleCatalogConf($this);
    }

    return $this;
}

public function removeShopArticleCatalogConfDefaultOrderCollection(shopArticleCatalogConfDefaultOrder $ShopArticleCatalogConfDefaultOrder): self
{
    if ($this->ShopArticleCatalogConfDefaultOrderCollection->removeElement($ShopArticleCatalogConfDefaultOrder)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleCatalogConfDefaultOrder->getShopArticleCatalogConf() === $this) {
            $ShopArticleCatalogConfDefaultOrder->setShopArticleCatalogConf(null);
        }
    }

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowSubcategoryProducts(): bool
{
    return $this->ShowSubcategoryProducts;
}
public function setshowSubcategoryProducts(bool $ShowSubcategoryProducts): self
{
    $this->ShowSubcategoryProducts = $ShowSubcategoryProducts;

    return $this;
}


  
    // TCMSFieldNumber
public function getpageSize(): int
{
    return $this->PageSize;
}
public function setpageSize(int $PageSize): self
{
    $this->PageSize = $PageSize;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopModuleArticlelistOrderby(): ?shopModuleArticlelistOrderby
{
    return $this->ShopModuleArticlelistOrderby;
}

public function setShopModuleArticlelistOrderby(?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby): self
{
    $this->ShopModuleArticlelistOrderby = $ShopModuleArticlelistOrderby;

    return $this;
}


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, shopModuleArticlelistOrderby>
*/
public function getShopModuleArticlelistOrderbyCollection(): Collection
{
    return $this->ShopModuleArticlelistOrderbyCollection;
}

public function addShopModuleArticlelistOrderbyCollection(shopModuleArticlelistOrderby $ShopModuleArticlelistOrderbyMlt): self
{
    if (!$this->ShopModuleArticlelistOrderbyCollection->contains($ShopModuleArticlelistOrderbyMlt)) {
        $this->ShopModuleArticlelistOrderbyCollection->add($ShopModuleArticlelistOrderbyMlt);
        $ShopModuleArticlelistOrderbyMlt->set($this);
    }

    return $this;
}

public function removeShopModuleArticlelistOrderbyCollection(shopModuleArticlelistOrderby $ShopModuleArticlelistOrderbyMlt): self
{
    if ($this->ShopModuleArticlelistOrderbyCollection->removeElement($ShopModuleArticlelistOrderbyMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopModuleArticlelistOrderbyMlt->get() === $this) {
            $ShopModuleArticlelistOrderbyMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getintro(): string
{
    return $this->Intro;
}
public function setintro(string $Intro): self
{
    $this->Intro = $Intro;

    return $this;
}


  
}
