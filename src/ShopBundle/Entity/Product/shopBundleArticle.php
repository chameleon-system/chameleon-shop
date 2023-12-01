<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopBundleArticle {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to bundle article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Article */
private ?shopArticle $BundleArticle = null
, 
    // TCMSFieldNumber
/** @var int - Units */
private int $Amount = 1, 
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
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getBundleArticle(): ?shopArticle
{
    return $this->BundleArticle;
}

public function setBundleArticle(?shopArticle $BundleArticle): self
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
