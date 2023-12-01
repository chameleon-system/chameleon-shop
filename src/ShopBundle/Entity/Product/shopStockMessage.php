<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;
use ChameleonSystem\ShopBundle\Entity\Product\shopStockMessageTrigger;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopStockMessage {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Class */
private string $ClassName = '', 
    // TCMSFieldVarchar
/** @var string - Class subtype (path) */
private string $ClassSubtype = '', 
    // TCMSFieldOption
/** @var string - Class type */
private string $ClassType = 'Customer', 
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - Interface identifier */
private string $Identifier = '', 
    // TCMSFieldVarchar
/** @var string - CSS class */
private string $Class = '', 
    // TCMSFieldVarchar
/** @var string - Message */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $InternalName = '', 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopStockMessageTrigger> - Stock messages */
private Collection $ShopStockMessageTriggerCollection = new ArrayCollection()
, 
    // TCMSFieldBoolean
/** @var bool - Automatically deactivate when stock = 0 */
private bool $AutoDeactivateOnZeroStock = true, 
    // TCMSFieldBoolean
/** @var bool - Automatically deactivate when stock > 0 */
private bool $AutoActivateOnStock = true, 
    // TCMSFieldVarchar
/** @var string - Google availability */
private string $GoogleAvailability = ''  ) {}

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
public function getclassName(): string
{
    return $this->ClassName;
}
public function setclassName(string $ClassName): self
{
    $this->ClassName = $ClassName;

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


  
    // TCMSFieldLookupParentID
public function getShop(): ?shop
{
    return $this->Shop;
}

public function setShop(?shop $Shop): self
{
    $this->Shop = $Shop;

    return $this;
}


  
    // TCMSFieldVarchar
public function getidentifier(): string
{
    return $this->Identifier;
}
public function setidentifier(string $Identifier): self
{
    $this->Identifier = $Identifier;

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
public function getinternalName(): string
{
    return $this->InternalName;
}
public function setinternalName(string $InternalName): self
{
    $this->InternalName = $InternalName;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopStockMessageTrigger>
*/
public function getShopStockMessageTriggerCollection(): Collection
{
    return $this->ShopStockMessageTriggerCollection;
}

public function addShopStockMessageTriggerCollection(shopStockMessageTrigger $ShopStockMessageTrigger): self
{
    if (!$this->ShopStockMessageTriggerCollection->contains($ShopStockMessageTrigger)) {
        $this->ShopStockMessageTriggerCollection->add($ShopStockMessageTrigger);
        $ShopStockMessageTrigger->setShopStockMessage($this);
    }

    return $this;
}

public function removeShopStockMessageTriggerCollection(shopStockMessageTrigger $ShopStockMessageTrigger): self
{
    if ($this->ShopStockMessageTriggerCollection->removeElement($ShopStockMessageTrigger)) {
        // set the owning side to null (unless already changed)
        if ($ShopStockMessageTrigger->getShopStockMessage() === $this) {
            $ShopStockMessageTrigger->setShopStockMessage(null);
        }
    }

    return $this;
}


  
    // TCMSFieldBoolean
public function isautoDeactivateOnZeroStock(): bool
{
    return $this->AutoDeactivateOnZeroStock;
}
public function setautoDeactivateOnZeroStock(bool $AutoDeactivateOnZeroStock): self
{
    $this->AutoDeactivateOnZeroStock = $AutoDeactivateOnZeroStock;

    return $this;
}


  
    // TCMSFieldBoolean
public function isautoActivateOnStock(): bool
{
    return $this->AutoActivateOnStock;
}
public function setautoActivateOnStock(bool $AutoActivateOnStock): self
{
    $this->AutoActivateOnStock = $AutoActivateOnStock;

    return $this;
}


  
    // TCMSFieldVarchar
public function getgoogleAvailability(): string
{
    return $this->GoogleAvailability;
}
public function setgoogleAvailability(string $GoogleAvailability): self
{
    $this->GoogleAvailability = $GoogleAvailability;

    return $this;
}


  
}
