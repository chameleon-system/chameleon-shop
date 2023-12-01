<?php
namespace ChameleonSystem\ShopBundle\Entity\ProductList;

use ChameleonSystem\DataAccessBundle\Entity\CorePagedef\cmsTplModuleInstance;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticleListFilter;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticlelistOrderby;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticleListArticle;

class shopModuleArticleList {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var cmsTplModuleInstance|null - Belongs to module instance */
private ?cmsTplModuleInstance $CmsTplModuleInstance = null
, 
    // TCMSFieldBoolean
/** @var bool - Release for the Post-Search-Filter */
private bool $CanBeFiltered = false, 
    // TCMSFieldVarchar
/** @var string - Headline */
private string $Name = '', 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Icon */
private ?cmsMedia $I = null
, 
    // TCMSFieldLookup
/** @var shopModuleArticleListFilter|null - Filter / content */
private ?shopModuleArticleListFilter $ShopModuleArticleListFilter = null
, 
    // TCMSFieldLookup
/** @var shopModuleArticlelistOrderby|null - Sorting */
private ?shopModuleArticlelistOrderby $ShopModuleArticlelistOrderby = null
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, shopModuleArticlelistOrderby> - Available sortings */
private Collection $ShopModuleArticlelistOrderbyCollection = new ArrayCollection()
, 
    // TCMSFieldNumber
/** @var int - Number of articles shown */
private int $NumberOfArticles = -1, 
    // TCMSFieldNumber
/** @var int - Number of articles per page */
private int $NumberOfArticlesPerPage = 10, 
    // TCMSFieldWYSIWYG
/** @var string - Introduction text */
private string $DescriptionStart = '', 
    // TCMSFieldWYSIWYG
/** @var string - Closing text */
private string $DescriptionEnd = '', 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticleGroup> - Show articles from these article groups */
private Collection $ShopArticleGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Show articles from these product categories */
private Collection $ShopCategoryCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopModuleArticleListArticle> - Show these articles */
private Collection $ShopModuleArticleListArticleCollection = new ArrayCollection()
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
public function getCmsTplModuleInstance(): ?cmsTplModuleInstance
{
    return $this->CmsTplModuleInstance;
}

public function setCmsTplModuleInstance(?cmsTplModuleInstance $CmsTplModuleInstance): self
{
    $this->CmsTplModuleInstance = $CmsTplModuleInstance;

    return $this;
}


  
    // TCMSFieldBoolean
public function iscanBeFiltered(): bool
{
    return $this->CanBeFiltered;
}
public function setcanBeFiltered(bool $CanBeFiltered): self
{
    $this->CanBeFiltered = $CanBeFiltered;

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


  
    // TCMSFieldExtendedLookupMedia
public function getI(): ?cmsMedia
{
    return $this->I;
}

public function setI(?cmsMedia $I): self
{
    $this->I = $I;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopModuleArticleListFilter(): ?shopModuleArticleListFilter
{
    return $this->ShopModuleArticleListFilter;
}

public function setShopModuleArticleListFilter(?shopModuleArticleListFilter $ShopModuleArticleListFilter): self
{
    $this->ShopModuleArticleListFilter = $ShopModuleArticleListFilter;

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


  
    // TCMSFieldNumber
public function getnumberOfArticles(): int
{
    return $this->NumberOfArticles;
}
public function setnumberOfArticles(int $NumberOfArticles): self
{
    $this->NumberOfArticles = $NumberOfArticles;

    return $this;
}


  
    // TCMSFieldNumber
public function getnumberOfArticlesPerPage(): int
{
    return $this->NumberOfArticlesPerPage;
}
public function setnumberOfArticlesPerPage(int $NumberOfArticlesPerPage): self
{
    $this->NumberOfArticlesPerPage = $NumberOfArticlesPerPage;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescriptionStart(): string
{
    return $this->DescriptionStart;
}
public function setdescriptionStart(string $DescriptionStart): self
{
    $this->DescriptionStart = $DescriptionStart;

    return $this;
}


  
    // TCMSFieldWYSIWYG
public function getdescriptionEnd(): string
{
    return $this->DescriptionEnd;
}
public function setdescriptionEnd(string $DescriptionEnd): self
{
    $this->DescriptionEnd = $DescriptionEnd;

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopArticleGroup>
*/
public function getShopArticleGroupCollection(): Collection
{
    return $this->ShopArticleGroupCollection;
}

public function addShopArticleGroupCollection(shopArticleGroup $ShopArticleGroupMlt): self
{
    if (!$this->ShopArticleGroupCollection->contains($ShopArticleGroupMlt)) {
        $this->ShopArticleGroupCollection->add($ShopArticleGroupMlt);
        $ShopArticleGroupMlt->set($this);
    }

    return $this;
}

public function removeShopArticleGroupCollection(shopArticleGroup $ShopArticleGroupMlt): self
{
    if ($this->ShopArticleGroupCollection->removeElement($ShopArticleGroupMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleGroupMlt->get() === $this) {
            $ShopArticleGroupMlt->set(null);
        }
    }

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


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopModuleArticleListArticle>
*/
public function getShopModuleArticleListArticleCollection(): Collection
{
    return $this->ShopModuleArticleListArticleCollection;
}

public function addShopModuleArticleListArticleCollection(shopModuleArticleListArticle $ShopModuleArticleListArticle): self
{
    if (!$this->ShopModuleArticleListArticleCollection->contains($ShopModuleArticleListArticle)) {
        $this->ShopModuleArticleListArticleCollection->add($ShopModuleArticleListArticle);
        $ShopModuleArticleListArticle->setShopModuleArticleList($this);
    }

    return $this;
}

public function removeShopModuleArticleListArticleCollection(shopModuleArticleListArticle $ShopModuleArticleListArticle): self
{
    if ($this->ShopModuleArticleListArticleCollection->removeElement($ShopModuleArticleListArticle)) {
        // set the owning side to null (unless already changed)
        if ($ShopModuleArticleListArticle->getShopModuleArticleList() === $this) {
            $ShopModuleArticleListArticle->setShopModuleArticleList(null);
        }
    }

    return $this;
}


  
}
