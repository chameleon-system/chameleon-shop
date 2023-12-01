<?php
namespace ChameleonSystem\ShopBundle\Entity\ShopOrder;

use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderItem;

class shopOrderBundleArticle {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopOrderItem|null - Bundle articles of the order */
private ?shopOrderItem $ShopOrderItem = null
, 
    // TCMSFieldExtendedLookup
/** @var shopOrderItem|null - Article belonging to bundle */
private ?shopOrderItem $BundleArticle = null
, 
    // TCMSFieldNumber
/** @var int - Units */
private int $Amount = 0, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0  ) {}

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
public function getShopOrderItem(): ?shopOrderItem
{
    return $this->ShopOrderItem;
}

public function setShopOrderItem(?shopOrderItem $ShopOrderItem): self
{
    $this->ShopOrderItem = $ShopOrderItem;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getBundleArticle(): ?shopOrderItem
{
    return $this->BundleArticle;
}

public function setBundleArticle(?shopOrderItem $BundleArticle): self
{
    $this->BundleArticle = $BundleArticle;

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


  
    // TCMSFieldPosition
public function getposition(): int
{
    return $this->Position;
}
public function setposition(int $Position): self
{
    $this->Position = $Position;

    return $this;
}


  
}
