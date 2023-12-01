<?php
namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ShopWishlistBundle\Entity\pkgShopWishlist;

class pkgShopWishlistMailHistory {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldLookupParentID
/** @var pkgShopWishlist|null - Belongs to wishlist */
private ?pkgShopWishlist $PkgShopWishlist = null
, 
    // TCMSFieldDateTime
/** @var \DateTime|null - Email sent on */
private ?\DateTime $Datesend = null, 
    // TCMSFieldVarchar
/** @var string - Recipient name */
private string $ToName = '', 
    // TCMSFieldEmail
/** @var string - Feedback recipient (Email address) */
private string $ToEmail = '', 
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


  
    // TCMSFieldDateTime
public function getdatesend(): ?\DateTime
{
    return $this->Datesend;
}
public function setdatesend(?\DateTime $Datesend): self
{
    $this->Datesend = $Datesend;

    return $this;
}


  
    // TCMSFieldVarchar
public function gettoName(): string
{
    return $this->ToName;
}
public function settoName(string $ToName): self
{
    $this->ToName = $ToName;

    return $this;
}


  
    // TCMSFieldEmail
public function gettoEmail(): string
{
    return $this->ToEmail;
}
public function settoEmail(string $ToEmail): self
{
    $this->ToEmail = $ToEmail;

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
