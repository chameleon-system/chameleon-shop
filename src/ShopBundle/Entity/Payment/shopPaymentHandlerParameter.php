<?php
namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandler;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class shopPaymentHandlerParameter {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandler|null - Belongs to payment handler */
private ?shopPaymentHandler $ShopPaymentHandler = null
, 
    // TCMSFieldVarchar
/** @var string - Display name */
private string $Name = '', 
    // TCMSFieldOption
/** @var string - Type */
private string $Type = 'common', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $Systemname = '', 
    // TCMSFieldWYSIWYG
/** @var string - Description */
private string $Description = '', 
    // TCMSFieldText
/** @var string - Value */
private string $Value = '', 
    // TCMSFieldLookup
/** @var cmsPortal|null - Applies to this portal only */
private ?cmsPortal $CmsPortal = null
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
public function getShopPaymentHandler(): ?shopPaymentHandler
{
    return $this->ShopPaymentHandler;
}

public function setShopPaymentHandler(?shopPaymentHandler $ShopPaymentHandler): self
{
    $this->ShopPaymentHandler = $ShopPaymentHandler;

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


  
    // TCMSFieldOption
public function gettype(): string
{
    return $this->Type;
}
public function settype(string $Type): self
{
    $this->Type = $Type;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsystemname(): string
{
    return $this->Systemname;
}
public function setsystemname(string $Systemname): self
{
    $this->Systemname = $Systemname;

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


  
    // TCMSFieldText
public function getvalue(): string
{
    return $this->Value;
}
public function setvalue(string $Value): self
{
    $this->Value = $Value;

    return $this;
}


  
    // TCMSFieldLookup
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

    return $this;
}


  
}
