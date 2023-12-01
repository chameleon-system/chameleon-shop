<?php
namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandler;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use ChameleonSystem\DataAccessBundle\Entity\CoreMedia\cmsMedia;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetGroup;
use ChameleonSystem\DataAccessBundle\Entity\Core\dataCountry;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopPaymentMethod {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Belongs to payment provider */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Internal system name */
private string $NameInternal = '', 
    // TCMSFieldLookup
/** @var shopPaymentHandler|null - Payment handler */
private ?shopPaymentHandler $ShopPaymentHandler = null
, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldBoolean
/** @var bool - Allow for Packstation delivery addresses */
private bool $PkgDhlPackstationAllowForPackstation = true, 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $Position = 0, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, cmsPortal> - Restrict to the following portals */
private Collection $CmsPortalCollection = new ArrayCollection()
, 
    // TCMSFieldPrice
/** @var float - Available from merchandise value */
private float $RestrictToValueFrom = 0, 
    // TCMSFieldPrice
/** @var float - Available until merchandise value */
private float $RestrictToValueTo = 0, 
    // TCMSFieldDecimal
/** @var float - Available from basket value */
private float $RestrictToBasketValueFrom = 0, 
    // TCMSFieldDecimal
/** @var float - Available to basket value */
private float $RestrictToBasketValueTo = 0, 
    // TCMSFieldPrice
/** @var float - Additional costs */
private float $Value = 0, 
    // TCMSFieldOption
/** @var string - Additional costs type */
private string $ValueType = 'absolut', 
    // TCMSFieldLookup
/** @var shopVat|null - VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldExtendedLookupMedia
/** @var cmsMedia|null - Icon */
private ?cmsMedia $CmsMedia = null
, 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetUser> - Restrict to following customers */
private Collection $DataExtranetUserCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetGroup> - Restrict to following customer groups */
private Collection $DataExtranetGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataCountry> - Restrict to following shipping countries */
private Collection $DataCountryCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataCountry> - Restrict to following billing countries */
private Collection $DataCountryBilCollection = new ArrayCollection()
, 
    // TCMSFieldBoolean
/** @var bool - Use not fixed positive list match */
private bool $PositivListLooseMatch = false, 
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
/** @var Collection<int, shopArticleGroup> - Do not allow for following product groups */
private Collection $ShopArticleGroup1Collection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Do not allow for following product categories */
private Collection $ShopCategory1Collection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticle> - Do not allow for following products */
private Collection $ShopArticle1Collection = new ArrayCollection()
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
public function getShopPaymentHandlerGroup(): ?shopPaymentHandlerGroup
{
    return $this->ShopPaymentHandlerGroup;
}

public function setShopPaymentHandlerGroup(?shopPaymentHandlerGroup $ShopPaymentHandlerGroup): self
{
    $this->ShopPaymentHandlerGroup = $ShopPaymentHandlerGroup;

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


  
    // TCMSFieldVarchar
public function getnameInternal(): string
{
    return $this->NameInternal;
}
public function setnameInternal(string $NameInternal): self
{
    $this->NameInternal = $NameInternal;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopPaymentHandler(): ?shopPaymentHandler
{
    return $this->ShopPaymentHandler;
}

public function setShopPaymentHandler(?shopPaymentHandler $ShopPaymentHandler): self
{
    $this->ShopPaymentHandler = $ShopPaymentHandler;

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
public function ispkgDhlPackstationAllowForPackstation(): bool
{
    return $this->PkgDhlPackstationAllowForPackstation;
}
public function setpkgDhlPackstationAllowForPackstation(bool $PkgDhlPackstationAllowForPackstation): self
{
    $this->PkgDhlPackstationAllowForPackstation = $PkgDhlPackstationAllowForPackstation;

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


  
    // TCMSFieldDecimal
public function getrestrictToBasketValueFrom(): float
{
    return $this->RestrictToBasketValueFrom;
}
public function setrestrictToBasketValueFrom(float $RestrictToBasketValueFrom): self
{
    $this->RestrictToBasketValueFrom = $RestrictToBasketValueFrom;

    return $this;
}


  
    // TCMSFieldDecimal
public function getrestrictToBasketValueTo(): float
{
    return $this->RestrictToBasketValueTo;
}
public function setrestrictToBasketValueTo(float $RestrictToBasketValueTo): self
{
    $this->RestrictToBasketValueTo = $RestrictToBasketValueTo;

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


  
    // TCMSFieldExtendedLookupMedia
public function getCmsMedia(): ?cmsMedia
{
    return $this->CmsMedia;
}

public function setCmsMedia(?cmsMedia $CmsMedia): self
{
    $this->CmsMedia = $CmsMedia;

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
* @return Collection<int, dataCountry>
*/
public function getDataCountryBilCollection(): Collection
{
    return $this->DataCountryBilCollection;
}

public function addDataCountryBilCollection(dataCountry $DataCountryBilling): self
{
    if (!$this->DataCountryBilCollection->contains($DataCountryBilling)) {
        $this->DataCountryBilCollection->add($DataCountryBilling);
        $DataCountryBilling->set($this);
    }

    return $this;
}

public function removeDataCountryBilCollection(dataCountry $DataCountryBilling): self
{
    if ($this->DataCountryBilCollection->removeElement($DataCountryBilling)) {
        // set the owning side to null (unless already changed)
        if ($DataCountryBilling->get() === $this) {
            $DataCountryBilling->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldBoolean
public function ispositivListLooseMatch(): bool
{
    return $this->PositivListLooseMatch;
}
public function setpositivListLooseMatch(bool $PositivListLooseMatch): self
{
    $this->PositivListLooseMatch = $PositivListLooseMatch;

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
* @return Collection<int, shopArticleGroup>
*/
public function getShopArticleGroup1Collection(): Collection
{
    return $this->ShopArticleGroup1Collection;
}

public function addShopArticleGroup1Collection(shopArticleGroup $ShopArticleGroup1Mlt): self
{
    if (!$this->ShopArticleGroup1Collection->contains($ShopArticleGroup1Mlt)) {
        $this->ShopArticleGroup1Collection->add($ShopArticleGroup1Mlt);
        $ShopArticleGroup1Mlt->set($this);
    }

    return $this;
}

public function removeShopArticleGroup1Collection(shopArticleGroup $ShopArticleGroup1Mlt): self
{
    if ($this->ShopArticleGroup1Collection->removeElement($ShopArticleGroup1Mlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticleGroup1Mlt->get() === $this) {
            $ShopArticleGroup1Mlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopCategory>
*/
public function getShopCategory1Collection(): Collection
{
    return $this->ShopCategory1Collection;
}

public function addShopCategory1Collection(shopCategory $ShopCategory1Mlt): self
{
    if (!$this->ShopCategory1Collection->contains($ShopCategory1Mlt)) {
        $this->ShopCategory1Collection->add($ShopCategory1Mlt);
        $ShopCategory1Mlt->set($this);
    }

    return $this;
}

public function removeShopCategory1Collection(shopCategory $ShopCategory1Mlt): self
{
    if ($this->ShopCategory1Collection->removeElement($ShopCategory1Mlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopCategory1Mlt->get() === $this) {
            $ShopCategory1Mlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopArticle>
*/
public function getShopArticle1Collection(): Collection
{
    return $this->ShopArticle1Collection;
}

public function addShopArticle1Collection(shopArticle $ShopArticle1Mlt): self
{
    if (!$this->ShopArticle1Collection->contains($ShopArticle1Mlt)) {
        $this->ShopArticle1Collection->add($ShopArticle1Mlt);
        $ShopArticle1Mlt->set($this);
    }

    return $this;
}

public function removeShopArticle1Collection(shopArticle $ShopArticle1Mlt): self
{
    if ($this->ShopArticle1Collection->removeElement($ShopArticle1Mlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopArticle1Mlt->get() === $this) {
            $ShopArticle1Mlt->set(null);
        }
    }

    return $this;
}


  
}
