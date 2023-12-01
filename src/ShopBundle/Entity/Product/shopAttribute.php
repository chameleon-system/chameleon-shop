<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopAttributeValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class shopAttribute {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldBoolean
/** @var bool - System attributes */
private bool $IsSystemAttribute = false, 
    // TCMSFieldPropertyTable
/** @var Collection<int, shopAttributeValue> - Attribute values */
private Collection $ShopAttributeValueCollection = new ArrayCollection()
, 
    // TCMSFieldVarchar
/** @var string - Internal name */
private string $SystemName = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = ''  ) {}

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


  
    // TCMSFieldBoolean
public function isisSystemAttribute(): bool
{
    return $this->IsSystemAttribute;
}
public function setisSystemAttribute(bool $IsSystemAttribute): self
{
    $this->IsSystemAttribute = $IsSystemAttribute;

    return $this;
}


  
    // TCMSFieldPropertyTable
/**
* @return Collection<int, shopAttributeValue>
*/
public function getShopAttributeValueCollection(): Collection
{
    return $this->ShopAttributeValueCollection;
}

public function addShopAttributeValueCollection(shopAttributeValue $ShopAttributeValue): self
{
    if (!$this->ShopAttributeValueCollection->contains($ShopAttributeValue)) {
        $this->ShopAttributeValueCollection->add($ShopAttributeValue);
        $ShopAttributeValue->setShopAttribute($this);
    }

    return $this;
}

public function removeShopAttributeValueCollection(shopAttributeValue $ShopAttributeValue): self
{
    if ($this->ShopAttributeValueCollection->removeElement($ShopAttributeValue)) {
        // set the owning side to null (unless already changed)
        if ($ShopAttributeValue->getShopAttribute() === $this) {
            $ShopAttributeValue->setShopAttribute(null);
        }
    }

    return $this;
}


  
    // TCMSFieldVarchar
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

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


  
}
