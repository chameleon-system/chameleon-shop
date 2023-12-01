<?php
namespace ChameleonSystem\AmazonPaymentBundle\Entity;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransaction;

class amazonPaymentIdMapping {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Belongs to order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldVarchar
/** @var string - Amazon order reference ID */
private string $AmazonOrderReferenceId = '', 
    // TCMSFieldVarchar
/** @var string - Local reference ID */
private string $LocalId = '', 
    // TCMSFieldVarchar
/** @var string - Amazon ID */
private string $AmazonId = '', 
    // TCMSFieldDecimal
/** @var float - Value */
private float $Value = 0, 
    // TCMSFieldNumber
/** @var int - Type */
private int $Type = 0, 
    // TCMSFieldNumber
/** @var int - Request mode */
private int $RequestMode = 1, 
    // TCMSFieldBoolean
/** @var bool - CaptureNow */
private bool $CaptureNow = false, 
    // TCMSFieldExtendedLookup
/** @var pkgShopPaymentTransaction|null - Belongs to transaction */
private ?pkgShopPaymentTransaction $PkgShopPaymentTransaction = null
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
public function getamazonOrderReferenceId(): string
{
    return $this->AmazonOrderReferenceId;
}
public function setamazonOrderReferenceId(string $AmazonOrderReferenceId): self
{
    $this->AmazonOrderReferenceId = $AmazonOrderReferenceId;

    return $this;
}


  
    // TCMSFieldVarchar
public function getlocalId(): string
{
    return $this->LocalId;
}
public function setlocalId(string $LocalId): self
{
    $this->LocalId = $LocalId;

    return $this;
}


  
    // TCMSFieldVarchar
public function getamazonId(): string
{
    return $this->AmazonId;
}
public function setamazonId(string $AmazonId): self
{
    $this->AmazonId = $AmazonId;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalue(): float
{
    return $this->Value;
}
public function setvalue(float $Value): self
{
    $this->Value = $Value;

    return $this;
}


  
    // TCMSFieldNumber
public function gettype(): int
{
    return $this->Type;
}
public function settype(int $Type): self
{
    $this->Type = $Type;

    return $this;
}


  
    // TCMSFieldNumber
public function getrequestMode(): int
{
    return $this->RequestMode;
}
public function setrequestMode(int $RequestMode): self
{
    $this->RequestMode = $RequestMode;

    return $this;
}


  
    // TCMSFieldBoolean
public function iscaptureNow(): bool
{
    return $this->CaptureNow;
}
public function setcaptureNow(bool $CaptureNow): self
{
    $this->CaptureNow = $CaptureNow;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getPkgShopPaymentTransaction(): ?pkgShopPaymentTransaction
{
    return $this->PkgShopPaymentTransaction;
}

public function setPkgShopPaymentTransaction(?pkgShopPaymentTransaction $PkgShopPaymentTransaction): self
{
    $this->PkgShopPaymentTransaction = $PkgShopPaymentTransaction;

    return $this;
}


  
}
