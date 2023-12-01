<?php
namespace ChameleonSystem\ShopPaymentTransactionBundle\Entity;

use ChameleonSystem\ShopPaymentTransactionBundle\Entity\pkgShopPaymentTransaction;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderItem;

class pkgShopPaymentTransactionPosition {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopPaymentTransaction|null - Belongs to transaction */
private ?pkgShopPaymentTransaction $PkgShopPaymentTransaction = null
, 
    // TCMSFieldNumber
/** @var int - Amount */
private int $Amount = 0, 
    // TCMSFieldDecimal
/** @var float - Value */
private float $Value = 0, 
    // TCMSFieldOption
/** @var string - Type */
private string $Type = 'product', 
    // TCMSFieldExtendedLookup
/** @var shopOrderItem|null - Order item */
private ?shopOrderItem $ShopOrderItem = null
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
public function getPkgShopPaymentTransaction(): ?pkgShopPaymentTransaction
{
    return $this->PkgShopPaymentTransaction;
}

public function setPkgShopPaymentTransaction(?pkgShopPaymentTransaction $PkgShopPaymentTransaction): self
{
    $this->PkgShopPaymentTransaction = $PkgShopPaymentTransaction;

    return $this;
}


  
    // TCMSFieldNumber
public function getamount(): int
{
    return $this->Amount;
}
public function setamount(int $Amount): self
{
    $this->Amount = $Amount;

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
public function getShopOrderItem(): ?shopOrderItem
{
    return $this->ShopOrderItem;
}

public function setShopOrderItem(?shopOrderItem $ShopOrderItem): self
{
    $this->ShopOrderItem = $ShopOrderItem;

    return $this;
}


  
}
