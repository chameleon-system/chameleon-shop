<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;

class shopArticleImageSize {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
, 
    // TCMSFieldVarchar
/** @var string - System name */
private string $NameInternal = '', 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldNumber
/** @var int - Width */
private int $Width = 0, 
    // TCMSFieldNumber
/** @var int - Height */
private int $Height = 0, 
    // TCMSFieldBoolean
/** @var bool - Force size */
private bool $ForceSize = false  ) {}

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
public function getnameInternal(): string
{
    return $this->NameInternal;
}
public function setnameInternal(string $NameInternal): self
{
    $this->NameInternal = $NameInternal;

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


  
    // TCMSFieldNumber
public function getwidth(): int
{
    return $this->Width;
}
public function setwidth(int $Width): self
{
    $this->Width = $Width;

    return $this;
}


  
    // TCMSFieldNumber
public function getheight(): int
{
    return $this->Height;
}
public function setheight(int $Height): self
{
    $this->Height = $Height;

    return $this;
}


  
    // TCMSFieldBoolean
public function isforceSize(): bool
{
    return $this->ForceSize;
}
public function setforceSize(bool $ForceSize): self
{
    $this->ForceSize = $ForceSize;

    return $this;
}


  
}
