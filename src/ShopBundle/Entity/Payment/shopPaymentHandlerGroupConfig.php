<?php
namespace ChameleonSystem\ShopBundle\Entity\Payment;

use ChameleonSystem\ShopBundle\Entity\Payment\shopPaymentHandlerGroup;
use ChameleonSystem\DataAccessBundle\Entity\CorePortal\cmsPortal;

class shopPaymentHandlerGroupConfig {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopPaymentHandlerGroup|null - Belongs to */
private ?shopPaymentHandlerGroup $ShopPaymentHandlerGroup = null
, 
    // TCMSFieldVarchar
/** @var string - Name */
private string $Name = '', 
    // TCMSFieldOption
/** @var string - Type */
private string $Type = 'common', 
    // TCMSFieldExtendedLookup
/** @var cmsPortal|null - Portal */
private ?cmsPortal $CmsPortal = null
, 
    // TCMSFieldText
/** @var string - Value */
private string $Value = '', 
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


  
    // TCMSFieldExtendedLookup
public function getCmsPortal(): ?cmsPortal
{
    return $this->CmsPortal;
}

public function setCmsPortal(?cmsPortal $CmsPortal): self
{
    $this->CmsPortal = $CmsPortal;

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
