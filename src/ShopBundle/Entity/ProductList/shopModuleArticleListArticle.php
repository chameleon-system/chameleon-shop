<?php
namespace ChameleonSystem\ShopBundle\Entity\ProductList;

use ChameleonSystem\ShopBundle\Entity\ProductList\shopModuleArticleList;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopModuleArticleListArticle {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopModuleArticleList|null - Belongs to article list */
private ?shopModuleArticleList $ShopModuleArticleList = null
, 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldPosition
/** @var int - Position */
private int $Position = 0, 
    // TCMSFieldVarchar
/** @var string - Alternative headline */
private string $Name = ''  ) {}

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
public function getShopModuleArticleList(): ?shopModuleArticleList
{
    return $this->ShopModuleArticleList;
}

public function setShopModuleArticleList(?shopModuleArticleList $ShopModuleArticleList): self
{
    $this->ShopModuleArticleList = $ShopModuleArticleList;

    return $this;
}


  
    // TCMSFieldExtendedLookup
public function getShopArticle(): ?shopArticle
{
    return $this->ShopArticle;
}

public function setShopArticle(?shopArticle $ShopArticle): self
{
    $this->ShopArticle = $ShopArticle;

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


  
}
