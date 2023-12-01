<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopCore;

use ChameleonSystem\ShopBundle\Entity\ShopCore\shopCategory;
use ChameleonSystem\ShopBundle\Entity\ShopCore\shop;

class pkgShopFooterCategory {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldVarchar
/** @var string - Main category / heading */
private string $Name = '', 
    // TCMSFieldExtendedLookup
/** @var shopCategory|null - Product category */
private ?shopCategory $ShopCategory = null
, 
    // TCMSFieldPosition
/** @var int - Sorting */
private int $SortOrder = 0, 
    // TCMSFieldLookupParentID
/** @var shop|null - Belongs to shop */
private ?shop $Shop = null
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


  
    // TCMSFieldExtendedLookup
public function getShopCategory(): ?shopCategory
{
    return $this->ShopCategory;
}

public function setShopCategory(?shopCategory $ShopCategory): self
{
    $this->ShopCategory = $ShopCategory;

    return $this;
}


  
    // TCMSFieldPosition
public function getsortOrder(): int
{
    return $this->SortOrder;
}
public function setsortOrder(int $SortOrder): self
{
    $this->SortOrder = $SortOrder;

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


  
}
