<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetGroup;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataCountry;

class shopDiscount {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldPrice
/** @var float - Value */
private float $Value = 0, 
    // TCMSFieldOption
/** @var string - Value type */
private string $ValueType = 'absolut', 
    // TCMSFieldBoolean
/** @var bool - Show percentual discount on detailed product page */
private bool $ShowDiscountOnArticleDetailpage = false, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Valid from */
private ?\DateTime $ActiveFrom = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active until */
private ?\DateTime $ActiveTo = null, 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $Position = 0, 
    // TCMSFieldNumber
/** @var int - Min. amount of products affected */
private int $RestrictToArticlesFrom = 0, 
    // TCMSFieldNumber
/** @var int - Max. amount of products affected */
private int $RestrictToArticlesTo = 0, 
    // TCMSFieldPrice
/** @var float - Minimum value of affected products (Euro) */
private float $RestrictToValueFrom = 0, 
    // TCMSFieldPrice
/** @var float - Maximum value of affected products (Euro) */
private float $RestrictToValueTo = 0, 
    // TCMSFieldLookupMultiSelectRestriction
/** @var Collection<int, shopCategory> - Restrict to following product categories */
private Collection $ShopCategoryCollection = new ArrayCollection()
,
// TCMSFieldLookupMultiSelectRestriction
/** @var bool - Restrict to following product categories */
private bool $ShopCategoryMltInverseEmpty = false, 
    // TCMSFieldLookupMultiSelectRestriction
/** @var Collection<int, shopArticle> - Restrict to following products */
private Collection $ShopArticleCollection = new ArrayCollection()
,
// TCMSFieldLookupMultiSelectRestriction
/** @var bool - Restrict to following products */
private bool $ShopArticleMltInverseEmpty = false, 
    // TCMSFieldLookupMultiSelectRestriction
/** @var Collection<int, dataExtranetGroup> - Restrict to following customer groups */
private Collection $DataExtranetGroupCollection = new ArrayCollection()
,
// TCMSFieldLookupMultiSelectRestriction
/** @var bool - Restrict to following customer groups */
private bool $DataExtranetGroupMltInverseEmpty = false, 
    // TCMSFieldLookupMultiSelectRestriction
/** @var Collection<int, dataExtranetUser> - Restrict to following customers */
private Collection $DataExtranetUserCollection = new ArrayCollection()
,
// TCMSFieldLookupMultiSelectRestriction
/** @var bool - Restrict to following customers */
private bool $DataExtranetUserMltInverseEmpty = false, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataCountry> - Restrict to following shipping countries */
private Collection $DataCountryCollection = new ArrayCollection()
, 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldDateTime
/** @var \DateTime|null - When has the cache of the affected products been cleared the last time? */
private ?\DateTime $CacheClearLastExecuted = null  ) {}

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
public function getname(): string
{
    return $this->Name;
}
public function setname(string $Name): self
{
    $this->Name = $Name;

    return $this;
}


  
    // TCMSFieldPrice
public function getvalue(): float
{
    return $this->Value;
}
public function setvalue(float $Value): self
{
    $this->Value = $Value;

    return $this;
}


  
    // TCMSFieldOption
public function getvalueType(): string
{
    return $this->ValueType;
}
public function setvalueType(string $ValueType): self
{
    $this->ValueType = $ValueType;

    return $this;
}


  
    // TCMSFieldBoolean
