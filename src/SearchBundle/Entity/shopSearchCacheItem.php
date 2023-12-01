<?php
namespace ChameleonSystem\SearchBundle\Entity;

use ChameleonSystem\SearchBundle\Entity\shopSearchCache;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopSearchCacheItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopSearchCache|null - Belongs to search cache */
private ?shopSearchCache $ShopSearchCache = null
, 
    // TCMSFieldDecimal
/** @var float - Weight */
private float $Weight = 0, 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Article */
private ?shopArticle $ShopArticle = null
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
public function getShopSearchCache(): ?shopSearchCache
{
    return $this->ShopSearchCache;
}

public function setShopSearchCache(?shopSearchCache $ShopSearchCache): self
{
    $this->ShopSearchCache = $ShopSearchCache;

    return $this;
}


  
    // TCMSFieldDecimal
public function getweight(): float
{
    return $this->Weight;
}
public function setweight(float $Weight): self
{
    $this->Weight = $Weight;

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


  
}
