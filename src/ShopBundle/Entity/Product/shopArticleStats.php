<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class shopArticleStats {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldNumber
/** @var int - Sales */
private int $StatsSales = 0, 
    // TCMSFieldNumber
/** @var int - Details on views */
private int $StatsDetailViews = 0, 
    // TCMSFieldDecimal
/** @var float - Average rating */
private float $StatsReviewAverage = 0, 
    // TCMSFieldNumber
/** @var int - Number of ratings */
private int $StatsReviewCount = 0  ) {}

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
public function getstatsSales(): int
{
    return $this->StatsSales;
}
public function setstatsSales(int $StatsSales): self
{
    $this->StatsSales = $StatsSales;

    return $this;
}


  
    // TCMSFieldNumber
public function getstatsDetailViews(): int
{
    return $this->StatsDetailViews;
}
public function setstatsDetailViews(int $StatsDetailViews): self
{
    $this->StatsDetailViews = $StatsDetailViews;

    return $this;
}


  
    // TCMSFieldDecimal
public function getstatsReviewAverage(): float
{
    return $this->StatsReviewAverage;
}
public function setstatsReviewAverage(float $StatsReviewAverage): self
{
    $this->StatsReviewAverage = $StatsReviewAverage;

    return $this;
}


  
    // TCMSFieldNumber
public function getstatsReviewCount(): int
{
    return $this->StatsReviewCount;
}
public function setstatsReviewCount(int $StatsReviewCount): self
{
    $this->StatsReviewCount = $StatsReviewCount;

    return $this;
}


  
}
