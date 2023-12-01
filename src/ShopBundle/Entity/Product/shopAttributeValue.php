<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopAttribute;

class shopAttributeValue {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopAttribute|null - Belongs to the attribute */
private ?shopAttribute $ShopAttribute = null
, 
    // TCMSFieldVarchar
/** @var string - Value */
private string $Name = '', 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $Position = 0  ) {}

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
public function getShopAttribute(): ?shopAttribute
{
    return $this->ShopAttribute;
}

public function setShopAttribute(?shopAttribute $ShopAttribute): self
{
    $this->ShopAttribute = $ShopAttribute;

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


  
}
