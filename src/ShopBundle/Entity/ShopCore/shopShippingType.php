<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticleGroup;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataCountry;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetGroup;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class shopShippingType {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldPrice
/** @var float - Additional costs */
private float $Value = 0, 
    // TCMSFieldOption
/** @var string - Addtional costs type */
private string $ValueType = 'absolut', 
    // TCMSFieldBoolean
/** @var bool - Value relates to the whole basket */
private bool $ValueBasedOnEntireBasket = false, 
    // TCMSFieldPrice
/** @var float - Additional charges */
private float $ValueAdditional = 0, 
    // TCMSFieldPrice
/** @var float - Maximum additional charges */
private float $ValueMax = 0, 
    // TCMSFieldPrice
/** @var float - Minimum additional charges */
private float $ValueMin = 0, 
    // TCMSFieldBoolean
/** @var bool - Calculate shipping costs for each item separately */
private bool $AddValueForEachArticle = false, 
    // TCMSFieldBoolean
/** @var bool - Use for logged in users only */
private bool $RestrictToSignedInUsers = false, 
    // TCMSFieldBoolean
/** @var bool - Apply to all products with at least one match */
private bool $ApplyToAllProducts = false, 
    // TCMSFieldBoolean
/** @var bool - When applied, ignore all other shipping costs types */
private bool $EndShippingTypeChain = false, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active as of */
private ?\DateTime $ActiveFrom = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active until */
private ?\DateTime $ActiveTo = null, 
    // TCMSFieldPrice
/** @var float - Minimum value of affected items (Euro) */
private float $RestrictToValueFrom = 0, 
    // TCMSFieldPrice
/** @var float - Maximum value of affected items (Euro) */
private float $RestrictToValueTo = 0, 
    // TCMSFieldNumber
/** @var int - Minimum amount of items affected */
private int $RestrictToArticlesFrom = 0, 
    // TCMSFieldNumber
/** @var int - Maximum amount of items affected */
private int $RestrictToArticlesTo = 0, 
    // TCMSFieldNumber
/** @var int - Minimum weight of affected items (grams) */
private int $RestrictToWeightFrom = 0, 
    // TCMSFieldNumber
/** @var int - Maximum weight of affected items (grams) */
private int $RestrictToWeightTo = 0, 
    // TCMSFieldDecimal
/** @var float - Minimum volume of affected items (cubic meters) */
private float $RestrictToVolumeFrom = 0, 
    // TCMSFieldDecimal
/** @var float - Maximum volume of affected items (cubic meters) */
private float $RestrictToVolumeTo = 0, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticleGroup> - Restrict to following product groups */
private Collection $ShopArticleGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Restrict to following product categories */
private Collection $ShopCategoryCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticle> - Restrict to following items */
private Collection $ShopArticleCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataCountry> - Restrict to following shipping countries */
private Collection $DataCountryCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetUser> - Restrict to following users */
private Collection $DataExtranetUserCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetGroup> - Restrict to following customer groups */
private Collection $DataExtranetGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, cmsPortal> - Restrict to following portals */
private Collection $CmsPortalCollection = new ArrayCollection()
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
public function isvalueBasedOnEntireBasket(): bool
{
    return $this->ValueBasedOnEntireBasket;
}
public function setvalueBasedOnEntireBasket(bool $ValueBasedOnEntireBasket): self
{
    $this->ValueBasedOnEntireBasket = $ValueBasedOnEntireBasket;

    return $this;
}


  
    // TCMSFieldPrice
public function getvalueAdditional(): float
{
    return $this->ValueAdditional;
}
public function setvalueAdditional(float $ValueAdditional): self
{
    $this->ValueAdditional = $ValueAdditional;

    return $this;
}


  
    // TCMSFieldPrice
public function getvalueMax(): float
{
    return $this->ValueMax;
}
public function setvalueMax(float $ValueMax): self
{
    $this->ValueMax = $ValueMax;

    return $this;
}


  
    // TCMSFieldPrice
public function getvalueMin(): float
{
    return $this->ValueMin;
}
public function setvalueMin(float $ValueMin): self
{
    $this->ValueMin = $ValueMin;

    return $this;
}


  
    // TCMSFieldBoolean
public function isaddValueForEachArticle(): bool
{
    return $this->AddValueForEachArticle;
}
public function setaddValueForEachArticle(bool $AddValueForEachArticle): self
{
    $this->AddValueForEachArticle = $AddValueForEachArticle;

    return $this;
}


  
    // TCMSFieldBoolean
public function isrestrictToSignedInUsers(): bool
{
    return $this->RestrictToSignedInUsers;
}
public function setrestrictToSignedInUsers(bool $RestrictToSignedInUsers): self
{
    $this->RestrictToSignedInUsers = $RestrictToSignedInUsers;

    return $this;
}


  
    // TCMSFieldBoolean
public function isapplyToAllProducts(): bool
{
    return $this->ApplyToAllProducts;
}
public function setapplyToAllProducts(bool $ApplyToAllProducts): self
{
    $this->ApplyToAllProducts = $ApplyToAllProducts;

    return $this;
}


  
    // TCMSFieldBoolean
public function isendShippingTypeChain(): bool
{
    return $this->EndShippingTypeChain;
}
public function setendShippingTypeChain(bool $EndShippingTypeChain): self
{
    $this->EndShippingTypeChain = $EndShippingTypeChain;

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


  
    // TCMSFieldNumber
public function getrestrictToWeightFrom(): int
{
    return $this->RestrictToWeightFrom;
}
public function setrestrictToWeightFrom(int $RestrictToWeightFrom): self
{
    $this->RestrictToWeightFrom = $RestrictToWeightFrom;

    return $this;
}


  
    // TCMSFieldNumber
public function getrestrictToWeightTo(): int
{
    return $this->RestrictToWeightTo;
}
public function setrestrictToWeightTo(int $RestrictToWeightTo): self
{
    $this->RestrictToWeightTo = $RestrictToWeightTo;

    return $this;
}


  
    // TCMSFieldDecimal
public function getrestrictToVolumeFrom(): float
{
    return $this->RestrictToVolumeFrom;
}
public function setrestrictToVolumeFrom(float $RestrictToVolumeFrom): self
{
    $this->RestrictToVolumeFrom = $RestrictToVolumeFrom;

    return $this;
}


  
    // TCMSFieldDecimal
public function getrestrictToVolumeTo(): float
{
    return $this->RestrictToVolumeTo;
}
public function setrestrictToVolumeTo(float $RestrictToVolumeTo): self
{
    $this->RestrictToVolumeTo = $RestrictToVolumeTo;

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


  
    // TCMSFieldLookupMultiselect
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


  
    // TCMSFieldLookupMultiselect
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


  
    // TCMSFieldLookupMultiselect
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


  
    // TCMSFieldLookupMultiselectCheckboxes
/**
* @return Collection<int, cmsPortal>
*/
public function getCmsPortalCollection(): Collection
{
    return $this->CmsPortalCollection;
}

public function addCmsPortalCollection(cmsPortal $CmsPortalMlt): self
{
    if (!$this->CmsPortalCollection->contains($CmsPortalMlt)) {
        $this->CmsPortalCollection->add($CmsPortalMlt);
        $CmsPortalMlt->set($this);
    }

    return $this;
}

public function removeCmsPortalCollection(cmsPortal $CmsPortalMlt): self
{
    if ($this->CmsPortalCollection->removeElement($CmsPortalMlt)) {
        // set the owning side to null (unless already changed)
        if ($CmsPortalMlt->get() === $this) {
            $CmsPortalMlt->set(null);
        }
    }

    return $this;
}


  
}
