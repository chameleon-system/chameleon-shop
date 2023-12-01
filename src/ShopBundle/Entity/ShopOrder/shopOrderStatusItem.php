<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderStatus;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderItem;

class shopOrderStatusItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrderStatus|null - Belongs to status */
private ?shopOrderStatus $ShopOrderStatus = null
, 
    // TCMSFieldExtendedLookup
/** @var shopOrderItem|null - Product */
private ?shopOrderItem $ShopOrderItem = null
, 
    // TCMSFieldDecimal
/** @var float - Amount */
private float $Amount = 0  ) {}

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
public function getShopOrderStatus(): ?shopOrderStatus
{
    return $this->ShopOrderStatus;
}

public function setShopOrderStatus(?shopOrderStatus $ShopOrderStatus): self
{
    $this->ShopOrderStatus = $ShopOrderStatus;

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


  
    // TCMSFieldDecimal
public function getamount(): float
{
    return $this->Amount;
}
public function setamount(float $Amount): self
{
    $this->Amount = $Amount;

    return $this;
}


  
}
