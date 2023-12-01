<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopVoucher;

use ChameleonSystem\ShopBundle\Entity\ShopVoucher\shopVoucher;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrder;
use ChameleonSystem\ShopBundle\Entity\ShopCore\pkgShopCurrency;

class shopVoucherUse {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopVoucher|null - Belongs to voucher */
private ?shopVoucher $ShopVoucher = null
, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Used on */
private ?\DateTime $DateUsed = null, 
    // TCMSFieldDecimal
/** @var float - Value used up */
private float $ValueUsed = 0, 
    // TCMSFieldLookupParentID
/** @var shopOrder|null - Used in this order */
private ?shopOrder $ShopOrder = null
, 
    // TCMSFieldDecimal
/** @var float - Value consumed in the order currency */
private float $ValueUsedInOrderCurrency = 0, 
    // TCMSFieldExtendedLookup
/** @var pkgShopCurrency|null - Currency in which the order was made */
private ?pkgShopCurrency $PkgShopCurrency = null
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
public function getShopVoucher(): ?shopVoucher
{
    return $this->ShopVoucher;
}

public function setShopVoucher(?shopVoucher $ShopVoucher): self
{
    $this->ShopVoucher = $ShopVoucher;

    return $this;
}


  
    // TCMSFieldDateTime
public function getdateUsed(): ?\DateTime
{
    return $this->DateUsed;
}
public function setdateUsed(?\DateTime $DateUsed): self
{
    $this->DateUsed = $DateUsed;

    return $this;
}


  
    // TCMSFieldDecimal
public function getvalueUsed(): float
{
    return $this->ValueUsed;
}
public function setvalueUsed(float $ValueUsed): self
{
    $this->ValueUsed = $ValueUsed;

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


  
    // TCMSFieldDecimal
public function getvalueUsedInOrderCurrency(): float
{
    return $this->ValueUsedInOrderCurrency;
}
public function setvalueUsedInOrderCurrency(float $ValueUsedInOrderCurrency): self
{
    $this->ValueUsedInOrderCurrency = $ValueUsedInOrderCurrency;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getPkgShopCurrency(): ?pkgShopCurrency
{
    return $this->PkgShopCurrency;
}

public function setPkgShopCurrency(?pkgShopCurrency $PkgShopCurrency): self
{
    $this->PkgShopCurrency = $PkgShopCurrency;

    return $this;
}


  
}
