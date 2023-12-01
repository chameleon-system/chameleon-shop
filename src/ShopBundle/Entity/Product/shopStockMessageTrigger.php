<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopStockMessage;

class shopStockMessageTrigger {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopStockMessage|null - Stock message */
private ?shopStockMessage $ShopStockMessage = null
, 
    // TCMSFieldNumber
/** @var int - Amount */
private int $Amount = 0, 
    // TCMSFieldVarchar
/** @var string - Message */
private string $Message = '', 
    // TCMSFieldVarchar
/** @var string - System name */
private string $SystemName = '', 
    // TCMSFieldVarchar
/** @var string - CSS class */
private string $CssClass = ''  ) {}

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
public function getShopStockMessage(): ?shopStockMessage
{
    return $this->ShopStockMessage;
}

public function setShopStockMessage(?shopStockMessage $ShopStockMessage): self
{
    $this->ShopStockMessage = $ShopStockMessage;

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


  
    // TCMSFieldVarchar
public function getmessage(): string
{
    return $this->Message;
}
public function setmessage(string $Message): self
{
    $this->Message = $Message;

    return $this;
}


  
    // TCMSFieldVarchar
public function getsystemName(): string
{
    return $this->SystemName;
}
public function setsystemName(string $SystemName): self
{
    $this->SystemName = $SystemName;

    return $this;
}


  
    // TCMSFieldVarchar
public function getcssClass(): string
{
    return $this->CssClass;
}
public function setcssClass(string $CssClass): self
{
    $this->CssClass = $CssClass;

    return $this;
}


  
}
