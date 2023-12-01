<?php
namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ShopWishlistBundle\Entity\pkgShopWishlist;
use ChameleonSystem\ShopBundle\Entity\Product\shopArticle;

class pkgShopWishlistArticle {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopWishlist|null - Belongs to wishlist */
private ?pkgShopWishlist $PkgShopWishlist = null
, 
    // TCMSFieldDateTimeNow
/** @var \DateTime|null - Created on */
private ?\DateTime $Datecreated = new \DateTime(), 
    // TCMSFieldNumber
/** @var int - Amount */
private int $Amount = 0, 
    // TCMSFieldExtendedLookup
/** @var shopArticle|null - Article */
private ?shopArticle $ShopArticle = null
, 
    // TCMSFieldText
/** @var string - Comment */
private string $Comment = ''  ) {}

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
public function getPkgShopWishlist(): ?pkgShopWishlist
{
    return $this->PkgShopWishlist;
}

public function setPkgShopWishlist(?pkgShopWishlist $PkgShopWishlist): self
{
    $this->PkgShopWishlist = $PkgShopWishlist;

    return $this;
}


  
    // TCMSFieldDateTimeNow
public function getdatecreated(): ?\DateTime
{
    return $this->Datecreated;
}
public function setdatecreated(?\DateTime $Datecreated): self
{
    $this->Datecreated = $Datecreated;

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


  
    // TCMSFieldText
public function getcomment(): string
{
    return $this->Comment;
}
public function setcomment(string $Comment): self
{
    $this->Comment = $Comment;

    return $this;
}


  
}
