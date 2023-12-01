<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopArticleStock {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldNumber
/** @var int - Amount */
private int $Amount = 0  ) {}

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


  
}
