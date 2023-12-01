<?php
namespace ChameleonSystem\ShopBundle\Entity\Product;

use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;
use ChameleonSystem\ShopBundle\Entity\Product\shopContributor;
use ChameleonSystem\ShopBundle\Entity\Product\shopContributorType;

class shopArticleContributor {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var shopArticle|null - Belongs to article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldExtendedLookup
/** @var shopContributor|null - Contributing person */
private ?shopContributor $ShopContributor = null
, 
    // TCMSFieldLookup
/** @var shopContributorType|null - Role of the contributing person / contribution type */
private ?shopContributorType $ShopContributorType = null
, 
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
public function getShopContributor(): ?shopContributor
{
    return $this->ShopContributor;
}

public function setShopContributor(?shopContributor $ShopContributor): self
{
    $this->ShopContributor = $ShopContributor;

    return $this;
}


  
    // TCMSFieldLookup
public function getShopContributorType(): ?shopContributorType
{
    return $this->ShopContributorType;
}

public function setShopContributorType(?shopContributorType $ShopContributorType): self
{
    $this->ShopContributorType = $ShopContributorType;

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
