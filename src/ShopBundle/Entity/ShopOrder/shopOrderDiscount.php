<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;

class shopOrderDiscount {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Order ID */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldVarchar
/** @var string - Discount ID */
private string $ShopDiscountId = '', 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Value */
private string $Value = '', 
    // TCMSFieldVarchar
/** @var string - Value type */
private string $Valuetype = '', 
    // TCMSFieldVarchar
/** @var string - Gratis article (name) */
private string $FreearticleName = '', 
    // TCMSFieldVarchar
/** @var string - Gratis article (article number) */
private string $FreearticleArticlenumber = '', 
    // TCMSFieldVarchar
/** @var string - Gratis article (ID) */
private string $FreearticleId = '', 
    // TCMSFieldDecimal
/** @var float - Total */
private float $Total = 0  ) {}

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
public function getShopOrder(): ?shopOrder
{
    return $this->ShopOrder;
}

public function setShopOrder(?shopOrder $ShopOrder): self
{
    $this->ShopOrder = $ShopOrder;

    return $this;
}


  
    // TCMSFieldVarchar
public function getshopDiscountId(): string
{
    return $this->ShopDiscountId;
}
public function setshopDiscountId(string $ShopDiscountId): self
{
    $this->ShopDiscountId = $ShopDiscountId;

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
public function getvalue(): string
{
    return $this->Value;
}
public function setvalue(string $Value): self
{
    $this->Value = $Value;

    return $this;
}


  
    // TCMSFieldVarchar
public function getvaluetype(): string
{
    return $this->Valuetype;
}
public function setvaluetype(string $Valuetype): self
{
    $this->Valuetype = $Valuetype;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfreearticleName(): string
{
    return $this->FreearticleName;
}
public function setfreearticleName(string $FreearticleName): self
{
    $this->FreearticleName = $FreearticleName;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfreearticleArticlenumber(): string
{
    return $this->FreearticleArticlenumber;
}
public function setfreearticleArticlenumber(string $FreearticleArticlenumber): self
{
    $this->FreearticleArticlenumber = $FreearticleArticlenumber;

    return $this;
}


  
    // TCMSFieldVarchar
public function getfreearticleId(): string
{
    return $this->FreearticleId;
}
public function setfreearticleId(string $FreearticleId): self
{
    $this->FreearticleId = $FreearticleId;

    return $this;
}


  
    // TCMSFieldDecimal
public function gettotal(): float
{
    return $this->Total;
}
public function settotal(float $Total): self
{
    $this->Total = $Total;

    return $this;
}


  
}
