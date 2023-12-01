<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shopShippingGroupHandler;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetGroup;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopVat;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shopShippingType;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentMethod;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class shopShippingGroup {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldLookup
/** @var shopShippingGroupHandler|null - Shipping group handler */
private ?shopShippingGroupHandler $ShopShippingGroupHandler = null
, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldBoolean
/** @var bool - Active */
private bool $Active = false, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active from */
private ?\DateTime $ActiveFrom = null, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Active until */
private ?\DateTime $ActiveTo = null, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetUser> - Restrict to following customers */
private Collection $DataExtranetUserCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, dataExtranetGroup> - Restrict to following customer groups */
private Collection $DataExtranetGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookup
/** @var shopVat|null - VAT group */
private ?shopVat $ShopVat = null
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopShippingType> - Shipping types */
private Collection $ShopShippingTypeCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopPaymentMethod> - Payment methods */
private Collection $ShopPaymentMethodCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselect
/** @var Collection<int, shopShippingGroup> - Is displayed only if the following shipping groups are not available */
private Collection $ShopShippingGroupCollection = new ArrayCollection()
, 
    // TCMSFieldLookupMultiselectCheckboxes
/** @var Collection<int, cmsPortal> - Restrict to the following portals */
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


  
    // TCMSFieldLookup
public function getShopShippingGroupHandler(): ?shopShippingGroupHandler
{
    return $this->ShopShippingGroupHandler;
}

public function setShopShippingGroupHandler(?shopShippingGroupHandler $ShopShippingGroupHandler): self
{
    $this->ShopShippingGroupHandler = $ShopShippingGroupHandler;

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


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopShippingType>
*/
public function getShopShippingTypeCollection(): Collection
{
    return $this->ShopShippingTypeCollection;
}

public function addShopShippingTypeCollection(shopShippingType $ShopShippingTypeMlt): self
{
    if (!$this->ShopShippingTypeCollection->contains($ShopShippingTypeMlt)) {
        $this->ShopShippingTypeCollection->add($ShopShippingTypeMlt);
        $ShopShippingTypeMlt->set($this);
    }

    return $this;
}

public function removeShopShippingTypeCollection(shopShippingType $ShopShippingTypeMlt): self
{
    if ($this->ShopShippingTypeCollection->removeElement($ShopShippingTypeMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopShippingTypeMlt->get() === $this) {
            $ShopShippingTypeMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopPaymentMethod>
*/
public function getShopPaymentMethodCollection(): Collection
{
    return $this->ShopPaymentMethodCollection;
}

public function addShopPaymentMethodCollection(shopPaymentMethod $ShopPaymentMethodMlt): self
{
    if (!$this->ShopPaymentMethodCollection->contains($ShopPaymentMethodMlt)) {
        $this->ShopPaymentMethodCollection->add($ShopPaymentMethodMlt);
        $ShopPaymentMethodMlt->set($this);
    }

    return $this;
}

public function removeShopPaymentMethodCollection(shopPaymentMethod $ShopPaymentMethodMlt): self
{
    if ($this->ShopPaymentMethodCollection->removeElement($ShopPaymentMethodMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopPaymentMethodMlt->get() === $this) {
            $ShopPaymentMethodMlt->set(null);
        }
    }

    return $this;
}


  
    // TCMSFieldLookupMultiselect
/**
* @return Collection<int, shopShippingGroup>
*/
public function getShopShippingGroupCollection(): Collection
{
    return $this->ShopShippingGroupCollection;
}

public function addShopShippingGroupCollection(shopShippingGroup $ShopShippingGroupMlt): self
{
    if (!$this->ShopShippingGroupCollection->contains($ShopShippingGroupMlt)) {
        $this->ShopShippingGroupCollection->add($ShopShippingGroupMlt);
        $ShopShippingGroupMlt->set($this);
    }

    return $this;
}

public function removeShopShippingGroupCollection(shopShippingGroup $ShopShippingGroupMlt): self
{
    if ($this->ShopShippingGroupCollection->removeElement($ShopShippingGroupMlt)) {
        // set the owning side to null (unless already changed)
        if ($ShopShippingGroupMlt->get() === $this) {
            $ShopShippingGroupMlt->set(null);
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
