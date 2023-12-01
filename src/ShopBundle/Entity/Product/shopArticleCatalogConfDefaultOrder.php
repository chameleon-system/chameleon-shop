<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticleCatalogConf;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticlelistOrderby;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopArticleCatalogConfDefaultOrder {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticleCatalogConf|null - Belongs to configuration */
private ?shopArticleCatalogConf $ShopArticleCatalogConf = null
, 
    // TCMSFieldVarchar
/** @var string - Name (description) */
private string $Name = '', 
    // TCMSFieldLookup
/** @var shopModuleArticlelistOrderby|null - Sorting */
private ?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby = null
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Category */
private Collection $ShopCategoryCollection = new ArrayCollection()
  ) {}

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
public function getShopArticleCatalogConf(): ?shopArticleCatalogConf
{
    return $this->ShopArticleCatalogConf;
}

public function setShopArticleCatalogConf(?shopArticleCatalogConf $ShopArticleCatalogConf): self
{
    $this->ShopArticleCatalogConf = $ShopArticleCatalogConf;

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


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopCategory>
*/
public function getShopCategoryCollection(): Collection
{
    return $this->ShopCategoryCollection;
}

public function addShopCategoryCollection(shopCategory $ShopCategoryMlt): self
{
    if (!$this->ShopCategoryCollection->contains($ShopCategoryMlt)) {
        $this->ShopCategoryCollection->add($ShopCategoryMlt);
        $ShopCategoryMlt->set($this);
    }

    return $this;
}

public function removeShopCategoryCollection(shopCategory $ShopCategoryMlt): self
{
    if ($this->ShopCategoryCollection->removeElement($ShopCategoryMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopCategoryMlt->get() === $this) {
            $ShopCategoryMlt->set(null);
        }
    }

    return $this;
}


  
}
