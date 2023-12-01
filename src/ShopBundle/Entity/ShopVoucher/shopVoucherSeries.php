<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucherSeriesSponsor;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetGroup;
use ChameleonSystem\ShopBundle\Entity\Product\shopManufacturer;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticleGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucher;

class shopVoucherSeries {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldLookup
/** @var shopVoucherSeriesSponsor|null - Voucher sponsor */
private ?shopVoucherSeriesSponsor $ShopVoucherSeriesSponsor = null
, 
    // TCMSFieldPrice
/** @var float - Value */
private float $Value = 0, 
    // TCMSFieldOption
/** @var string - Value type */
private string $ValueType = 'absolut', 
    // TCMSFieldLookup
/** @var shopVat|null - VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldBoolean
/** @var bool - Free shipping */
private bool $FreeShipping = false, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active from */
private ?\DateTime $ActiveFrom = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active until */
private ?\DateTime $ActiveTo = null, 
    // TCMSFieldPrice
/** @var float - Minimum order value */
private float $RestrictToValue = 0, 
    // TCMSFieldBoolean
/** @var bool - Allow with other series only */
private bool $RestrictToOtherSeries = true, 
    // TCMSFieldBoolean
/** @var bool - Do not allow in combination with other vouchers */
private bool $AllowNoOtherVouchers = true, 
    // TCMSFieldBoolean
/** @var bool - Allow one voucher per customer only */
private bool $RestrictToOnePerUser = false, 
    // TCMSFieldBoolean
/** @var bool - Only allow at first order of a customer */
private bool $RestrictToFirstOrder = false, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetUser> - Restrict to following customers */
private Collection $DataExtranetUserCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetGroup> - Restrict to following customer groups */
private Collection $DataExtranetGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopManufacturer> - Restrict to products from this manufacturer */
private Collection $ShopManufacturerCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticleGroup> - Restrict to products from these product groups */
private Collection $ShopArticleGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopCategory> - Restrict to products from these product categories */
private Collection $ShopCategoryCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopArticle> - Restrict to these products */
private Collection $ShopArticleCollection = new ArrayCollection()
, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopVoucher> - Vouchers belonging to the series */
private Collection $ShopVoucherCollection = new ArrayCollection()
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


  
    // TCMSFieldLookup
public function getShopVoucherSeriesSponsor(): ?shopVoucherSeriesSponsor
{
    return $this->ShopVoucherSeriesSponsor;
}

public function setShopVoucherSeriesSponsor(?shopVoucherSeriesSponsor $ShopVoucherSeriesSponsor): self
{
    $this->ShopVoucherSeriesSponsor = $ShopVoucherSeriesSponsor;

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


  
    // TCMSFieldBoolean
public function isfreeShipping(): bool
{
    return $this->FreeShipping;
}
public function setfreeShipping(bool $FreeShipping): self
{
    $this->FreeShipping = $FreeShipping;

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
public function getrestrictToValue(): float
{
    return $this->RestrictToValue;
}
public function setrestrictToValue(float $RestrictToValue): self
{
    $this->RestrictToValue = $RestrictToValue;

    return $this;
}


  
    // TCMSFieldBoolean
public function isrestrictToOtherSeries(): bool
{
    return $this->RestrictToOtherSeries;
}
public function setrestrictToOtherSeries(bool $RestrictToOtherSeries): self
{
    $this->RestrictToOtherSeries = $RestrictToOtherSeries;

    return $this;
}


  
    // TCMSFieldBoolean
public function isallowNoOtherVouchers(): bool
{
    return $this->AllowNoOtherVouchers;
}
public function setallowNoOtherVouchers(bool $AllowNoOtherVouchers): self
{
    $this->AllowNoOtherVouchers = $AllowNoOtherVouchers;

    return $this;
}


  
    // TCMSFieldBoolean
public function isrestrictToOnePerUser(): bool
{
    return $this->RestrictToOnePerUser;
}
public function setrestrictToOnePerUser(bool $RestrictToOnePerUser): self
{
    $this->RestrictToOnePerUser = $RestrictToOnePerUser;

    return $this;
}


  
    // TCMSFieldBoolean
public function isrestrictToFirstOrder(): bool
{
    return $this->RestrictToFirstOrder;
}
public function setrestrictToFirstOrder(bool $RestrictToFirstOrder): self
{
    $this->RestrictToFirstOrder = $RestrictToFirstOrder;

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
* @return Collection<int, shopManufacturer>
*/
public function getShopManufacturerCollection(): Collection
{
    return $this->ShopManufacturerCollection;
}

public function addShopManufacturerCollection(shopManufacturer $ShopManufacturerMlt): self
{
    if (!$this->ShopManufacturerCollection->contains($ShopManufacturerMlt)) {
        $this->ShopManufacturerCollection->add($ShopManufacturerMlt);
        $ShopManufacturerMlt->set($this);
    }

    return $this;
}

public function removeShopManufacturerCollection(shopManufacturer $ShopManufacturerMlt): self
{
    if ($this->ShopManufacturerCollection->removeElement($ShopManufacturerMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopManufacturerMlt->get() === $this) {
            $ShopManufacturerMlt->set(null);
        }
    }

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


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopVoucher>
*/
public function getShopVoucherCollection(): Collection
{
    return $this->ShopVoucherCollection;
}

public function addShopVoucherCollection(shopVoucher $ShopVoucher): self
{
    if (!$this->ShopVoucherCollection->contains($ShopVoucher)) {
        $this->ShopVoucherCollection->add($ShopVoucher);
        $ShopVoucher->setShopVoucherSeries($this);
    }

    return $this;
}

public function removeShopVoucherCollection(shopVoucher $ShopVoucher): self
{
    if ($this->ShopVoucherCollection->removeElement($ShopVoucher)) {
        // set the owning side to null (unless already changed)
        if ($ShopVoucher->getShopVoucherSeries() === $this) {
            $ShopVoucher->setShopVoucherSeries(null);
        }
    }

    return $this;
}


  
}
