<?php
namespace ChameleonSystem\ShopPaymentIPNBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;

class pkgShopPaymentIpnStatus {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Belongs to the configuration of */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldVarchar
/** @var string - Code (of the provider) */
private string $Code = '', 
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
public function getcode(): string
{
    return $this->Code;
}
public function setcode(string $Code): self
{
    $this->Code = $Code;

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
