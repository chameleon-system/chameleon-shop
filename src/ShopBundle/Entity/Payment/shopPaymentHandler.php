<?php
namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;
use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerParameter;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopPaymentHandler {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Belongs to payment provider */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldVarchar
/** @var string - Internal name for payment handler */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - Block user selection */
private bool $BlockUserSelection = false, 
    // TCMSFieldVarchar
/** @var string - Class name */
private string $Class = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Core', 
    // TCMSFieldVarchar
/** @var string - Class subtype */
private string $ClassSubtype = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopPaymentHandlerParameter> - Configuration settings */
private Collection $ShopPaymentHandlerParameterCollection = new ArrayCollection()
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


  
    // TCMSFieldBoolean
public function isblockUserSelection(): bool
{
    return $this->BlockUserSelection;
}
public function setblockUserSelection(bool $BlockUserSelection): self
{
    $this->BlockUserSelection = $BlockUserSelection;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclass(): string
{
    return $this->Class;
}
public function setclass(string $Class): self
{
    $this->Class = $Class;

    return $this;
}


  
    // TCMSFieldOption
public function getclassType(): string
{
    return $this->ClassType;
}
public function setclassType(string $ClassType): self
{
    $this->ClassType = $ClassType;

    return $this;
}


  
    // TCMSFieldVarchar
public function getclassSubtype(): string
{
    return $this->ClassSubtype;
}
public function setclassSubtype(string $ClassSubtype): self
{
    $this->ClassSubtype = $ClassSubtype;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopPaymentHandlerParameter>
*/
public function getShopPaymentHandlerParameterCollection(): Collection
{
    return $this->ShopPaymentHandlerParameterCollection;
}

public function addShopPaymentHandlerParameterCollection(shopPaymentHandlerParameter $ShopPaymentHandlerParameter): self
{
    if (!$this->ShopPaymentHandlerParameterCollection->contains($ShopPaymentHandlerParameter)) {
        $this->ShopPaymentHandlerParameterCollection->add($ShopPaymentHandlerParameter);
        $ShopPaymentHandlerParameter->setShopPaymentHandler($this);
    }

    return $this;
}

public function removeShopPaymentHandlerParameterCollection(shopPaymentHandlerParameter $ShopPaymentHandlerParameter): self
{
    if ($this->ShopPaymentHandlerParameterCollection->removeElement($ShopPaymentHandlerParameter)) {
        // set the owning side to null (unless already changed)
        if ($ShopPaymentHandlerParameter->getShopPaymentHandler() === $this) {
            $ShopPaymentHandlerParameter->setShopPaymentHandler(null);
        }
    }

    return $this;
}


  
}