public function isshowDiscountOnArticleDetailpage(): bool
{
    return $this->ShowDiscountOnArticleDetailpage;
}
public function setshowDiscountOnArticleDetailpage(bool $ShowDiscountOnArticleDetailpage): self
{
    $this->ShowDiscountOnArticleDetailpage = $ShowDiscountOnArticleDetailpage;

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


  
    // TCMSFieldDateTime
public function getactiveFrom(): ?\DateTime
{
    return $this->ActiveFrom;
}
public function setactiveFrom(?\DateTime $ActiveFrom): self
{
    $this->ActiveFrom = $ActiveFrom;

    return $this;
}


  
    // TCMSFieldDateTime
public function getactiveTo(): ?\DateTime
{
    return $this->ActiveTo;
}
public function setactiveTo(?\DateTime $ActiveTo): self
{
    $this->ActiveTo = $ActiveTo;

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


  
    // TCMSFieldNumber
public function getrestrictToArticlesFrom(): int
{
    return $this->RestrictToArticlesFrom;
}
public function setrestrictToArticlesFrom(int $RestrictToArticlesFrom): self
{
    $this->RestrictToArticlesFrom = $RestrictToArticlesFrom;

    return $this;
}


  
    // TCMSFieldNumber
public function getrestrictToArticlesTo(): int
{
    return $this->RestrictToArticlesTo;
}
public function setrestrictToArticlesTo(int $RestrictToArticlesTo): self
{
    $this->RestrictToArticlesTo = $RestrictToArticlesTo;

    return $this;
}


  
    // TCMSFieldPrice
public function getrestrictToValueFrom(): float
{
    return $this->RestrictToValueFrom;
}
public function setrestrictToValueFrom(float $RestrictToValueFrom): self
{
    $this->RestrictToValueFrom = $RestrictToValueFrom;

    return $this;
}


  
    // TCMSFieldPrice
public function getrestrictToValueTo(): float
{
    return $this->RestrictToValueTo;
}
public function setrestrictToValueTo(float $RestrictToValueTo): self
{
    $this->RestrictToValueTo = $RestrictToValueTo;

    return $this;
}


  
    // TCMSFieldLookupMultiSelectRestriction
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
// TCMSFieldLookupMultiSelectRestriction
public function isshopCategoryMltInverseEmpty(): bool
{
    return $this->ShopCategoryMltInverseEmpty;
}
public function setshopCategoryMltInverseEmpty(bool $ShopCategoryMltInverseEmpty): self
{
    $this->ShopCategoryMltInverseEmpty = $ShopCategoryMltInverseEmpty;

    return $this;
}


  
    // TCMSFieldLookupMultiSelectRestriction
/**
* @return Collection<int, shopArticle>
*/
public function getShopArticleCollection(): Collection
{
    return $this->ShopArticleCollection;
}

public function addShopArticleCollection(shopArticle $ShopArticleMlt): self
{
    if (!$this->ShopArticleCollection->contains($ShopArticleMlt)) {
        $this->ShopArticleCollection->add($ShopArticleMlt);
        $ShopArticleMlt->set($this);
    }

    return $this;
}

public function removeShopArticleCollection(shopArticle $ShopArticleMlt): self
{
    if ($this->ShopArticleCollection->removeElement($ShopArticleMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleMlt->get() === $this) {
            $ShopArticleMlt->set(null);
        }
    }

    return $this;
}
// TCMSFieldLookupMultiSelectRestriction
public function isshopArticleMltInverseEmpty(): bool
{
    return $this->ShopArticleMltInverseEmpty;
}
public function setshopArticleMltInverseEmpty(bool $ShopArticleMltInverseEmpty): self
{
    $this->ShopArticleMltInverseEmpty = $ShopArticleMltInverseEmpty;

    return $this;
}


  
    // TCMSFieldLookupMultiSelectRestriction
/**
* @return Collection<int, dataExtranetGroup>
*/
public function getDataExtranetGroupCollection(): Collection
{
    return $this->DataExtranetGroupCollection;
}

public function addDataExtranetGroupCollection(dataExtranetGroup $DataExtranetGroupMlt): self
{
    if (!$this->DataExtranetGroupCollection->contains($DataExtranetGroupMlt)) {
        $this->DataExtranetGroupCollection->add($DataExtranetGroupMlt);
        $DataExtranetGroupMlt->set($this);
    }

    return $this;
}

public function removeDataExtranetGroupCollection(dataExtranetGroup $DataExtranetGroupMlt): self
{
    if ($this->DataExtranetGroupCollection->removeElement($DataExtranetGroupMlt)) {
        // set the owning side to null (unless already changed)
        if ($DataExtranetGroupMlt->get() === $this) {
            $DataExtranetGroupMlt->set(null);
        }
    }

    return $this;
}
// TCMSFieldLookupMultiSelectRestriction
public function isdataExtranetGroupMltInverseEmpty(): bool
{
    return $this->DataExtranetGroupMltInverseEmpty;
}
public function setdataExtranetGroupMltInverseEmpty(bool $DataExtranetGroupMltInverseEmpty): self
{
    $this->DataExtranetGroupMltInverseEmpty = $DataExtranetGroupMltInverseEmpty;

    return $this;
}


  
    // TCMSFieldLookupMultiSelectRestriction
/**
* @return Collection<int, dataExtranetUser>
*/
public function getDataExtranetUserCollection(): Collection
{
    return $this->DataExtranetUserCollection;
}

public function addDataExtranetUserCollection(dataExtranetUser $DataExtranetUserMlt): self
{
    if (!$this->DataExtranetUserCollection->contains($DataExtranetUserMlt)) {
        $this->DataExtranetUserCollection->add($DataExtranetUserMlt);
        $DataExtranetUserMlt->set($this);
    }

    return $this;
}

public function removeDataExtranetUserCollection(dataExtranetUser $DataExtranetUserMlt): self
{
    if ($this->DataExtranetUserCollection->removeElement($DataExtranetUserMlt)) {
        // set the owning side to null (unless already changed)
        if ($DataExtranetUserMlt->get() === $this) {
            $DataExtranetUserMlt->set(null);
        }
    }

    return $this;
}
// TCMSFieldLookupMultiSelectRestriction
public function isdataExtranetUserMltInverseEmpty(): bool
{
    return $this->DataExtranetUserMltInverseEmpty;
}
public function setdataExtranetUserMltInverseEmpty(bool $DataExtranetUserMltInverseEmpty): self
{
    $this->DataExtranetUserMltInverseEmpty = $DataExtranetUserMltInverseEmpty;

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, dataCountry>
*/
public function getDataCountryCollection(): Collection
{
    return $this->DataCountryCollection;
}

public function addDataCountryCollection(dataCountry $DataCountryMlt): self
{
    if (!$this->DataCountryCollection->contains($DataCountryMlt)) {
        $this->DataCountryCollection->add($DataCountryMlt);
        $DataCountryMlt->set($this);
    }

    return $this;
}

public function removeDataCountryCollection(dataCountry $DataCountryMlt): self
{
    if ($this->DataCountryCollection->removeElement($DataCountryMlt)) {
        // set the owning side to null (unless already changed)
        if ($DataCountryMlt->get() === $this) {
            $DataCountryMlt->set(null);
        }
    }

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


  
    // TCMSFieldDateTime
public function getcacheClearLastExecuted(): ?\DateTime
{
    return $this->CacheClearLastExecuted;
}
public function setcacheClearLastExecuted(?\DateTime $CacheClearLastExecuted): self
{
    $this->CacheClearLastExecuted = $CacheClearLastExecuted;

    return $this;
}


  
}
