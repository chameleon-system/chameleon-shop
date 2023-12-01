<?php
namespace ChameleonSystem\ShopWishlistBundle\Entity;

use ChameleonSystem\ShopWishlistBundle\Entity\pkgShopWishlist;
use ChameleonSystem\ShopBundle\Entity\ShopOrder\shopOrderItem;
use ChameleonSystem\ExtranetBundle\Entity\dataExtranetUser;

class pkgShopWishlistOrderItem {
  public function __construct(
    private string $id,
    private int|null $cmsident = null,
        
    // TCMSFieldExtendedLookup
/** @var pkgShopWishlist|null - Wishlist */
private ?pkgShopWishlist $PkgShopWishlist = null
, 
    // TCMSFieldExtendedLookup
/** @var shopOrderItem|null - Order item */
private ?shopOrderItem $ShopOrderItem = null
, 
    // TCMSFieldExtendedLookup
/** @var dataExtranetUser|null - Wishlist owner */
private ?dataExtranetUser $DataExtranetUser = null
, 
    // TCMSFieldVarchar
/** @var string - Email of the wishlist owner */
private string $DataExtranetUserEmail = ''  ) {}

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
    // TCMSFieldExtendedLookup
public function getPkgShopWishlist(): ?pkgShopWishlist
{
    return $this->PkgShopWishlist;
}

public function setPkgShopWishlist(?pkgShopWishlist $PkgShopWishlist): self
{
    $this->PkgShopWishlist = $PkgShopWishlist;

    return $this;
}


  
    // TCMSFieldExtendedLookup
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
public function getDataExtranetUser(): ?dataExtranetUser
{
    return $this->DataExtranetUser;
}

public function setDataExtranetUser(?dataExtranetUser $DataExtranetUser): self
{
    $this->DataExtranetUser = $DataExtranetUser;

    return $this;
}


  
    // TCMSFieldVarchar
public function getdataExtranetUserEmail(): string
{
    return $this->DataExtranetUserEmail;
}
public function setdataExtranetUserEmail(string $DataExtranetUserEmail): self
{
    $this->DataExtranetUserEmail = $DataExtranetUserEmail;

    return $this;
}


  
}
